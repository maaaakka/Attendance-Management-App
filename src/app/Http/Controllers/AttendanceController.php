<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;

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
}
