<?php

namespace App\Http\Controllers;

use App\Models\Ipcr;
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

        return view('division_head.index', compact('pendingIpcrs', 'year'));
    }
}

