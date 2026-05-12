<?php

namespace App\Services;

use App\Models\Ipcr;
use Illuminate\Support\Facades\DB;

class IpcrService
{
    public function createIpcrWithFunctions(array $data)
    {
        return DB::transaction(function () use ($data) {
            $data['core_functions'] = $this->filterFunctionRows($data['core_functions'] ?? []);
            $data['support_functions'] = $this->filterFunctionRows($data['support_functions'] ?? []);
            $data['strategic_functions'] = $this->filterFunctionRows($data['strategic_functions'] ?? []);

            $ipcrData = $data['ipcr'];

            $alreadyExists = Ipcr::where('userid', $ipcrData['userid'])
                ->where('year', $ipcrData['year'])
                ->where('semester', $ipcrData['semester'])
                ->exists();

            if ($alreadyExists) {
                throw new \Exception('IPCR already exists for this year and semester.');
            }

            $user = \App\Models\User::find($ipcrData['userid']);
            
            // Automate Signatories
            $signatories = $this->getAutomatedSignatories($user);
            $ipcrData['supervisor_id'] = $signatories['supervisor_id'];
            $ipcrData['division_head'] = $signatories['division_head'];
            $ipcrData['highest_supervisor'] = $signatories['highest_supervisor'];
            $ipcrData['pmt_id'] = null;
            
            // Set default distributions if not provided
            $ipcrData['core_percentage_distribution'] = $ipcrData['core_percentage_distribution'] ?? 50;
            $ipcrData['support_percentage_distribution'] = $ipcrData['support_percentage_distribution'] ?? 10;
            $ipcrData['strategic_percentage_distribution'] = $ipcrData['strategic_percentage_distribution'] ?? 40;
            
            $ipcr = Ipcr::create($ipcrData);

            $hasManualRows = !empty($data['core_functions']) || !empty($data['support_functions']) || !empty($data['strategic_functions']);

            // For 2nd semester, copy 1st semester targets only when user did not manually provide rows.
            if (!empty($ipcrData['semester']) && $ipcrData['semester'] == 2 && !$hasManualRows) {
                $firstSemesterIpcr = Ipcr::where('userid', $ipcrData['userid'])
                    ->where('year', $ipcrData['year'])
                    ->where('semester', 1)
                    ->with(['coreFunctions', 'supportFunctions', 'strategicFunctions'])
                    ->first();

                if ($firstSemesterIpcr) {
                    // Copy targets from 1st semester
                    foreach ($firstSemesterIpcr->coreFunctions as $func) {
                        $ipcr->coreFunctions()->create([
                            'output' => $func->output,
                            'success_indicator' => $func->success_indicator,
                        ]);
                    }
                    
                    foreach ($firstSemesterIpcr->supportFunctions as $func) {
                        $ipcr->supportFunctions()->create([
                            'output' => $func->output,
                            'success_indicator' => $func->success_indicator,
                        ]);
                    }
                    
                    foreach ($firstSemesterIpcr->strategicFunctions as $func) {
                        $ipcr->strategicFunctions()->create([
                            'output' => $func->output,
                            'success_indicator' => $func->success_indicator,
                        ]);
                    }
                }
            } else {
                // For 1st semester or when manual rows are provided, create from payload.
                if (!empty($data['core_functions'])) {
                    foreach ($data['core_functions'] as $func) {
                        $ipcr->coreFunctions()->create($this->prepareFunctionData($func));
                    }
                }

                if (!empty($data['support_functions'])) {
                    foreach ($data['support_functions'] as $func) {
                        $ipcr->supportFunctions()->create($this->prepareFunctionData($func));
                    }
                }

                if (!empty($data['strategic_functions'])) {
                    foreach ($data['strategic_functions'] as $func) {
                        $ipcr->strategicFunctions()->create($this->prepareFunctionData($func));
                    }
                }
            }

            $this->calculateAllRatings($ipcr);

            // Log the creation
            $semester = $ipcrData['semester'] ?? 1;
            $logSubject = $semester == 1 ? 'IPCR Target Created' : 'IPCR Accomplishment Created';
            
            $ipcr->logs()->create([
                'subject' => $logSubject,
                'content' => "IPCR form created for Year {$ipcrData['year']} Semester {$semester}.",
                'updated_by' => $ipcrData['userid'] ?? null,
            ]);

            return $ipcr->load(['coreFunctions', 'supportFunctions', 'strategicFunctions', 'logs']);
        });
    }

