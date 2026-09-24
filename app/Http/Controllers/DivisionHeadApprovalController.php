<?php

namespace App\Http\Controllers;

use App\Models\Ipcr;
use App\Models\Spcr;
use Illuminate\Http\Request;

class DivisionHeadApprovalController extends Controller
{
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
}

