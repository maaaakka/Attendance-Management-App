<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();

        foreach ($users as $user) {

            for ($i = 0; $i < 5; $i++) {

                $date = Carbon::now()->subDays($i);

                Attendance::create([
                    'user_id' => $user->id,
                    'work_date' => $date,
                    'work_start_datetime' => $date->copy()->setTime(9,0),
                    'work_end_datetime' => $date->copy()->setTime(18,0),
                    'status' => 1
                ]);
            }
        }
    }
}
