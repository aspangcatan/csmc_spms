<?php

namespace App\Http\Controllers;

use App\Models\Ipcr;
use App\Models\Spcr;
use Illuminate\Http\Request;

class PmtApprovalController extends Controller
{
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
            ->where('status', 'Supervisor Approved')
            ->where('year', $year)
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('pmt.index', compact('pendingIpcrs', 'pendingSpcrs', 'year'));
    }
}
