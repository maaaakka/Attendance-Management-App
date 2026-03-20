<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();

        //  前日から10日間
        for ($day = 0; $day < 10; $day++) {

            $date = Carbon::yesterday()->subDays($day);

            foreach ($users as $user) {

                Attendance::create([
                    'user_id' => $user->id,
                    'work_date' => $date,

                    // ⭐ 全員固定
                    'work_start_datetime' => $date->copy()->setTime(9, 0),
                    'work_end_datetime' => $date->copy()->setTime(18, 0),

                    // ⭐ 退勤済
                    'status' => Attendance::STATUS_LEFT,

                ]);
            }
        }
    }
}