<?php

// use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\AdminAttendanceController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AdminStaffController;



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

Route::get('/stamp_correction_request/list',[StampCorrectionRequestController::class,'list']
        )->name('stamp_correction_request.list');

// 一般ユーザー
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
    // Route::get('/stamp_correction_request/list',[StampCorrectionRequestController::class,'list']
    //     )->name('stamp_correction_request.list');
            // ->middleware('auth');
});


// 管理者ログイン画面
Route::get('/admin/login', function () {
    return view('admin.login');
});

// 管理者ログイン処理
Route::post('/admin/login', [\Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::class, 'store']);

// ログアウト
Route::post('/admin/logout', function (Illuminate\Http\Request $request) {
    Auth::guard('admin')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/admin/login');
})->name('admin.logout');


// 管理者専用
Route::middleware('auth:admin')->group(function () {

    // 勤怠一覧
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'list']);

    // 詳細
    Route::get('/admin/attendance/{id}', [AdminAttendanceController::class, 'detail']);

    // 更新
    Route::post('/admin/attendance/{id}', [AdminAttendanceController::class, 'update'])
        ->name('admin.attendance.update');

    // 新規作成
    Route::post('/admin/attendance', [AdminAttendanceController::class, 'store'])
        ->name('admin.attendance.store');

    // 修正申請
    Route::get('/stamp_correction_request/approve/{id}', [StampCorrectionRequestController::class, 'approve'])
        ->name('stamp_correction_request.approve');

    Route::post('/stamp_correction_request/approve/{id}', [StampCorrectionRequestController::class, 'approveUpdate'])
        ->name('stamp_correction_request.approve.update');

    // スタッフ一覧
    Route::get('/admin/staff/list', [AdminStaffController::class, 'index']);

    // スタッフ別月次一覧
    Route::get('/admin/attendance/staff/{id}', [AdminStaffController::class, 'attendanceList']);

    // スタッフ別月次一覧CSV
    Route::get('/admin/attendance/staff/{id}/csv', [AdminStaffController::class, 'exportCsv'])
    ->name('admin.attendance.staff.csv');
});