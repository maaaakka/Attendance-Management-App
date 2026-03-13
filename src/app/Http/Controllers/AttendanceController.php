<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\BreakTime;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 今日の勤怠データ取得
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', today())
            ->first();

        // ステータス判定
        if ($attendance) {
            $status = $attendance->status;
        } else {
            $status = 0; // 勤務外
        }

        return view('attendance.index', compact('user', 'status'));

    }

    // 出勤
    public function startWork()
    {
        $user = Auth::user();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'work_start_datetime' => Carbon::now(),
            'status' => 1
        ]);

        return redirect()->route('attendance.index');
    }

    // 休憩入
    public function startBreak()
    {
        $user = Auth::user();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', today())
            ->first();

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::now()
        ]);

        $attendance->update([
            'status' => 2
        ]);

        return redirect()->route('attendance.index');
    }

    // 休憩戻
    public function endBreak()
    {
        $user = Auth::user();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', today())
            ->first();

        $break = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->latest()
            ->first();

        $break->update([
            'break_end' => Carbon::now()
        ]);

        $attendance->update([
            'status' => 1
        ]);

        return redirect()->route('attendance.index');
    }

    // 退勤
    public function endWork()
    {
        $user = Auth::user();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', today())
            ->first();

        $attendance->update([
            'work_end_datetime' => Carbon::now(),
            'status' => 3
        ]);

        return redirect()->route('attendance.index');
    }

    // 勤怠一覧
    public function list(Request $request)
{
    $user = Auth::user();

    $month = $request->input('month', Carbon::now()->format('Y-m'));

    $start = Carbon::parse($month)->startOfMonth();
    $end = Carbon::parse($month)->endOfMonth();

    // 勤怠データ取得
    $attendances = Attendance::where('user_id', $user->id)
        ->whereBetween('work_date', [$start, $end])
        ->get()
        ->keyBy(function ($item) {
            return Carbon::parse($item->work_date)->format('Y-m-d');
        });

    // 1ヶ月の日付
    $dates = CarbonPeriod::create($start, $end);

    return view('attendance.list', compact(
        'dates',
        'attendances',
        'month'
    ));
}
}
