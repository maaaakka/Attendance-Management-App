<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class BreakTimeSeeder extends Seeder
{
    public function run()
    {
        $attendances = Attendance::all();

        foreach ($attendances as $attendance) {

            $date = Carbon::parse($attendance->work_date);

            // 休憩① 12:00〜12:30
            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_start' => $date->copy()->setTime(12, 0),
                'break_end' => $date->copy()->setTime(12, 30),
            ]);

            // 休憩② 15:00〜15:30
            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_start' => $date->copy()->setTime(15, 0),
                'break_end' => $date->copy()->setTime(15, 30),
            ]);
        }
    }
}