    protected function prepareFunctionData(array $func)
    {
        $q = $func['quantity_rating'] ?? null;
        $e = $func['efficiency_rating'] ?? null;
        $t = $func['timeliness_rating'] ?? null;
        
        if ($q !== null || $e !== null || $t !== null) {
            $ratings = array_filter([$q, $e, $t], fn($v) => $v !== null);
            $func['average_rating'] = count($ratings) > 0 ? array_sum($ratings) / count($ratings) : null;
        }
        
        return $func;
    }

    public function calculateAllRatings(Ipcr $ipcr)
    {
        $ipcr->load(['coreFunctions', 'supportFunctions', 'strategicFunctions']);
        
        $calculateCategoryAverage = function ($functions) {
            $sum = 0;
            $count = 0;
            foreach ($functions as $f) {
                // If average_rating isn't set on the model, calculate it on the fly if ratings exist
                $avg = $f->average_rating;
                if ($avg === null) {
                    $ratings = array_filter([$f->quantity_rating, $f->efficiency_rating, $f->timeliness_rating], fn($v) => $v !== null && $v > 0);
                    $avg = count($ratings) > 0 ? array_sum($ratings) / count($ratings) : null;
                }

                if ($avg !== null) {
                    $sum += $avg;
                    $count++;
                }
            }
            return $count > 0 ? $sum / $count : 0;
        };

        $coreAvg = $calculateCategoryAverage($ipcr->coreFunctions);
        $supportAvg = $calculateCategoryAverage($ipcr->supportFunctions);
        $strategicAvg = $calculateCategoryAverage($ipcr->strategicFunctions);

        // User requested static distributions: Core-50%, Support-10%, Strategic-40%
        $coreDist = 0.50;
        $supportDist = 0.10;
        $strategicDist = 0.40;

        $finalAvg = ($coreAvg * $coreDist) + ($supportAvg * $supportDist) + ($strategicAvg * $strategicDist);
        
        $ipcr->final_average_rating = $finalAvg;
        $ipcr->final_rating = $finalAvg; // Usually the same unless there are deductions
        $ipcr->final_rating_adjective = $this->getAdjectiveRating($finalAvg);
        $ipcr->save();
    }

    protected function getAdjectiveRating($score)
    {
        if ($score >= 4.5) return 'Outstanding';
        if ($score >= 3.5) return 'Very Satisfactory';
        if ($score >= 2.5) return 'Satisfactory';
        if ($score >= 1.5) return 'Unsatisfactory';
        if ($score > 0) return 'Poor';
        return 'N/A';
    }

    public function getAll()
    {
        return Ipcr::with(['user', 'coreFunctions', 'supportFunctions', 'strategicFunctions'])->get();
    }

    public function getById($id)
    {
        return Ipcr::with(['user', 'coreFunctions', 'supportFunctions', 'strategicFunctions'])->findOrFail($id);
    }

