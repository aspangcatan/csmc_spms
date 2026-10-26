<?php

namespace App\Services;

use App\Models\PmtApproval;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// Tracks the PMT phase, where every PMT member must approve before a record becomes 'PMT Approved'.
class PmtApprovalService
{
    protected $memberIds = null;

    // All users with PMT level in the user DB; the PMT phase is complete once each of them approves.
    public function memberIds(): array
    {
        if ($this->memberIds === null) {
            try {
                $this->memberIds = DB::connection('user')
                    ->table('user_priv')
                    ->where('syscode', 'e-spms')
                    ->where('level', 'PMT')
                    ->distinct()
                    ->pluck('user_id')
                    ->map(fn ($id) => (int) $id)
                    ->all();
            } catch (\Exception $e) {
                $this->memberIds = [];
            }
        }

        return $this->memberIds;
    }

    // Approver ids per record id for the given type, limited to current PMT members.
    public function approverIdsFor(string $type, array $recordIds): array
    {
        if (empty($recordIds)) {
            return [];
        }

        return PmtApproval::where('record_type', $type)
            ->whereIn('record_id', $recordIds)
            ->whereIn('user_id', $this->memberIds())
            ->get(['record_id', 'user_id'])
            ->groupBy('record_id')
            ->map(fn ($rows) => $rows->pluck('user_id')->map(fn ($id) => (int) $id)->all())
            ->all();
    }

    // {approved, total, approvers[], pending[]} for the stepper badge and review modal.
    public function progress(string $type, $record): array
    {
        $memberIds = $this->memberIds();
        $total = count($memberIds);

        if ($record->status === 'PMT Approved') {
            $approverIds = $this->approverIdsFor($type, [$record->id])[$record->id] ?? [];
            // Records approved before multi-member PMT approval have no rows; show them as complete.
            $approved = empty($approverIds) ? $total : count($approverIds);
        } elseif ($record->status === 'Division Head Approved') {
            $approverIds = $this->approverIdsFor($type, [$record->id])[$record->id] ?? [];
            $approved = count($approverIds);
        } else {
            return ['approved' => 0, 'total' => $total, 'approvers' => [], 'pending' => []];
        }

        // `name` is an accessor (fname/mname/lname/suffix), not a column, so it can't be plucked in SQL.
        $names = User::whereIn('id', $memberIds)
            ->get(['id', 'fname', 'mname', 'lname', 'suffix'])
            ->mapWithKeys(fn ($user) => [$user->id => $user->name]);

        return [
            'approved'  => $approved,
            'total'     => $total,
            'approvers' => collect($approverIds)->map(fn ($id) => $names[$id] ?? "User #{$id}")->values()->all(),
            'pending'   => collect($memberIds)->diff($approverIds)->map(fn ($id) => $names[$id] ?? "User #{$id}")->values()->all(),
        ];
    }

    // "PMT x/N" labels keyed by record id for list pages; only records in or past the PMT phase get one.
    public function badgeMap(string $type, $records): array
    {
        $records = collect($records)->filter()->filter(
            fn ($record) => in_array($record->status, ['Division Head Approved', 'PMT Approved'], true)
        );
        $approvers = $this->approverIdsFor($type, $records->pluck('id')->all());
        $total = count($this->memberIds());

        return $records->mapWithKeys(function ($record) use ($approvers, $total) {
            $approved = count($approvers[$record->id] ?? []);
            // Records approved before multi-member PMT approval have no rows; show them as complete.
            if ($record->status === 'PMT Approved' && $approved === 0) {
                $approved = $total;
            }
            return [$record->id => "PMT {$approved}/{$total}"];
        })->all();
    }

    // Records one member's approval and returns [approvedCount, total].
    public function approve(string $type, int $recordId, int $userId): array
    {
        if (!in_array($userId, $this->memberIds(), true)) {
            throw new \Exception('Only PMT members can approve this stage.');
        }

        $exists = PmtApproval::where('record_type', $type)
            ->where('record_id', $recordId)
            ->where('user_id', $userId)
            ->exists();
        if ($exists) {
            throw new \Exception('You have already approved this record.');
        }

        PmtApproval::create(['record_type' => $type, 'record_id' => $recordId, 'user_id' => $userId]);

        $approved = count($this->approverIdsFor($type, [$recordId])[$recordId] ?? []);

        return [$approved, count($this->memberIds())];
    }

    // A rejection restarts the PMT phase, so the next submission needs every member again.
    public function reset(string $type, int $recordId): void
    {
        PmtApproval::where('record_type', $type)->where('record_id', $recordId)->delete();
    }
}
