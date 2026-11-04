<?php

namespace App\Http\Controllers;

use App\Models\Ipcr;
use App\Models\Spcr;
use App\Models\User;
use App\Services\IpcrService;
use App\Services\PmtApprovalService;
use App\Services\SpcrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PmtApprovalController extends Controller
{
    protected $ipcrService;
    protected $spcrService;
    protected $pmtApprovalService;

    public function __construct(IpcrService $ipcrService, SpcrService $spcrService, PmtApprovalService $pmtApprovalService)
    {
        $this->ipcrService = $ipcrService;
        $this->spcrService = $spcrService;
        $this->pmtApprovalService = $pmtApprovalService;
    }

    public function index(Request $request)
    {
        if (!auth()->user()->isPmt()) {
            abort(403, 'Unauthorized. Only PMT can access this module.');
        }

        $userId = auth()->id();
        $filters = $this->filters($request);

        $pendingIpcrs = $this->filteredQuery(Ipcr::class, $filters)
            ->with('user')
            ->where('status', 'Division Head Approved')
            ->orderBy('updated_at', 'desc')
            ->get();

        $pendingSpcrs = $this->filteredQuery(Spcr::class, $filters)
            ->with('user')
            ->where('status', 'Division Head Approved')
            ->orderBy('updated_at', 'desc')
            ->get();

        // Each member only sees records they haven't approved yet, with the overall PMT x/N progress.
        $pmtTotal = count($this->pmtApprovalService->memberIds());
        $pendingIpcrs = $this->withPmtProgress('ipcr', $pendingIpcrs, $userId);
        $pendingSpcrs = $this->withPmtProgress('spcr', $pendingSpcrs, $userId);

        return view('pmt.index', array_merge(
            compact('pendingIpcrs', 'pendingSpcrs', 'pmtTotal'),
            $this->filterOptions($filters)
        ));
    }

    // Approval status overview of IPCR/SPCR submissions for PMT members.
    public function dashboard(Request $request)
    {
        if (!auth()->user()->isPmt()) {
            abort(403, 'Unauthorized. Only PMT can access this module.');
        }

        $userId = auth()->id();
        $filters = $this->filters($request);
        $pmtTotal = count($this->pmtApprovalService->memberIds());

        $statusCounts = [];
        $pending = [];
        $approved = [];
        foreach (['ipcr' => Ipcr::class, 'spcr' => Spcr::class] as $type => $model) {
            $statusCounts[$type] = $this->filteredQuery($model, $filters)
                ->select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status')
                ->all();

            $pendingRecords = $this->filteredQuery($model, $filters)
                ->with('user')
                ->where('status', 'Division Head Approved')
                ->orderBy('updated_at', 'desc')
                ->get();
            $approvers = $this->pmtApprovalService->approverIdsFor($type, $pendingRecords->pluck('id')->all());
            $pending[$type] = $pendingRecords->each(function ($record) use ($approvers, $userId) {
                $ids = $approvers[$record->id] ?? [];
                $record->pmt_approved_count = count($ids);
                $record->signed_by_me = in_array((int) $userId, $ids, true);
            });

            $approved[$type] = $this->filteredQuery($model, $filters)
                ->with('user')
                ->where('status', 'PMT Approved')
                ->orderBy('updated_at', 'desc')
                ->limit(10)
                ->get();
        }

        $countFor = fn ($status) => ($statusCounts['ipcr'][$status] ?? 0) + ($statusCounts['spcr'][$status] ?? 0);
        $totalRecords = array_sum($statusCounts['ipcr']) + array_sum($statusCounts['spcr']);
        $awaitingPmt = $countFor('Division Head Approved');
        $awaitingMe = $pending['ipcr']->where('signed_by_me', false)->count() + $pending['spcr']->where('signed_by_me', false)->count();

        $summary = [
            'total'        => $totalRecords,
            'pmt_approved' => $countFor('PMT Approved'),
            'awaiting_pmt' => $awaitingPmt,
            'awaiting_me'  => $awaitingMe,
            'in_progress'  => $totalRecords - $countFor('PMT Approved') - $awaitingPmt,
        ];

        return view('pmt.dashboard', array_merge(
            compact('statusCounts', 'pending', 'approved', 'summary', 'pmtTotal'),
            $this->filterOptions($filters)
        ));
    }

    protected function withPmtProgress(string $type, $records, $userId)
    {
        $approvers = $this->pmtApprovalService->approverIdsFor($type, $records->pluck('id')->all());

        return $records
            ->reject(fn ($record) => in_array((int) $userId, $approvers[$record->id] ?? [], true))
            ->each(fn ($record) => $record->pmt_approved_count = count($approvers[$record->id] ?? []))
            ->values();
    }

    // Year, month and org filters shared by the queue and the dashboard. Month is when the
    // record last changed status (for the queue: when it reached the PMT).
    protected function filters(Request $request): array
    {
        $month = (int) $request->query('month');

        return [
            'year'     => (int) $request->query('year', date('Y')),
            'month'    => $month >= 1 && $month <= 12 ? $month : null,
            'division' => $request->query('division') ?: null,
            'section'  => $request->query('section') ?: null,
            'search'   => trim((string) $request->query('search', '')),
        ];
    }

    protected function filteredQuery(string $model, array $filters)
    {
        $query = $model::query()
            ->where('year', $filters['year'])
            ->when($filters['month'], fn ($q, $month) => $q->whereMonth('updated_at', $month));

        // Owners live in the user DB, so resolve the org/name filters to user ids first.
        if ($filters['division'] || $filters['section'] || $filters['search'] !== '') {
            $userIds = User::query()
                ->when($filters['division'], fn ($q, $division) => $q->where('division', $division))
                ->when($filters['section'], fn ($q, $section) => $q->where('section', $section))
                ->search($filters['search'])
                ->pluck('id');
            $query->whereIn('userid', $userIds);
        }

        return $query;
    }

    protected function filterOptions(array $filters): array
    {
        $divisions = DB::connection('user')
            ->table('division')
            ->orderBy('description')
            ->get(['id', 'description']);

        $sections = DB::connection('user')
            ->table('section')
            ->when($filters['division'], fn ($q, $division) => $q->where('division', $division))
            ->orderBy('description')
            ->get(['id', 'description', 'division']);

        return array_merge($filters, compact('divisions', 'sections'));
    }

    // Approves or rejects one or more IPCR/SPCR records, applying the same remarks to each.
    // Approvals carry the member's signature, printed in the CSMC PMT box.
    public function decide(Request $request)
    {
        if (!auth()->user()->isPmt()) {
            return response()->json(['message' => 'Unauthorized. Only PMT can access this module.'], 403);
        }

        $validated = $request->validate([
            'type'      => 'required|in:ipcr,spcr',
            'decision'  => 'required|in:approve,reject',
            'ids'       => 'required|array|min:1',
            'ids.*'     => 'integer',
            'remarks'   => 'nullable|string|max:2000|required_if:decision,reject',
            'signature' => 'nullable|string|required_if:decision,approve',
        ]);

        if ($validated['decision'] === 'approve' && !PmtApprovalService::isValidSignature($validated['signature'])) {
            return response()->json(['message' => 'Please draw your signature before approving.'], 422);
        }

        $userId    = auth()->id();
        $remarks   = isset($validated['remarks']) ? trim($validated['remarks']) : null;
        $signature = $validated['signature'] ?? null;
        $model     = $validated['type'] === 'ipcr' ? Ipcr::class : Spcr::class;
        $done      = [];
        $failed    = [];

        foreach ($validated['ids'] as $id) {
            try {
                $record = $model::findOrFail($id);
                if ($record->status !== 'Division Head Approved') {
                    throw new \Exception('Record is not awaiting PMT approval.');
                }

                if ($validated['decision'] === 'approve') {
                    $validated['type'] === 'ipcr'
                        ? $this->ipcrService->approveIpcr($id, $userId, null, $remarks, $signature)
                        : $this->spcrService->approveSpcr($id, $userId, $remarks, $signature);
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