    public function updateIpcr($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $ipcr = Ipcr::findOrFail($id);
            
            if (isset($data['ipcr'])) {
                $ipcrData = $data['ipcr'];
                
                // If userid is being updated or signatories aren't set, re-automate
                if (isset($ipcrData['userid']) || !isset($ipcr->supervisor_id)) {
                    $user = \App\Models\User::find($ipcrData['userid'] ?? $ipcr->userid);
                    $signatories = $this->getAutomatedSignatories($user);
                    $ipcrData['supervisor_id'] = $signatories['supervisor_id'];
                    $ipcrData['division_head'] = $signatories['division_head'];
                    $ipcrData['highest_supervisor'] = $signatories['highest_supervisor'];
                }
                
                $ipcr->update($ipcrData);
            }

            if (isset($data['core_functions'])) {
                $this->syncFunctions($ipcr->coreFunctions(), $this->filterFunctionRows($data['core_functions']));
            }

            if (isset($data['support_functions'])) {
                $this->syncFunctions($ipcr->supportFunctions(), $this->filterFunctionRows($data['support_functions']));
            }

            if (isset($data['strategic_functions'])) {
                $this->syncFunctions($ipcr->strategicFunctions(), $this->filterFunctionRows($data['strategic_functions']));
            }

            $this->calculateAllRatings($ipcr);
            return $ipcr->load(['coreFunctions', 'supportFunctions', 'strategicFunctions']);
        });
    }

    protected function syncFunctions($relation, array $functionsData)
    {
        $functionsData = $this->filterFunctionRows($functionsData);

        $parentKey  = $relation->getParentKey();
        $foreignKey = $relation->getForeignKeyName();
        $model      = $relation->getRelated();

        $existingIds = $model->newQuery()
            ->where($foreignKey, $parentKey)
            ->pluck('id')
            ->toArray();

        $receivedIds = array_filter(array_column($functionsData, 'id'));

        // Delete rows not in the incoming payload
        $idsToDelete = array_diff($existingIds, $receivedIds);
        if (!empty($idsToDelete)) {
            $model->newQuery()
                ->where($foreignKey, $parentKey)
                ->whereIn('id', $idsToDelete)
                ->delete();
        }

        foreach ($functionsData as $func) {
            $preparedData = $this->prepareFunctionData($func);
            if (!empty($func['id'])) {
                // Fresh query each iteration — avoids WHERE accumulation on the shared relation
                $updateData = array_diff_key($preparedData, ['id' => null]);
                $model->newQuery()
                    ->where('id', $func['id'])
                    ->where($foreignKey, $parentKey)
                    ->update($updateData);
            } else {
                $relation->create($preparedData);
            }
        }
    }

    protected function filterFunctionRows(array $functions): array
    {
        return array_values(array_filter($functions, function ($func) {
            return !$this->isEmptyFunctionRow((array) $func);
        }));
    }

    protected function isEmptyFunctionRow(array $func): bool
    {
        $fields = [
            'output',
            'success_indicator',
            'actual_accomplishment',
            'quantity_rating',
            'efficiency_rating',
            'timeliness_rating',
            'remarks',
        ];

        foreach ($fields as $field) {
            if ($this->hasMeaningfulValue($func[$field] ?? null)) {
                return false;
            }
        }

        return true;
    }

    protected function hasMeaningfulValue($value): bool
    {
        if (is_null($value)) {
            return false;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        return $value !== '';
    }

    public function deleteIpcr($id)
    {
        $ipcr = Ipcr::findOrFail($id);
        return DB::transaction(function () use ($ipcr) {
            $ipcr->coreFunctions()->delete();
            $ipcr->supportFunctions()->delete();
            $ipcr->strategicFunctions()->delete();
            $ipcr->delete();
        });
    }

    public function getPendingApprovals($userId, $year = null, $semester = null)
    {
        return Ipcr::where(function ($query) use ($userId) {
            $query->where(function ($roleQuery) use ($userId) {
                // As Supervisor
                $roleQuery->where('supervisor_id', $userId)
                    ->whereIn('status', ['Target Submitted', 'Accomplishment Submitted']);
            })->orWhere(function ($roleQuery) use ($userId) {
                // As Division Head
                $roleQuery->where('division_head', $userId)
                    ->where('status', 'Supervisor Approved');
            })->orWhere(function ($roleQuery) use ($userId) {
                // As PMT (final level)
                $roleQuery->where('highest_supervisor', $userId)
                    ->where('status', 'Division Head Approved');
            });
        })
        ->when($year, function ($query) use ($year) {
            $query->where('year', $year);
        })
        ->when($semester, function ($query) use ($semester) {
            $query->where('semester', $semester);
        })
        ->with('user')
        ->get();
    }

    public function approveIpcr($id, $userId, $comments = null)
    {
        $ipcr = Ipcr::findOrFail($id);
        $oldStatus = $ipcr->status;
        $actingUser = \App\Models\User::find($userId);
        $isPmtUser = $actingUser ? $actingUser->isPmt() : false;
        
        if ($ipcr->status === 'Target Submitted') {
            $ipcr->status = 'Target Approved';
        } elseif ($ipcr->status === 'Accomplishment Submitted') {
            $ipcr->status = 'Supervisor Approved';
        } elseif ($ipcr->status === 'Supervisor Approved') {
            $ipcr->status = 'Division Head Approved';
        } elseif ($ipcr->status === 'Division Head Approved') {
            if ($ipcr->highest_supervisor && $ipcr->highest_supervisor != $userId && !$isPmtUser) {
                throw new \Exception("Only PMT can approve this stage.");
            }
            $ipcr->status = 'PMT Approved';
            $ipcr->pmt_id = $userId;
        } else {
            return $ipcr; // No transition
        }

        if ($comments) {
            $ipcr->comments = $comments;
        }

        $ipcr->save();
        
        $ipcr->logs()->create([
            'subject' => 'Approved',
            'content' => "Status changed from '{$oldStatus}' to '{$ipcr->status}'." . ($comments ? " Comment: {$comments}" : ""),
            'updated_by' => $userId,
        ]);
        
        return $ipcr;
    }

    public function submitIpcr($id, $userId)
    {
        $ipcr = Ipcr::findOrFail($id);
        $oldStatus = $ipcr->status;

        if ($ipcr->status === 'Draft Target') {
            $ipcr->status = 'Target Submitted';
        } elseif ($ipcr->status === 'Target Approved' || $ipcr->status === 'Draft Accomplishment') {
            $ipcr->status = 'Accomplishment Submitted';
        } else {
            return $ipcr; // No transition
        }

        $ipcr->save();

        $ipcr->logs()->create([
            'subject' => 'Submitted',
            'content' => "Status changed from '{$oldStatus}' to '{$ipcr->status}'.",
            'updated_by' => $userId,
        ]);

        return $ipcr;
    }

    public function getSupervisors()
    {
        // Fetch users who are likely supervisors (e.g., have certain roles or are section heads)
        // For now, returning all users as potential supervisors until role logic is clear
        return \App\Models\User::all(['id', 'name']);
    }

    public function getByYearAndSemester($userId, $year, $semester)
    {
        return Ipcr::where('userid', $userId)
            ->where('year', $year)
            ->where('semester', $semester)
            ->with(['coreFunctions', 'supportFunctions', 'strategicFunctions', 'logs'])
            ->first();
    }

    public function getIpcrLogs($ipcrId)
    {
        $ipcr = Ipcr::findOrFail($ipcrId);
        return $ipcr->logs()->with('user')->get();
    }

    public function getAutomatedSignatories($user)
    {
        $sectionHead = null;
        $divisionHead = null;
        $highestSupervisor = 35;

        if ($user) {
            try {
                if ($user->section) {
                    $section = DB::connection('user')
                        ->table('section')
                        ->select('head', 'subsection')
                        ->where('id', $user->section)
                        ->first();

                    if ($section) {
                        // Default: immediate supervisor is the user's own section head.
                        $sectionHead = $section->head;

                        // If current section has a parent section, highest supervisor should be
                        // the head of its parent/main section.
                        if (!empty($section->subsection)) {
                            $parentSectionHead = DB::connection('user')
                                ->table('section')
                                ->where('id', $section->subsection)
                                ->value('head');

                            if (!empty($parentSectionHead)) {
                                $highestSupervisor = $parentSectionHead;
                            }
                        }
                    }
                }
                if ($user->division) {
                    $divisionHead = DB::connection('user')
                        ->table('division')
                        ->where('id', $user->division)
                        ->value('head');
                }
            } catch (\Exception $e) {
                // Silently handle if tables don't exist yet or connection fails
            }
        }

        return [
            'supervisor_id' => $sectionHead ?? 1, // Fallback to 1 for dev
            'division_head' => $divisionHead,
            'highest_supervisor' => $highestSupervisor,
        ];
    }

    public function getStaffStatusList($userId, $year = null, $semester = null, array $filters = [], ?int $perPage = null)
    {
        $year = $year ?? date('Y');
        $actingUser = \App\Models\User::find($userId);
        if (!$actingUser) {
            return [];
        }

        $divisionId    = $filters['division'] ?? null;
        $sectionId     = $filters['section'] ?? null;
        $statusFilter  = $filters['status'] ?? null;
        $isAdmin = $actingUser->hasAdminAccessRight();

        $staffQuery = \App\Models\User::query()
            ->where('id', '!=', $userId);

        if (!empty($divisionId)) {
            $staffQuery->where('division', $divisionId);
        }

        if (!empty($sectionId)) {
            $staffQuery->where('section', $sectionId);
        }

        if (!$isAdmin) {
            // Find sections where the user is the head.
            $sections = DB::connection('user')
                ->table('section')
                ->where('head', $userId)
                ->pluck('id')
                ->toArray();

            $staffQuery->whereIn('section', $sections);

            // Exclude unit heads from Staff IPCR; they are handled in Staff SPCR.
            $unitHeadIds = DB::connection('user')->table('section')
                ->whereNotNull('head')
                ->whereNotNull('subsection')
                ->where('subsection', '!=', '')
                ->where('subsection', '!=', '0')
                ->pluck('head')
                ->unique()
                ->toArray();

            if (!empty($unitHeadIds)) {
                $staffQuery->whereNotIn('id', $unitHeadIds);
            }
        }

        // Staff IPCR must show only standard employees:
        // exclude all section heads (includes unit heads and main section heads).
        $sectionHeadIds = DB::connection('user')->table('section')
            ->whereNotNull('head')
            ->pluck('head')
            ->unique()
            ->toArray();

        if (!empty($sectionHeadIds)) {
            $staffQuery->whereNotIn('id', $sectionHeadIds);
        }

        // Pre-filter by IPCR status so pagination reflects the filtered set
        if ($statusFilter) {
            $matchingIds = Ipcr::where('year', $year)
                ->when($semester, fn($q) => $q->where('semester', $semester))
                ->where('status', $statusFilter)
                ->pluck('userid');
            $staffQuery->whereIn('id', $matchingIds);
        }

        $staffQuery->orderBy('lname')->orderBy('fname');

        if ($perPage) {
            $staffPaginator = $staffQuery->paginate($perPage);
            $rows = $this->buildStaffStatusRows($staffPaginator->getCollection(), $year, $semester);
            $staffPaginator->setCollection(collect($rows));
            return $staffPaginator;
        }

        $staff = $staffQuery->get();
        return $this->buildStaffStatusRows($staff, $year, $semester);
    }

    protected function buildStaffStatusRows($staff, $year, $semester = null): array
    {
        $results = [];
        foreach ($staff as $member) {
            $ipcrQuery = Ipcr::where('userid', $member->id)
                ->where('year', $year);

            if (!is_null($semester)) {
                // Filter to exact selected semester for dashboard cards.
                $ipcrQuery->where('semester', $semester);
            } else {
                // Default behavior for pages using year-only filter.
                $ipcrQuery->orderBy('semester', 'desc');
            }

            $ipcr = $ipcrQuery->first();
                
            $results[] = [
                'user' => $member,
                'ipcr' => $ipcr,
                'status' => $ipcr ? $ipcr->status : 'NOT SUBMITTED',
                'date_submitted' => $this->getSubmissionDate($ipcr)
            ];
        }

        return $results;
    }

    protected function getSubmissionDate($ipcr)
    {
        if (!$ipcr) return '---';
        
        $log = $ipcr->logs()
            ->whereIn('subject', ['Submitted', 'IPCR Target Created', 'IPCR Accomplishment Created'])
            ->orderBy('created_at', 'desc')
            ->first();
            
        return $log ? $log->created_at->format('M d, Y') : ($ipcr->ipcr_date ? date('M d, Y', strtotime($ipcr->ipcr_date)) : '---');
    }
}
