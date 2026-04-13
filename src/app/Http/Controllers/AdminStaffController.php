<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class AdminStaffController extends Controller
{
    public function index()
    {
        $users = User::paginate(8);

        return view('admin.staff.list', compact('users'));
    }

    public function attendanceList(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $month = $request->input('month', Carbon::now()->format('Y-m'));

        $start = Carbon::parse($month)->startOfMonth();
        $end   = Carbon::parse($month)->endOfMonth();

        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('work_date', [$start, $end])
            ->with('breakTimes')
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->work_date)->format('Y-m-d');
            });

        $dates = CarbonPeriod::create($start, $end);

        return view('admin.attendance.staff', compact(
            'user',
            'dates',
            'attendances',
            'month'
        ));
    }

    public function exportCsv(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $month = $request->input('month', Carbon::now()->format('Y-m'));

        $start = Carbon::parse($month)->startOfMonth();
        $end   = Carbon::parse($month)->endOfMonth();

        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('work_date', [$start, $end])
            ->with('breakTimes')
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->work_date)->format('Y-m-d');
            });

        $dates = CarbonPeriod::create($start, $end);

        $csvData = [];

        $csvData[] = ['日付', '出勤', '退勤', '休憩', '合計'];

        foreach ($dates as $date) {

            $attendance = $attendances[$date->format('Y-m-d')] ?? null;

            $startTime = $attendance?->work_start_datetime
                ? Carbon::parse($attendance->work_start_datetime)->format('H:i')
                : '';

            $endTime = $attendance?->work_end_datetime
                ? Carbon::parse($attendance->work_end_datetime)->format('H:i')
                : '';

            $breakTotal = 0;
            if ($attendance) {
                foreach ($attendance->breakTimes as $break) {
                    if ($break->break_end) {
                        $breakTotal += strtotime($break->break_end) - strtotime($break->break_start);
                    }
                }
            }

            $breakTime = $breakTotal ? gmdate('H:i', floor($breakTotal / 60) * 60) : '';

            $workTotal = 0;
            if ($attendance && $attendance->work_end_datetime) {
                $workTotal = strtotime($attendance->work_end_datetime)
                    - strtotime($attendance->work_start_datetime)
                    - $breakTotal;
            }

            $workTime = $workTotal ? gmdate('H:i', floor($workTotal / 60) * 60) : '';

            $csvData[] = [
                $date->format('Y-m-d'),
                $startTime,
                $endTime,
                $breakTime,
                $workTime,
            ];
        }

        $filename = 'attendance_' . $user->name . '_' . $month . '.csv';

        $handle = fopen('php://temp', 'r+');

        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);

        return response(stream_get_contents($handle), 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}