<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use App\Models\CorrectionRequestAttendance as CorrectionAttendance;;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\CorrectionRequestAttendance;

class AdminAttendanceController extends Controller
{
    // 一覧
    public function list(Request $request)
    {
        // 日付取得（なければ今日）
        $date = $request->input('date')
            ? Carbon::parse($request->date)
            : Carbon::today();

        // 全ユーザー
        $users = User::all();

        // ★ その日の勤怠を user_id でまとめる
        $attendances = Attendance::whereDate('work_date', $date)
            ->with('breakTimes')
            ->get()
            ->keyBy('user_id');

        return view('admin.attendance.list', compact(
            'users',
            'date',
            'attendances'
        ));
    }

public function detail(Request $request, $id)
{
    $date = $request->date;

    // =========================
    // ★ dateあり → user_id扱い（絶対こっち）
    // =========================
    if ($date) {

        $attendance = Attendance::with(['user', 'breakTimes'])
            ->where('user_id', $id)
            ->whereDate('work_date', $date)
            ->first();

        // なければ空データ
        if (!$attendance) {
            $attendance = new Attendance();
            $attendance->id = null;
            $attendance->user_id = $id;
            $attendance->work_date = $date;
            $attendance->breakTimes = collect();

            $attendance->setRelation('user', User::find($id));
        }

        return view('admin.attendance.detail', [
            'attendance' => $attendance,
            'pendingRequest' => null
        ]);
    }

    // =========================
    // ★ dateなし → attendance_id扱い
    // =========================
    $attendance = Attendance::with(['user', 'breakTimes'])->findOrFail($id);

    return view('admin.attendance.detail', [
        'attendance' => $attendance,
        'pendingRequest' => null
    ]);
}

    public function update(CorrectionRequestAttendance $request, $id)
{
    $date = $request->query('date');

    // =========================
    // ① 勤怠取得（or 新規作成）
    // =========================
    if ($date) {
        // user_idとして扱う
        $attendance = Attendance::where('user_id', $id)
            ->whereDate('work_date', $date)
            ->first();

        if (!$attendance) {
            $attendance = Attendance::create([
                'user_id' => $id,
                'work_date' => $date,
            ]);
        }
    } else {
        // attendance_idとして扱う
        $attendance = Attendance::findOrFail($id);
        $date = $attendance->work_date;
    }

    // =========================
    // ② 勤怠更新
    // =========================
    $attendance->update([
        'work_start_datetime' => $date . ' ' . $request->work_start_datetime,
        'work_end_datetime' => $date . ' ' . $request->work_end_datetime,
        'note' => $request->note,
    ]);

    // =========================
    // ③ 休憩更新
    // =========================
    $existingBreaks = $attendance->breakTimes;

    foreach ($request->break_start as $index => $start) {

        $end = $request->break_end[$index] ?? null;

        // 🔥 空はスキップ（←重要）
        if (empty($start) && empty($end)) {
            continue;
        }

        // 🔥 片方だけ入力はエラー
        if (($start && !$end) || (!$start && $end)) {
            return back()->withErrors([
                "break_start.$index" => '休憩開始時間と終了時間を入力してください'
            ])->withInput();
        }

        if (isset($existingBreaks[$index])) {
            $existingBreaks[$index]->update([
                'break_start' => $date . ' ' . $start,
                'break_end' => $date . ' ' . $end,
            ]);
        } else {
            $attendance->breakTimes()->create([
                'break_start' => $date . ' ' . $start,
                'break_end' => $date . ' ' . $end,
            ]);
        }
    }

    return back()->with('success', '勤怠を更新しました');
}

public function store(CorrectionRequestAttendance $request)
{
    $attendance = Attendance::create([
        'user_id' => $request->user_id,
        'work_date' => $request->work_date,
        'work_start_datetime' => $request->work_date . ' ' . $request->work_start_datetime,
        'work_end_datetime' => $request->work_date . ' ' . $request->work_end_datetime,
        'note' => $request->note,
    ]);

    // 休憩
    if ($request->break_start) {
        foreach ($request->break_start as $index => $start) {

            $end = $request->break_end[$index] ?? null;

            if (empty($start) && empty($end)) continue;

            if (($start && !$end) || (!$start && $end)) {
                return back()->withErrors([
                    "break_start.$index" => '休憩開始時間と終了時間を入力してください'
                ])->withInput();
            }

            $attendance->breakTimes()->create([
                'break_start' => $request->work_date . ' ' . $start,
                'break_end' => $end ? $request->work_date . ' ' . $end : null,
            ]);
        }
    }

    return back();
}


}
