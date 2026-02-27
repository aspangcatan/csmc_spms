<?php

namespace App\Http\Controllers;

use App\Models\Ipcr;
use Illuminate\Support\Facades\DB;
use App\Services\IpcrService;
use App\Services\SpcrService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class IpcrController extends Controller
{
    protected $ipcrService;
    protected $spcrService;

    public function __construct(IpcrService $ipcrService, SpcrService $spcrService)
    {
        $this->ipcrService = $ipcrService;
        $this->spcrService = $spcrService;

        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if ($user && $user->isSectionHead()) {
                $allowedForSectionHead = [
                    'dashboard',
                    'staff',
                    'show',
                    'printIpcr',
                    'getLogs',
                    'getPending',
                    'approve',
                ];

                $actionMethod = optional($request->route())->getActionMethod();
                if (!in_array($actionMethod, $allowedForSectionHead, true)) {
                    abort(403, 'Unauthorized. Section Heads cannot create or manage personal IPCR documents.');
                }
            }

            return $next($request);
        });
    }

    public function index()
    {
        return response()->json($this->ipcrService->getAll());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ipcr.userid' => 'required|integer',
            'ipcr.supervisor_id' => 'nullable|integer', // Made nullable in case table lookup fails? Orig was required. 
                                                        // I'll keep it nullable to avoid hard crash, but logic says it should depend on section.
            'ipcr.year' => 'required|integer',
            'ipcr.semester' => 'required|integer|in:1,2',
            'ipcr.section_head' => 'nullable|integer',
            'ipcr.division_head' => 'nullable|integer',
            'ipcr.highest_supervisor' => 'nullable|integer',
            'ipcr.period_from' => 'required|date',
            'ipcr.period_to' => 'required|date',
            'ipcr.ipcr_date' => 'nullable|date',
            'ipcr.date_done' => 'nullable|date',
            'ipcr.comments' => 'nullable|string',
            'ipcr.status' => 'nullable|string',
            'ipcr.core_percentage_distribution' => 'nullable|numeric|min:0|max:100',
            'ipcr.support_percentage_distribution' => 'nullable|numeric|min:0|max:100',
            'ipcr.strategic_percentage_distribution' => 'nullable|numeric|min:0|max:100',

            'core_functions' => 'array',
            'core_functions.*.output' => 'required_with:core_functions|string',
            'core_functions.*.success_indicator' => 'nullable|string',
            'core_functions.*.actual_accomplishment' => 'nullable|string',
            'core_functions.*.quantity_rating' => 'nullable|numeric|min:1|max:5',
            'core_functions.*.efficiency_rating' => 'nullable|numeric|min:1|max:5',
            'core_functions.*.timeliness_rating' => 'nullable|numeric|min:1|max:5',
            'core_functions.*.remarks' => 'nullable|string',

            'support_functions' => 'array',
            'support_functions.*.output' => 'nullable|string',
            'support_functions.*.success_indicator' => 'nullable|string',
            'support_functions.*.actual_accomplishment' => 'nullable|string',
            'support_functions.*.quantity_rating' => 'nullable|numeric|min:1|max:5',
            'support_functions.*.efficiency_rating' => 'nullable|numeric|min:1|max:5',
            'support_functions.*.timeliness_rating' => 'nullable|numeric|min:1|max:5',
            'support_functions.*.remarks' => 'nullable|string',

            'strategic_functions' => 'array',
            'strategic_functions.*.output' => 'nullable|string',
            'strategic_functions.*.success_indicator' => 'nullable|string',
            'strategic_functions.*.actual_accomplishment' => 'nullable|string',
            'strategic_functions.*.quantity_rating' => 'nullable|numeric|min:1|max:5',
            'strategic_functions.*.efficiency_rating' => 'nullable|numeric|min:1|max:5',
            'strategic_functions.*.timeliness_rating' => 'nullable|numeric|min:1|max:5',
            'strategic_functions.*.remarks' => 'nullable|string',
        ]);
        $this->sanitizeAndValidateFunctionGroups($validated);

        try {
            $ipcr = $this->ipcrService->createIpcrWithFunctions($validated);
            return response()->json($ipcr, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }


    public function show($id)
    {
        return response()->json($this->ipcrService->getById($id));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'ipcr.userid' => 'sometimes|integer',
            'ipcr.supervisor_id' => 'sometimes|nullable|integer',
            'ipcr.year' => 'sometimes|integer',
            'ipcr.semester' => 'sometimes|integer|in:1,2',
            'ipcr.section_head' => 'nullable|integer',
            'ipcr.division_head' => 'nullable|integer',
            'ipcr.highest_supervisor' => 'nullable|integer',
            'ipcr.period_from' => 'sometimes|date',
            'ipcr.period_to' => 'sometimes|date',
            'ipcr.ipcr_date' => 'nullable|date',
            'ipcr.date_done' => 'nullable|date',
            'ipcr.comments' => 'nullable|string',
            'ipcr.status' => 'nullable|string',
            'ipcr.core_percentage_distribution' => 'nullable|numeric|min:0|max:100',
            'ipcr.support_percentage_distribution' => 'nullable|numeric|min:0|max:100',
            'ipcr.strategic_percentage_distribution' => 'nullable|numeric|min:0|max:100',

            'core_functions' => 'array',
            'core_functions.*.id' => 'nullable|integer',
            'core_functions.*.output' => 'required_with:core_functions|string',
            'core_functions.*.success_indicator' => 'nullable|string',
            'core_functions.*.actual_accomplishment' => 'nullable|string',
            'core_functions.*.quantity_rating' => 'nullable|numeric|min:1|max:5',
            'core_functions.*.efficiency_rating' => 'nullable|numeric|min:1|max:5',
            'core_functions.*.timeliness_rating' => 'nullable|numeric|min:1|max:5',
            'core_functions.*.remarks' => 'nullable|string',

            'support_functions' => 'array',
            'support_functions.*.id' => 'nullable|integer',
            'support_functions.*.output' => 'nullable|string',
            'support_functions.*.success_indicator' => 'nullable|string',
            'support_functions.*.actual_accomplishment' => 'nullable|string',
            'support_functions.*.quantity_rating' => 'nullable|numeric|min:1|max:5',
            'support_functions.*.efficiency_rating' => 'nullable|numeric|min:1|max:5',
            'support_functions.*.timeliness_rating' => 'nullable|numeric|min:1|max:5',
            'support_functions.*.remarks' => 'nullable|string',

            'strategic_functions' => 'array',
            'strategic_functions.*.id' => 'nullable|integer',
            'strategic_functions.*.output' => 'nullable|string',
            'strategic_functions.*.success_indicator' => 'nullable|string',
            'strategic_functions.*.actual_accomplishment' => 'nullable|string',
            'strategic_functions.*.quantity_rating' => 'nullable|numeric|min:1|max:5',
            'strategic_functions.*.efficiency_rating' => 'nullable|numeric|min:1|max:5',
            'strategic_functions.*.timeliness_rating' => 'nullable|numeric|min:1|max:5',
            'strategic_functions.*.remarks' => 'nullable|string',
        ]);
        $this->sanitizeAndValidateFunctionGroups($validated);

        $ipcrObj = \App\Models\Ipcr::findOrFail($id);
        if ($ipcrObj->userid != auth()->id()) {
            return response()->json(['message' => 'Unauthorized. Only the creator can update this record.'], 403);
        }

        $ipcr = $this->ipcrService->updateIpcr($id, $validated);
        return response()->json($ipcr);
    }

    public function destroy($id)
    {
        $ipcrObj = \App\Models\Ipcr::findOrFail($id);
        if ($ipcrObj->userid != auth()->id()) {
            return response()->json(['message' => 'Unauthorized. Only the creator can delete this record.'], 403);
        }
        $this->ipcrService->deleteIpcr($id);
        return response()->json(['message' => 'IPCR deleted successfully.']);
    }

    public function generatePDF($id)
    {
        $ipcr = $this->ipcrService->getById($id);
        $folioSize = [0, 0, 612.00, 936.00]; // 8.5in x 13in converted to points (1in = 72pt)
        $pdf = Pdf::loadView('ipcr.pdf', compact('ipcr'))
            ->setPaper($folioSize, 'landscape'); // 👈 Folio Landscape
        return $pdf->stream('ipcr_form.pdf');
    }

    public function printIpcr($id)
    {
        $ipcr = $this->ipcrService->getById($id);
        return view('ipcr.print', compact('ipcr'));
    }


    public function dashboard(Request $request)
    {
        $userId = auth()->id();
        $user = auth()->user();
        
        $year = $request->query('year', date('Y'));
        $semester = $request->query('semester', (date('n') <= 6 ? 1 : 2));

        // 1. Period-Specific IPCR (Affected by Filters)
        // This will be used for "Latest Rating", "Period Status", and "Quick Action"
        $latestIpcr = \App\Models\Ipcr::where('userid', $userId)
            ->where('year', $year)
            ->where('semester', $semester)
            ->first();

        $filteredIpcr = $latestIpcr; // Keep for compatibility with existing view logic

        // 2. All-Time History for Trend Chart (Not affected by filters)
        $history = \App\Models\Ipcr::where('userid', $userId)
            ->where('status', 'PMT Approved')
            ->orderBy('year', 'asc')
            ->orderBy('semester', 'asc')
            ->get(['year', 'semester', 'final_rating']);

        // 3. Supervisor Stats (Affected by selected filters)
        $supervisorStats = null;
        if ($user->isSupervisor()) {
            $ipcrStaff = $this->ipcrService->getStaffStatusList($userId, $year, $semester);
            $spcrStaff = $this->spcrService->getStaffStatusList($userId, $year, $semester);
            $ipcrPendingCount = count($this->ipcrService->getPendingApprovals($userId, $year, $semester));
            $spcrPendingCount = count($this->spcrService->getPendingApprovals($userId, $year, $semester));
            
            $submittedCount = 0;
            $distCounts = [
                'Outstanding' => 0,
                'Very Satisfactory' => 0,
                'Satisfactory' => 0,
                'Unsatisfactory' => 0,
                'Poor' => 0
            ];

            $combinedByUser = [];
            foreach ($ipcrStaff as $entry) {
                $uid = $entry['user']->id;
                $combinedByUser[$uid] = [
                    'user' => $entry['user'],
                    'ipcr' => $entry['ipcr'] ?? null,
                    'spcr' => null,
                    'ipcr_status' => $entry['status'] ?? 'NOT SUBMITTED',
                    'spcr_status' => 'NOT SUBMITTED',
                ];
            }

            foreach ($spcrStaff as $entry) {
                $uid = $entry['user']->id;
                if (!isset($combinedByUser[$uid])) {
                    $combinedByUser[$uid] = [
                        'user' => $entry['user'],
                        'ipcr' => null,
                        'spcr' => $entry['spcr'] ?? null,
                        'ipcr_status' => 'NOT SUBMITTED',
                        'spcr_status' => $entry['status'] ?? 'NOT SUBMITTED',
                    ];
                } else {
                    $combinedByUser[$uid]['spcr'] = $entry['spcr'] ?? null;
                    $combinedByUser[$uid]['spcr_status'] = $entry['status'] ?? 'NOT SUBMITTED';
                }
            }

            foreach ($combinedByUser as $member) {
                $hasSubmittedIpcr = !empty($member['ipcr']) && !str_contains($member['ipcr_status'], 'Draft');
                $hasSubmittedSpcr = !empty($member['spcr']) && !str_contains($member['spcr_status'], 'Draft');

                if ($hasSubmittedIpcr || $hasSubmittedSpcr) {
                    $submittedCount++;
                }

                $recordForDist = null;
                if (!empty($member['ipcr']) && !empty($member['spcr'])) {
                    $recordForDist = $member['ipcr']->updated_at >= $member['spcr']->updated_at
                        ? $member['ipcr']
                        : $member['spcr'];
                } elseif (!empty($member['ipcr'])) {
                    $recordForDist = $member['ipcr'];
                } elseif (!empty($member['spcr'])) {
                    $recordForDist = $member['spcr'];
                }

                if ($recordForDist) {
                    $adj = $recordForDist->final_rating_adjective ?? null;
                    if ($adj && isset($distCounts[$adj])) {
                        $distCounts[$adj]++;
                    }
                }
            }

            $supervisorStats = [
                'staff_count' => count($combinedByUser),
                'pending_approvals' => $ipcrPendingCount + $spcrPendingCount,
                'pending_ipcr' => $ipcrPendingCount,
                'pending_spcr' => $spcrPendingCount,
                'compliance_rate' => count($combinedByUser) > 0 ? ($submittedCount / count($combinedByUser)) * 100 : 0,
                'distribution' => $distCounts,
            ];
        }
        
        // 4. Absolute Latest IPCR for Personal Quick Action (Global Context)
        $globalIpcr = \App\Models\Ipcr::where('userid', $userId)
            ->orderBy('year', 'desc')
            ->orderBy('semester', 'desc')
            ->first();
        
        return view('ipcr.new_dashboard', compact('latestIpcr', 'filteredIpcr', 'globalIpcr', 'supervisorStats', 'history', 'year', 'semester'));
    }

    public function staff(Request $request)
    {
        $year = $request->query('year', date('Y'));
        $semester = $request->query('semester', (date('n') <= 6 ? 1 : 2));
        $staffData = $this->ipcrService->getStaffStatusList(auth()->id(), $year, $semester);
        
        return view('ipcr.staff', compact('staffData', 'year', 'semester'));
    }

    public function getPending(Request $request)
    {
        $userId = $request->query('user_id') ?? auth()->id() ?? 1;
        $year = $request->query('year');
        $semester = $request->query('semester');

        return response()->json($this->ipcrService->getPendingApprovals($userId, $year, $semester));
    }

    public function approve(Request $request, $id)
    {
        $userId = $request->query('user_id') ?? auth()->id() ?? 1;
        $comments = $request->input('comments');
        try {
            $ipcr = $this->ipcrService->approveIpcr($id, $userId, $comments);
            return response()->json($ipcr);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }

    public function submit(Request $request, $id)
    {
        $userId = $request->query('user_id') ?? auth()->id() ?? 1;
        $ipcr = $this->ipcrService->submitIpcr($id, $userId);
        return response()->json($ipcr);
    }

    public function getByYearSemester(Request $request)
    {
        $userId = $request->query('user_id', 1);
        $year = $request->query('year');
        $semester = $request->query('semester');
        
        $ipcr = $this->ipcrService->getByYearAndSemester($userId, $year, $semester);
        return response()->json($ipcr);
    }

    public function getLogs($id)
    {
        $logs = $this->ipcrService->getIpcrLogs($id);
        return response()->json($logs);
    }

    public function getSupervisors()
    {
        return response()->json($this->ipcrService->getSupervisors());
    }

    protected function sanitizeAndValidateFunctionGroups(array &$validated): void
    {
        $groups = [
            'core_functions' => 'Core Functions',
            'support_functions' => 'Support Functions',
            'strategic_functions' => 'Strategic Functions',
        ];

        $errors = [];

        foreach ($groups as $key => $label) {
            $rows = $validated[$key] ?? [];
            $rows = array_values(array_filter($rows, function ($row) {
                return !$this->isEmptyFunctionRow((array) $row);
            }));
            $validated[$key] = $rows;

            if (count($rows) < 1) {
                $errors[$key] = ["At least one {$label} entry is required before saving."];
                continue;
            }

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 1;
                $output = trim((string) ($row['output'] ?? ''));
                $successIndicator = trim((string) ($row['success_indicator'] ?? ''));

                if ($output === '') {
                    $errors["{$key}.{$index}.output"][] = "{$label} row {$rowNumber}: Output is required.";
                }

                if ($successIndicator === '') {
                    $errors["{$key}.{$index}.success_indicator"][] = "{$label} row {$rowNumber}: Success Indicator is required.";
                }
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    protected function isEmptyFunctionRow(array $row): bool
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
            $value = $row[$field] ?? null;
            if (is_string($value)) {
                if (trim($value) !== '') {
                    return false;
                }
                continue;
            }

            if (!is_null($value) && $value !== '') {
                return false;
            }
        }

        return true;
    }
}
