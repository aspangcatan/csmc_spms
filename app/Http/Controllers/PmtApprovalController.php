<?php

namespace App\Http\Controllers;

use App\Models\Ipcr;
use App\Models\Spcr;
use App\Services\IpcrService;
use App\Services\SpcrService;
use Illuminate\Http\Request;

class PmtApprovalController extends Controller
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
        if (!auth()->user()->isPmt()) {
            abort(403, 'Unauthorized. Only PMT can access this module.');
        }

        $userId = auth()->id();
        $year = $request->query('year', date('Y'));

        $pendingIpcrs = Ipcr::with('user')
            ->where('status', 'Division Head Approved')
            ->where('year', $year)
            ->orderBy('updated_at', 'desc')
            ->get();

        $pendingSpcrs = Spcr::with('user')
            ->where('status', 'Division Head Approved')
            ->where('year', $year)
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('pmt.index', compact('pendingIpcrs', 'pendingSpcrs', 'year'));
    }

    // Approves or rejects one or more IPCR/SPCR records, applying the same remarks to each.
    public function decide(Request $request)
    {
        if (!auth()->user()->isPmt()) {
            return response()->json(['message' => 'Unauthorized. Only PMT can access this module.'], 403);
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
        $model   = $validated['type'] === 'ipcr' ? Ipcr::class : Spcr::class;
        $done    = [];
        $failed  = [];

        foreach ($validated['ids'] as $id) {
            try {
                $record = $model::findOrFail($id);
                if ($record->status !== 'Division Head Approved') {
                    throw new \Exception('Record is not awaiting PMT approval.');
                }

                if ($validated['decision'] === 'approve') {
                    $validated['type'] === 'ipcr'
                        ? $this->ipcrService->approveIpcr($id, $userId, null, $remarks)
                        : $this->spcrService->approveSpcr($id, $userId, $remarks);
                } else {
                    $validated['type'] === 'ipcr'
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
}
