<?php

use App\Http\Controllers\IpcrController;
use App\Http\Controllers\DivisionHeadApprovalController;
use App\Http\Controllers\PmtApprovalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


use App\Http\Controllers\Auth\LoginController;

Route::get('/', function () {
    return redirect('/ipcr');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/ipcr', function () {
        return view('ipcr.index');
    });

    Route::get('/ipcr/pdf/{id}', [IpcrController::class, 'generatePDF'])->name('ipcr.pdf');
    Route::get('/ipcr/print/{id}', [IpcrController::class, 'printIpcr'])->name('ipcr.print');

    Route::get('/dashboard-new', [IpcrController::class, 'dashboard'])->name('dashboard.new');

    Route::get('/ipcr/staff', [IpcrController::class, 'staff'])->name('ipcr.staff');
    Route::post('/password/update', [LoginController::class, 'updatePassword'])->name('password.update');

    // API-style routes for IPCR (now with Web Session access)
    Route::prefix('api/ipcr')->group(function () {
        Route::get('/', [IpcrController::class, 'index']);
        Route::post('/', [IpcrController::class, 'store']);
        Route::get('/pending', [IpcrController::class, 'getPending']);
        Route::get('/by-semester', [IpcrController::class, 'getByYearSemester']);
        Route::post('/{id}/approve', [IpcrController::class, 'approve']);
        Route::post('/{id}/submit', [IpcrController::class, 'submit']);
        Route::get('/{id}', [IpcrController::class, 'show']);
        Route::get('/{id}/logs', [IpcrController::class, 'getLogs']);
        Route::get('/supervisors', [IpcrController::class, 'getSupervisors']);
        Route::put('/{id}', [IpcrController::class, 'update']);
        Route::delete('/{id}', [IpcrController::class, 'destroy']);
    });

    // SPCR Routes
    Route::get('/spcr', [\App\Http\Controllers\SpcrController::class, 'index'])->name('spcr.index');
    Route::get('/spcr/staff', [\App\Http\Controllers\SpcrController::class, 'staff'])->name('spcr.staff');
    Route::get('/spcr/{id}/print', [\App\Http\Controllers\SpcrController::class, 'print'])->name('spcr.print');
    Route::get('/division-head/approvals', [DivisionHeadApprovalController::class, 'index'])->name('division_head.approvals');
    Route::get('/pmt/approvals', [PmtApprovalController::class, 'index'])->name('pmt.approvals');
    Route::prefix('api/spcr')->group(function () {
        Route::get('/by-semester', [\App\Http\Controllers\SpcrController::class, 'getByYearSemester']);
        Route::post('/', [\App\Http\Controllers\SpcrController::class, 'store']);
        Route::get('/{id}', [\App\Http\Controllers\SpcrController::class, 'show']);
        Route::get('/{id}/logs', [\App\Http\Controllers\SpcrController::class, 'getLogs']);
        Route::put('/{id}', [\App\Http\Controllers\SpcrController::class, 'update']);
        Route::post('/{id}/submit', [\App\Http\Controllers\SpcrController::class, 'submit']);
        Route::post('/{id}/approve', [\App\Http\Controllers\SpcrController::class, 'approve']);
        Route::delete('/{id}', [\App\Http\Controllers\SpcrController::class, 'destroy']);
        Route::post('/entry/{entryId}/rate', [\App\Http\Controllers\SpcrController::class, 'rate']);
        Route::post('/entry/{entryId}/accomplishment', [\App\Http\Controllers\SpcrController::class, 'addAccomplishment']);
    });
});
