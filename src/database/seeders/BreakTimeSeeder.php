<?php

namespace Database\Seeders;

use App\Models\BreakTime;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BreakTimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $attendances = Attendance::all();

        foreach ($attendances as $attendance) {

            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_start' => Carbon::parse($attendance->work_date)->setTime(12,0),
                'break_end' => Carbon::parse($attendance->work_date)->setTime(13,0),
            ]);
        }

    }
}
