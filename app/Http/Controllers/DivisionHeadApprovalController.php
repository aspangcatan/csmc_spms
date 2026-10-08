<?php

namespace App\Http\Controllers;

use App\Models\Ipcr;
use App\Models\Spcr;
use App\Services\IpcrService;
use App\Services\SpcrService;
use Illuminate\Http\Request;

class DivisionHeadApprovalController extends Controller
{
    protected $ipcrService;
    protected $spcrService;

    public function __construct(IpcrService $ipcrService, SpcrService $spcrService)
    {
        $this->ipcrService = $ipcrService;
        $this->spcrService = $spcrService;
    }

    public function index(Request $request)
    {
        if (!auth()->user()->isDivisionHead()) {
            abort(403, 'Unauthorized. Only Division Heads can access this module.');
        }

        $userId = auth()->id();
        $year = $request->query('year', date('Y'));

        $pendingIpcrs = Ipcr::with('user')
            ->where('division_head', $userId)
            ->where('status', 'Supervisor Approved')
            ->where('year', $year)
            ->where('userid', '!=', $userId)
            ->orderBy('updated_at', 'desc')
            ->get();

        // A Section Head's SPCR supervisor is usually the Division Head too,
        // so include both the supervisor stages and the Division Head stage.
        $pendingSpcrs = Spcr::with('user')
            ->where(function ($query) use ($userId) {
                $query->where(function ($q) use ($userId) {
                    $q->where('supervisor_id', $userId)
                        ->whereIn('status', ['Target Submitted', 'Accomplishment Submitted']);
                })->orWhere(function ($q) use ($userId) {
                    $q->where('division_head_id', $userId)
                        ->where('status', 'Supervisor Approved');
                });
            })
            ->where('year', $year)
            ->where('userid', '!=', $userId)
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('division_head.index', compact('pendingIpcrs', 'pendingSpcrs', 'year'));
    }

    // Approves or rejects one or more IPCR/SPCR records in this Division Head's queue,
    // applying the same remarks to each. Rejected records go back to the employee.
    public function decide(Request $request)
    {
        if (!auth()->user()->isDivisionHead()) {
            return response()->json(['message' => 'Unauthorized. Only Division Heads can access this module.'], 403);
        }

        $validated = $request->validate([
            'type'     => 'required|in:ipcr,spcr',
            'decision' => 'required|in:approve,reject',
            'ids'      => 'required|array|min:1',
            'ids.*'    => 'integer',
            'remarks'  => 'nullable|string|max:2000|required_if:decision,reject',
        ]);

        $userId  = auth()->id();
        $remarks = isset($validated['remarks']) ? trim($validated['remarks']) : null;
        $isIpcr  = $validated['type'] === 'ipcr';
        $done    = [];
        $failed  = [];

        foreach ($validated['ids'] as $id) {
            try {
                $record = $isIpcr ? Ipcr::findOrFail($id) : Spcr::findOrFail($id);
                if (!$this->isInQueue($record, $userId)) {
                    throw new \Exception('Record is not awaiting your approval.');
                }

                if ($validated['decision'] === 'approve') {
                    $isIpcr
                        ? $this->ipcrService->approveIpcr($id, $userId, null, $remarks)
                        : $this->spcrService->approveSpcr($id, $userId, $remarks);
                } else {
                    $isIpcr
                        ? $this->ipcrService->rejectIpcr($id, $userId, $remarks)
                        : $this->spcrService->rejectSpcr($id, $userId, $remarks);
                }

                $done[] = $id;
            } catch (\Exception $e) {
                $failed[] = ['id' => $id, 'reason' => $e->getMessage()];
            }
        }

        return response()->json(['done' => $done, 'failed' => $failed]);
    }

    // Mirrors the queue filters in index().
    protected function isInQueue($record, $userId): bool
    {
        if ((int) $record->userid === (int) $userId) {
            return false;
        }

        if ($record instanceof Ipcr) {
            return (int) $record->division_head === (int) $userId && $record->status === 'Supervisor Approved';
        }

        return ((int) $record->supervisor_id === (int) $userId && in_array($record->status, ['Target Submitted', 'Accomplishment Submitted']))
            || ((int) $record->division_head_id === (int) $userId && $record->status === 'Supervisor Approved');
    }
}
