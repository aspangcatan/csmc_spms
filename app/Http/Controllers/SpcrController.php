<?php

namespace App\Http\Controllers;

use App\Services\SpcrService;
use Illuminate\Http\Request;
use App\Models\Spcr;
use App\Models\SpcrEntry;
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
        if (!auth()->user()->isSectionHead()) {
            abort(403, 'Unauthorized. Only Section Heads can access this module.');
        }

        $year = $request->query('year', date('Y'));
        $semester = $request->query('semester', (date('n') <= 6 ? 1 : 2));
        $user = auth()->user();
        
        $spcrs = Spcr::where('division_id', $user->division)
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
        return response()->json($spcr);
    }

    public function update(Request $request, $id)
    {
        $spcr = $this->spcrService->updateSpcr($id, $request->all());
        return response()->json($spcr);
    }

    public function submit($id)
    {
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

    public function destroy($id)
    {
        try {
            $this->spcrService->deleteSpcr($id);
            return response()->json(['message' => 'Deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }

    public function getByYearSemester(Request $request)
    {
        $divisionId = auth()->user()->division;
        $year = $request->query('year');
        $semester = $request->query('semester');
        
        $spcr = $this->spcrService->getByYearAndSemester($divisionId, $year, $semester);
        return response()->json($spcr);
    }

    public function getLogs($id)
    {
        $logs = $this->spcrService->getSpcrLogs($id);
        return response()->json($logs);
    }

    public function staff(Request $request)
    {
        $user = auth()->user();

        $managedSectionIds = DB::connection('user')->table('section')
            ->where('head', $user->id)
            ->pluck('id')
            ->toArray();

        $hasSubSections = !empty($managedSectionIds) && DB::connection('user')->table('section')
            ->whereIn('subsection', $managedSectionIds)
            ->exists();

        if (!$user->isDivisionHead() && !$hasSubSections) {
            abort(403, 'Unauthorized access. Only Division Heads or parent Section Heads can access this module.');
        }

        $year = $request->query('year', date('Y'));
        $staffData = $this->spcrService->getStaffStatusList(auth()->id(), $year);
        
        return view('spcr.staff', compact('staffData', 'year'));
    }

    public function print($id)
    {
        $spcr = Spcr::with(['user', 'entries', 'supervisor', 'divisionHead', 'highestSupervisor', 'pmt'])->findOrFail($id);
        return view('spcr.print', compact('spcr'));
    }
}
