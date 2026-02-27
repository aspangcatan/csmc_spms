<?php

namespace App\Services;

use App\Models\Spcr;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SpcrService
{
    public function createSpcrWithEntries(array $data)
    {
        return DB::transaction(function () use ($data) {
            $data['core_entries'] = $this->filterEntryRows($data['core_entries'] ?? []);
            $data['support_entries'] = $this->filterEntryRows($data['support_entries'] ?? []);
            $data['strategic_entries'] = $this->filterEntryRows($data['strategic_entries'] ?? []);

            $alreadyExists = Spcr::where('userid', $data['userid'])
                ->where('year', $data['year'])
                ->where('semester', $data['semester'])
                ->exists();

            if ($alreadyExists) {
                throw new \Exception('SPCR already exists for this year and semester.');
            }

            $user = User::find($data['userid']);
            $signatories = $this->getAutomatedSignatories($user);

            $spcrData = [
                'userid' => $data['userid'],
                'division_id' => $user->division,
                'year' => $data['year'],
                'semester' => $data['semester'],
                'period_from' => $data['period_from'] ?? ($data['semester'] == 1 ? "{$data['year']}-01-01" : "{$data['year']}-07-01"),
                'period_to' => $data['period_to'] ?? ($data['semester'] == 1 ? "{$data['year']}-06-30" : "{$data['year']}-12-31"),
                'spcr_date' => $data['spcr_date'] ?? null,
                'date_done' => $data['date_done'] ?? null,
                'supervisor_id' => $signatories['supervisor_id'],
                'division_head_id' => $signatories['division_head_id'],
                'highest_supervisor' => $signatories['highest_supervisor'],
                'pmt_id' => null,
                'status' => $data['status'] ?? 'Draft Target',
                'core_dist' => $data['core_dist'] ?? 50,
                'support_dist' => $data['support_dist'] ?? 10,
                'strategic_dist' => $data['strategic_dist'] ?? 40,
            ];

            $spcr = Spcr::create($spcrData);

            $this->saveEntries($spcr, 'core', $data['core_entries'] ?? []);
            $this->saveEntries($spcr, 'support', $data['support_entries'] ?? []);
            $this->saveEntries($spcr, 'strategic', $data['strategic_entries'] ?? []);

            $this->calculateRatings($spcr);

            $spcr->logs()->create([
                'subject' => 'SPCR Created',
                'content' => "SPCR created for Year {$spcr->year} Sem {$spcr->semester}.",
                'updated_by' => $data['userid']
            ]);

            return $spcr->load(['entries', 'logs']);
        });
    }

    protected function saveEntries(Spcr $spcr, $category, array $entries)
    {
        foreach ($entries as $entryData) {
            $entryData['average_rating'] = $this->calculateRowAvg($entryData);
            $spcr->entries()->create(array_merge($entryData, ['category' => $category]));
        }
    }

    public function updateSpcr($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $spcr = Spcr::findOrFail($id);
            $spcrData = $data['spcr'] ?? [];
            foreach (['status', 'year', 'semester', 'period_from', 'period_to', 'spcr_date', 'date_done', 'core_dist', 'support_dist', 'strategic_dist', 'highest_supervisor'] as $key) {
                if (array_key_exists($key, $data)) {
                    $spcrData[$key] = $data[$key];
                }
            }
            $spcr->update($spcrData);

            if (isset($data['core_entries'])) $this->syncEntries($spcr, 'core', $data['core_entries']);
            if (isset($data['support_entries'])) $this->syncEntries($spcr, 'support', $data['support_entries']);
            if (isset($data['strategic_entries'])) $this->syncEntries($spcr, 'strategic', $data['strategic_entries']);

            $this->calculateRatings($spcr);
            return $spcr->load('entries');
        });
    }

    protected function syncEntries(Spcr $spcr, $category, array $entries)
    {
        $entries = $this->filterEntryRows($entries);
        $receivedIds = array_filter(array_column($entries, 'id'));
        $spcr->entries()->where('category', $category)->whereNotIn('id', $receivedIds)->delete();

        foreach ($entries as $entryData) {
            $entryData['average_rating'] = $this->calculateRowAvg($entryData);
            if (!empty($entryData['id'])) {
                $spcr->entries()->where('id', $entryData['id'])->update($entryData);
            } else {
                $spcr->entries()->create(array_merge($entryData, ['category' => $category]));
            }
        }
    }

    protected function filterEntryRows(array $entries): array
    {
        return array_values(array_filter($entries, function ($entry) {
            return !$this->isEmptyEntryRow((array) $entry);
        }));
    }

    protected function isEmptyEntryRow(array $entry): bool
    {
        $fields = [
            'output',
            'success_indicator',
            'accountability',
            'actual_accomplishment',
            'accomplishment_rate',
            'quantity_rating',
            'efficiency_rating',
            'timeliness_rating',
            'remarks',
        ];

        foreach ($fields as $field) {
            if ($this->hasMeaningfulValue($entry[$field] ?? null)) {
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

    protected function calculateRowAvg($data)
    {
        $q = $data['quantity_rating'] ?? 0;
        $e = $data['efficiency_rating'] ?? 0;
        $t = $data['timeliness_rating'] ?? 0;
        $ratings = array_filter([$q, $e, $t], fn($v) => $v > 0);
        return count($ratings) > 0 ? array_sum($ratings) / count($ratings) : 0;
    }

    public function calculateRatings(Spcr $spcr)
    {
        $spcr->load('entries');
        
        $calculateAvg = function($entries) {
            $ratings = $entries->pluck('average_rating')->filter(fn($v) => $v > 0);
            return $ratings->count() > 0 ? $ratings->average() : 0;
        };

        $coreAvg = $calculateAvg($spcr->coreEntries);
        $supportAvg = $calculateAvg($spcr->supportEntries);
        $strategicAvg = $calculateAvg($spcr->strategicEntries);

        $finalAvg = ($coreAvg * ($spcr->core_dist / 100)) + 
                    ($supportAvg * ($spcr->support_dist / 100)) + 
                    ($strategicAvg * ($spcr->strategic_dist / 100));

        $spcr->final_average_rating = $finalAvg;
        $spcr->final_rating = $finalAvg;
        $spcr->final_rating_adjective = $this->getAdjectiveRating($finalAvg);
        $spcr->save();
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

    public function getAutomatedSignatories($user)
    {
        $supervisorId = null;
        $divisionHead = null;
        $highestSupervisor = 35;

        if ($user && $user->division) {
            $divisionHead = DB::connection('user')
                ->table('division')
                ->where('id', $user->division)
                ->value('head');
        }

        if ($user && $user->section) {
            $section = DB::connection('user')
                ->table('section')
                ->select('head', 'subsection')
                ->where('id', $user->section)
                ->first();

            // Unit-head flow: section has a parent section, so immediate supervisor
            // should be the head of that parent section.
            if ($section && !empty($section->subsection)) {
                $supervisorId = DB::connection('user')
                    ->table('section')
                    ->where('id', $section->subsection)
                    ->value('head');

                // For unit-head flow, next highest approver is division head.
                $highestSupervisor = $divisionHead ?? 35;
            }
        }

        return [
            'supervisor_id' => $supervisorId ?? $divisionHead ?? 1,
            'division_head_id' => $divisionHead ?? 1,
            'highest_supervisor' => $highestSupervisor,
        ];
    }

    public function submitSpcr($id, $userId)
    {
        $spcr = Spcr::findOrFail($id);
        $oldStatus = $spcr->status;
        
        if ($oldStatus === 'Draft Target') {
            $spcr->status = 'Target Submitted';
        } elseif ($oldStatus === 'Target Approved' || $oldStatus === 'Draft Accomplishment') {
            $spcr->status = 'Accomplishment Submitted';
        }

        $spcr->save();

        $spcr->logs()->create([
            'subject' => 'Submitted',
            'content' => "Status changed from {$oldStatus} to {$spcr->status}.",
            'updated_by' => $userId
        ]);

        return $spcr;
    }

    public function approveSpcr($id, $userId)
    {
        $spcr = Spcr::findOrFail($id);
        $actingUser = User::find($userId);
        $isPmtUser = $actingUser ? $actingUser->isPmt() : false;
        
        // Authorization check based on status
        if ($spcr->status === 'Target Submitted' || $spcr->status === 'Accomplishment Submitted') {
            if ($spcr->supervisor_id != $userId) throw new \Exception("Only Supervisor can approve this stage.");
        } elseif ($spcr->status === 'Supervisor Approved') {
            if ($spcr->division_head_id != $userId) throw new \Exception("Only Division Head can approve this stage.");
        } elseif ($spcr->status === 'Division Head Approved') {
            if ($spcr->highest_supervisor && $spcr->highest_supervisor != $userId && !$isPmtUser) {
                throw new \Exception("Only PMT can approve this stage.");
            }
        }

        $oldStatus = $spcr->status;
        $newStatus = $oldStatus;

        if ($oldStatus === 'Target Submitted') {
            $newStatus = 'Target Approved';
        } elseif ($oldStatus === 'Accomplishment Submitted') {
            $newStatus = 'Supervisor Approved';
        } elseif ($oldStatus === 'Supervisor Approved') {
            $newStatus = 'Division Head Approved';
        } elseif ($oldStatus === 'Division Head Approved') {
            $newStatus = 'PMT Approved';
            $spcr->pmt_id = $userId;
        }

        $spcr->status = $newStatus;
        $spcr->save();

        $spcr->logs()->create([
            'subject' => $newStatus,
            'content' => "Status changed from {$oldStatus} to {$newStatus}.",
            'updated_by' => $userId
        ]);

        return $spcr;
    }

    public function deleteSpcr($id)
    {
        $spcr = Spcr::findOrFail($id);
        if ($spcr->status !== 'Draft Target') {
            throw new \Exception("Only Draft Target SPCR can be deleted.");
        }
        return $spcr->delete();
    }

    public function getByYearAndSemester($divisionId, $year, $semester)
    {
        return Spcr::where('division_id', $divisionId)
            ->where('year', $year)
            ->where('semester', $semester)
            ->with(['user', 'divisionHead'])
            ->first();
    }

    public function getSpcrLogs($id)
    {
        $spcr = Spcr::findOrFail($id);
        return $spcr->logs()->with('user')->orderBy('created_at', 'desc')->get();
    }

    public function getPendingApprovals($userId, $year = null, $semester = null)
    {
        return Spcr::where(function ($query) use ($userId) {
            $query->where(function ($roleQuery) use ($userId) {
                // As Supervisor
                $roleQuery->where('supervisor_id', $userId)
                    ->whereIn('status', ['Target Submitted', 'Accomplishment Submitted']);
            })->orWhere(function ($roleQuery) use ($userId) {
                // As Division Head
                $roleQuery->where('division_head_id', $userId)
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

    public function getStaffStatusList($userId, $year = null, $semester = null, array $filters = [], ?int $perPage = null)
    {
        $year = $year ?? date('Y');
        $user = \App\Models\User::find($userId);
        if (!$user) {
            return [];
        }

        $divisionFilter = $filters['division'] ?? null;
        $sectionFilter = $filters['section'] ?? null;
        $isAdmin = $user->hasAdminAccessRight();
        $sectionHeadIds = DB::connection('user')->table('section')
            ->whereNotNull('head')
            ->pluck('head')
            ->unique()
            ->toArray();

        if (empty($sectionHeadIds)) {
            return [];
        }

        $staffQuery = \App\Models\User::query()
            ->where('id', '!=', $userId)
            // Staff SPCR must show only heads (unit head + section head).
            ->whereIn('id', $sectionHeadIds);

        if (!empty($divisionFilter)) {
            $staffQuery->where('division', $divisionFilter);
        }

        if (!empty($sectionFilter)) {
            $staffQuery->where('section', $sectionFilter);
        }

        if (!$isAdmin) {
            $divisionId = $user->division;
            $isDivisionHead = $user->isDivisionHead();
            $staffIds = [];

            if ($isDivisionHead && $divisionId) {
                $divisionSectionHeadIds = DB::connection('user')->table('section')
                    ->where('division', $divisionId)
                    ->whereNotNull('head')
                    ->pluck('head')
                    ->toArray();

                $staffIds = array_merge($staffIds, $divisionSectionHeadIds);
            }

            // If user heads a parent section that has child sections,
            // include those child section heads as managed staff.
            $managedSectionIds = DB::connection('user')->table('section')
                ->where('head', $userId)
                ->pluck('id')
                ->toArray();

            if (!empty($managedSectionIds)) {
                $subSectionHeadIds = DB::connection('user')->table('section')
                    ->whereIn('subsection', $managedSectionIds)
                    ->whereNotNull('head')
                    ->pluck('head')
                    ->toArray();

                $staffIds = array_merge($staffIds, $subSectionHeadIds);
            }

            $staffIds = array_values(array_unique(array_filter($staffIds, fn($id) => (int) $id !== (int) $userId)));
            if (empty($staffIds)) {
                return [];
            }

            $staffQuery->whereIn('id', $staffIds);
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
            $spcrQuery = Spcr::where('userid', $member->id)
                ->where('year', $year);

            if (!is_null($semester)) {
                $spcrQuery->where('semester', $semester);
            } else {
                // Default behavior for pages using year-only filter.
                $spcrQuery->orderBy('semester', 'desc');
            }

            $spcr = $spcrQuery->first();
                
            $results[] = [
                'user' => $member,
                'spcr' => $spcr,
                'status' => $spcr ? $spcr->status : 'NOT SUBMITTED',
                'date_submitted' => $this->getSubmissionDate($spcr)
            ];
        }

        return $results;
    }

    protected function getSubmissionDate($spcr)
    {
        if (!$spcr) return '---';
        
        $log = $spcr->logs()
            ->whereIn('subject', ['Submitted', 'SPCR Created'])
            ->orderBy('created_at', 'desc')
            ->first();
            
        return $log ? $log->created_at->format('M d, Y') : $spcr->created_at->format('M d, Y');
    }
}
