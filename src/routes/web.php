<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;


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

Route::get('/', function () {
    return redirect('/login');
});

Route::middleware(['auth','verified'])->group(function () {
    // 出勤前
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');

    // 出勤
    Route::post('/attendance/start', [AttendanceController::class, 'startWork'])
        ->name('attendance.start');

    // 休憩入
    Route::post('/attendance/break/start', [AttendanceController::class, 'startBreak'])
        ->name('attendance.break.start');

    // 休憩戻
    Route::post('/attendance/break/end', [AttendanceController::class, 'endBreak'])
        ->name('attendance.break.end');

    // 退勤
    Route::post('/attendance/end', [AttendanceController::class, 'endWork'])
    ->name('attendance.end');

    // 勤怠一覧
    Route::get('/attendance/list', [AttendanceController::class, 'list'])
        ->name('attendance.list');

    // 勤怠詳細(既存データ)
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'detail'])
        ->name('attendance.detail');

    // 勤怠詳細(追加データ)
    Route::get('/attendance/detail/date/{date}', [AttendanceController::class, 'detailByDate'])
        ->name('attendance.detail.date');

    // 修正申請
    Route::post('/attendance/request/{id}',[AttendanceController::class,'requestCorrection']
        )->name('attendance.request');

    // 申請一覧
    Route::get('/stamp_correction_request/list',[StampCorrectionRequestController::class,'list']
        )->name('stamp_correction_request.list');

});