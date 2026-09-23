<?php

namespace App\Http\Controllers;

use App\Services\SpcrService;
use Illuminate\Http\Request;
use App\Models\Spcr;
use App\Models\SpcrEntry;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SpcrController extends Controller
{
    protected $spcrService;

    public function __construct(SpcrService $spcrService)
    {
        $this->spcrService = $spcrService;
    }

    public function index(Request $request)
    {
        if (!auth()->user()->headsAnySection()) {
            abort(403, 'Unauthorized. Only Section Heads can access this module.');
        }

        $year = $request->query('year', date('Y'));
        $semester = $request->query('semester', (date('n') <= 6 ? 1 : 2));
        $user = auth()->user();
        
        $spcrs = Spcr::where('userid', $user->id)
            ->where('year', $year)
            ->where('semester', $semester)
            ->with(['user', 'divisionHead'])
            ->get();

        return view('spcr.index', compact('spcrs', 'year', 'semester'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer',
            'semester' => 'required|integer|in:1,2',
            'period_from' => 'nullable|date',
            'period_to' => 'nullable|date',
            'spcr_date' => 'nullable|date',
            'date_done' => 'nullable|date',
            'status' => 'nullable|string|in:Draft Target,Target Submitted,Target Approved,Draft Accomplishment,Accomplishment Submitted,Supervisor Approved,Division Head Approved,PMT Approved',
            'core_entries' => 'array',
            'support_entries' => 'array',
            'strategic_entries' => 'array',
            'core_entries.*.output' => 'string|nullable',
            'core_entries.*.success_indicator' => 'string|nullable',
            'core_entries.*.accountability' => 'string|nullable',
            'core_entries.*.actual_accomplishment' => 'string|nullable',
            'core_entries.*.accomplishment_rate' => 'string|nullable',
            // ... add more as needed
        ]);

        $validated['userid'] = auth()->id();
        try {
            $spcr = $this->spcrService->createSpcrWithEntries($validated);
            return response()->json($spcr, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show($id)
    {
        $spcr = Spcr::with(['user', 'entries', 'supervisor', 'divisionHead', 'pmt', 'logs.user'])->findOrFail($id);
        $this->authorizeSpcrView($spcr);
        return response()->json($spcr);
    }

    public function update(Request $request, $id)
    {
        $spcrObj = Spcr::findOrFail($id);
        $authUser = auth()->user();
        if (!$authUser->canEditSpcr($spcrObj)) {
            return response()->json(['message' => 'Unauthorized. Only the creator or an assigned approver can update this record.'], 403);
        }

        // Ownership and signatories never change through an update.
        $data = $request->except(['spcr', 'highest_supervisor']);

        $newStatus = $data['status'] ?? null;
        if ($newStatus !== null && $newStatus !== $spcrObj->status) {
            $isOwner = (int) $spcrObj->userid === (int) $authUser->id;
            if (!$isOwner || !$this->isOwnerStatusTransition($spcrObj->status, $newStatus)) {
                return response()->json(['message' => 'Unauthorized status change. Use the approval workflow.'], 403);
            }
        }

        $spcr = $this->spcrService->updateSpcr($id, $data);
        return response()->json($spcr);
    }

    public function submit($id)
    {
        if ((int) Spcr::findOrFail($id)->userid !== (int) auth()->id()) {
            return response()->json(['message' => 'Unauthorized. Only the creator can submit this record.'], 403);
        }
        $spcr = $this->spcrService->submitSpcr($id, auth()->id());
        return response()->json($spcr);
    }

    public function approve($id)
    {
        try {
            $spcr = $this->spcrService->approveSpcr($id, auth()->id());
            return response()->json($spcr);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }

    public function bulkApprove(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);

        $userId = auth()->id();
        $approved = [];
        $failed   = [];

        foreach ($request->ids as $id) {
            try {
                $this->spcrService->approveSpcr($id, $userId);
                $approved[] = $id;
            } catch (\Exception $e) {
                $failed[] = ['id' => $id, 'reason' => $e->getMessage()];
            }
        }

        return response()->json(['approved' => $approved, 'failed' => $failed]);
    }

    public function destroy($id)
    {
        if ((int) Spcr::findOrFail($id)->userid !== (int) auth()->id()) {
            return response()->json(['message' => 'Unauthorized. Only the creator can delete this record.'], 403);
        }

        try {
            $this->spcrService->deleteSpcr($id);
            return response()->json(['message' => 'Deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }

    public function getByYearSemester(Request $request)
    {
        $year = $request->query('year');
        $semester = $request->query('semester');
        
        // SPCRs are one per user, so load the logged-in user's own record.
        $spcr = $this->spcrService->getByYearAndSemester(auth()->id(), $year, $semester);
        return response()->json($spcr);
    }

    public function getLogs($id)
    {
        $this->authorizeSpcrView(Spcr::findOrFail($id));
        $logs = $this->spcrService->getSpcrLogs($id);
        return response()->json($logs);
    }

    public function staff(Request $request)
    {
        $user = auth()->user();

        if (!$user->canAccessSpcrStaff()) {
            abort(403, 'Unauthorized access. You do not have permission to view staff SPCR records.');
        }

        $year     = $request->query('year', date('Y'));
        $division = $request->query('division');
        $section  = $request->query('section');
        $status   = $request->query('status');
        $perPage  = 10;

        $staffData = $this->spcrService->getStaffStatusList(auth()->id(), $year, null, [
            'division' => $division,
            'section'  => $section,
            'status'   => $status,
        ], $perPage);

        $divisions = DB::connection('user')
            ->table('division')
            ->orderBy('description')
            ->get(['id', 'description']);

        $sections = DB::connection('user')
            ->table('section')
            ->when($division, function ($query) use ($division) {
                $query->where('division', $division);
            })
            ->orderBy('description')
            ->get(['id', 'description', 'division']);
        
        return view('spcr.staff', compact('staffData', 'year', 'divisions', 'sections', 'division', 'section', 'status'));
    }

    public function print($id)
    {
        $spcr = Spcr::with(['user', 'entries', 'supervisor', 'divisionHead', 'highestSupervisor', 'pmt'])->findOrFail($id);
        $this->authorizeSpcrView($spcr);
        $viewName = $this->resolveSpcrPrintView($spcr->userid);
        return view($viewName, compact('spcr'));
    }

    protected function resolveSpcrPrintView($creatorId): string
    {
        try {
            $creator = User::find($creatorId);

            if ($creator && $creator->isUnitHead()) {
                return 'spcr.print_subsection';
            }

            return 'spcr.print';
        } catch (\Exception $e) {
            return 'spcr.print';
        }
    }

    protected function authorizeSpcrView(Spcr $spcr): void
    {
        if (!auth()->user()->canViewSpcr($spcr)) {
            abort(403, 'Unauthorized. You cannot view this SPCR.');
        }
    }
}
