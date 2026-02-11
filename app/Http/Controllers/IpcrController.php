<?php

namespace App\Http\Controllers;

use App\Models\Ipcr;
use Illuminate\Support\Facades\DB;
use App\Services\IpcrService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class IpcrController extends Controller
{
    protected $ipcrService;

    public function __construct(IpcrService $ipcrService)
    {
        $this->ipcrService = $ipcrService;
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

        $ipcr = $this->ipcrService->createIpcrWithFunctions($validated);

        return response()->json($ipcr, 201);
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

        // 3. Supervisor Stats (Global/Current Year - Not affected by filters)
        $supervisorStats = null;
        if ($user->isSupervisor()) {
            $currentYear = date('Y');
            $staff = $this->ipcrService->getStaffStatusList($userId, $currentYear);
            $pendingCount = count($this->ipcrService->getPendingApprovals($userId));
            
            $submittedCount = 0;
            $distCounts = [
                'Outstanding' => 0,
                'Very Satisfactory' => 0,
                'Satisfactory' => 0,
                'Unsatisfactory' => 0,
                'Poor' => 0
            ];

            foreach($staff as $s) {
                if ($s['ipcr']) {
                    if (!str_contains($s['status'], 'Draft')) {
                        $submittedCount++;
                    }
                    $adj = $s['ipcr']->final_rating_adjective;
                    if ($adj && isset($distCounts[$adj])) {
                        $distCounts[$adj]++;
                    }
                }
            }

            $supervisorStats = [
                'staff_count' => count($staff),
                'pending_approvals' => $pendingCount,
                'compliance_rate' => count($staff) > 0 ? ($submittedCount / count($staff)) * 100 : 0,
                'distribution' => $distCounts
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
        $staffData = $this->ipcrService->getStaffStatusList(auth()->id(), $year);
        
        return view('ipcr.staff', compact('staffData', 'year'));
    }

    public function getPending(Request $request)
    {
        $userId = $request->query('user_id') ?? auth()->id() ?? 1;
        return response()->json($this->ipcrService->getPendingApprovals($userId));
    }

    public function approve(Request $request, $id)
    {
        $userId = $request->query('user_id') ?? auth()->id() ?? 1;
        $comments = $request->input('comments');
        $ipcr = $this->ipcrService->approveIpcr($id, $userId, $comments);
        return response()->json($ipcr);
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
}
