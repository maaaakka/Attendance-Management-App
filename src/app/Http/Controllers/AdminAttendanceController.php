<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use App\Models\CorrectionRequestAttendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\AttendanceRequest;

class AdminAttendanceController extends Controller
{
    public function list(Request $request)
    {
        
        // 日付取得（なければ今日）
        $date = $request->input('date')
            ? Carbon::parse($request->date)
            : Carbon::today();

        // 全ユーザー取得
        $users = User::with(['attendances' => function ($query) use ($date) {
            $query->whereDate('work_date', $date)
                ->with('breakTimes');
        }])->get();

        return view('admin.attendance.list', compact('users', 'date'));
    }

    public function detail($id)
    {
        $attendance = Attendance::with(['user', 'breakTimes'])->findOrFail($id);

        // 承認待ち申請
        $pendingRequest = CorrectionRequestAttendance::where('attendance_id', $id)
            ->where('status', CorrectionRequestAttendance::STATUS_PENDING)
            ->first();

        return view('admin.attendance.detail', compact('attendance', 'pendingRequest'));
    }

    public function update(Request $request, $id)
    {
        $attendance = Attendance::with('breakTimes')->findOrFail($id);

        // バリデーション
        $request->validate([
            'work_start_datetime' => ['required'],
            'work_end_datetime' => ['required'],
            'note' => ['required'],
        ], [
            'work_start_datetime.required' => '出勤時間を入力してください',
            'work_end_datetime.required' => '退勤時間を入力してください',
            'note.required' => '備考を記入してください',
        ]);

        // ❗ 時間チェック
        if ($request->work_start_datetime >= $request->work_end_datetime) {
            return back()->withErrors([
                'work_start_datetime' => '出勤時間もしくは退勤時間が不適切な値です'
            ]);
        }

        // 更新
        $attendance->update([
            'work_start_datetime' => $attendance->work_date . ' ' . $request->work_start_datetime,
            'work_end_datetime' => $attendance->work_date . ' ' . $request->work_end_datetime,
            'note' => $request->note,
        ]);

        // 休憩更新
        $existingBreaks = $attendance->breakTimes;

        foreach ($request->break_start as $index => $start) {

            $end = $request->break_end[$index] ?? null;

            // ❗休憩バリデーション
            if ($start && ($start < $request->work_start_datetime || $start > $request->work_end_datetime)) {
                return back()->withErrors([
                    'break_start' => '休憩時間が不適切な値です'
                ]);
            }

            if ($end && $end > $request->work_end_datetime) {
                return back()->withErrors([
                    'break_end' => '休憩時間もしくは退勤時間が不適切な値です'
                ]);
            }

            if (isset($existingBreaks[$index])) {
                $existingBreaks[$index]->update([
                    'break_start' => $attendance->work_date . ' ' . $start,
                    'break_end' => $end ? $attendance->work_date . ' ' . $end : null,
                ]);
            } else {
                $attendance->breakTimes()->create([
                    'break_start' => $attendance->work_date . ' ' . $start,
                    'break_end' => $end ? $attendance->work_date . ' ' . $end : null,
                ]);
            }
        }

        return back();
    }
}
