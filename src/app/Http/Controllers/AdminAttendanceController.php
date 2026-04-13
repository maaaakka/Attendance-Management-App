<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use App\Models\CorrectionRequestAttendance as CorrectionAttendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\CorrectionRequestAttendance;

class AdminAttendanceController extends Controller
{
    public function list(Request $request)
    {
        $date = $request->input('date')
            ? Carbon::parse($request->date)
            : Carbon::today();

        $users = User::paginate(8)->appends($request->query());

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

        if ($date) {

            $attendance = Attendance::with(['user', 'breakTimes'])
                ->where('user_id', $id)
                ->whereDate('work_date', $date)
                ->first();

            if (!$attendance) {
                $attendance = new Attendance();
                $attendance->id = null;
                $attendance->user_id = $id;
                $attendance->work_date = $date;
                $attendance->breakTimes = collect();

                $attendance->setRelation('user', User::find($id));

                $pendingRequest = null;
            } else {

                $pendingRequest = CorrectionAttendance::where('attendance_id', $attendance->id)
                    ->where('status', CorrectionAttendance::STATUS_PENDING)
                    ->first();
            }

            return view('admin.attendance.detail', compact(
                'attendance',
                'pendingRequest'
            ));
        }

        $attendance = Attendance::with(['user', 'breakTimes'])->findOrFail($id);

        $pendingRequest = CorrectionAttendance::where('attendance_id', $attendance->id)
            ->where('status', CorrectionAttendance::STATUS_PENDING)
            ->first();

        return view('admin.attendance.detail', compact(
            'attendance',
            'pendingRequest'
        ));
    }

    public function update(CorrectionRequestAttendance $request, $id)
    {
        $validated = $request->validated();

        $date = $request->query('date');

        if ($date) {
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
            $attendance = Attendance::findOrFail($id);
            $date = $attendance->work_date;
        }

        $attendance->update([
            'work_start_datetime' => $date . ' ' . $request->work_start_datetime,
            'work_end_datetime' => $date . ' ' . $request->work_end_datetime,
            'note' => $request->note,
        ]);

        $existingBreaks = $attendance->breakTimes;

        foreach ($request->break_start as $index => $start) {

            $end = $request->break_end[$index] ?? null;

            if (empty($start) && empty($end)) {
                continue;
            }

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
        $attendance->updateStatus();
        $attendance->save();

        return back()->with('success', '勤怠更新に成功しました');
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

        $attendance->updateStatus();
        $attendance->save();

        return back()->with('success', '勤怠更新に成功しました');
    }
}
