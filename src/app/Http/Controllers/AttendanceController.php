<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\CorrectionRequestAttendance as CorrectionAttendance;
use App\Models\CorrectionRequestBreak;

use App\Http\Requests\CorrectionRequestAttendance;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', today())
            ->first();

        $status = $attendance?->status ?? Attendance::STATUS_OFF_WORK;

        return view('attendance.index', compact('user', 'status'));
    }


    // 出勤
    public function startWork()
    {
        $user = Auth::user();

        $attendance = Attendance::firstOrCreate(
        [
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
        ],
        [
            'work_start_datetime' => Carbon::now(),
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

        if (!$attendance) {
            return redirect()->route('attendance.index')
                ->with('error', '先に出勤してください');
        }

        $activeBreak = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->exists();

        if ($activeBreak) {
            return back()->with('error', 'すでに休憩中です');
        }

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::now()
        ]);

        $attendance->save();

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

        $attendance->save();

        return redirect()->route('attendance.index');
    }


    // 退勤
    public function endWork()
    {
        $user = Auth::user();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', today())
            ->first();

        $attendance->work_end_datetime = Carbon::now();
        $attendance->save();

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

    // 詳細
    public function detail($id)
    {
        // 数値かどうか判定
        if (is_numeric($id)) {
            $attendance = Attendance::with('breakTimes','user')->find($id);
        } else {
            // 日付として取得
            $attendance = Attendance::with('breakTimes','user')
                ->where('user_id', Auth::id())
                ->whereDate('work_date', $id)
                ->first();
        }

        // データなし
        if (!$attendance) {
            $attendance = new Attendance();
            $attendance->id = null;
            $attendance->user_id = Auth::id();
            $attendance->work_date = $id;
            $attendance->breakTimes = collect();
        }

        $pendingRequest = null;

        if ($attendance->id) {
            $pendingRequest = CorrectionAttendance::where('attendance_id',$attendance->id)
                ->where('status','pending')
                ->first();

            if ($pendingRequest) {
            $pendingRequest->load('breaks');}
        }

        return view('attendance.detail', compact(
            'attendance',
            'pendingRequest'
        ));
    }

    public function detailByDate($date)
    {
        $attendance = Attendance::with('breakTimes','user')
            ->where('user_id', Auth::id())
            ->whereDate('work_date', $date)
            ->first();

        if (!$attendance) {
            $attendance = new Attendance();
            $attendance->id = null;
            $attendance->user_id = Auth::id();
            $attendance->work_date = $date;
            $attendance->breakTimes = collect();
        }

        $pendingRequest = null;

        return view('attendance.detail', compact(
            'attendance',
            'pendingRequest'
        ));
    }

    // 修正申請
    public function requestCorrection(CorrectionRequestAttendance $request, $id)
    {

        // 勤怠データ取得（無ければ作成）
        if (is_numeric($id)) {
            $attendance = Attendance::findOrFail($id);
        } else {
            $attendance = Attendance::where('user_id', auth()->id())
                ->whereDate('work_date', $id)
                ->first();

            // 無ければ作成
            if (!$attendance) {
                $attendance = Attendance::create([
                    'user_id' => auth()->id(),
                    'work_date' => $request->work_date,
                ]);
            }
        }


        // 休憩データ読み込み
        $attendance->load('breakTimes');

        $workDate = $attendance->work_date;


        // 勤怠修正申請
        $correction = CorrectionAttendance::create([

            'attendance_id' => $attendance->id,
            'user_id' => auth()->id(),

            'requested_work_start_datetime' => $workDate . ' ' . $request->work_start_datetime,
            'requested_work_end_datetime' => $workDate . ' ' . $request->work_end_datetime,

            'requested_note' => $request->note,

            'status' => CorrectionAttendance::STATUS_PENDING

        ]);

        // 休憩修正申請
        if ($request->break_start) {

            foreach ($request->break_start as $index => $start) {

                if (!$start) {
                    continue;
                }

                $breakId = $attendance->breakTimes[$index]->id ?? null;

                $breakEnd = $request->break_end[$index] ?? null;

                $data = [
                    'request_id' => $correction->id,
                    'break_start' => $workDate . ' ' . $start,
                    'break_end' => $breakEnd ? $workDate . ' ' . $breakEnd : null
                ];

                // 既存休憩がある場合のみ
                if ($breakId) {
                    $data['break_id'] = $breakId;
                }

                CorrectionRequestBreak::create($data);

            }

        }

        return redirect()
            ->route('stamp_correction_request.list')
            ->with('success', '修正申請を送信しました');
    }
}
