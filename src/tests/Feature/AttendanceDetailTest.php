<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 勤怠詳細画面の名前がログインユーザーになっている()
    {
        $user = User::factory()->create([
            'name' => 'テストユーザー'
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now(),
            'work_start_datetime' => now(),
            'work_end_datetime' => now(),
            'status' => 3
        ]);

        $response = $this->actingAs($user)
            ->get("/attendance/detail/{$attendance->id}");

        $response->assertSee('テストユーザー');
    }

    /** @test */
    public function 勤怠詳細の日付が正しい()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now(),
            'work_start_datetime' => now(),
            'work_end_datetime' => now(),
            'status' => 3
        ]);

        $response = $this->actingAs($user)
            ->get("/attendance/detail/{$attendance->id}");

        $response->assertSee(
            \Carbon\Carbon::parse($attendance->work_date)->format('Y年')
        );

        $response->assertSee(
            \Carbon\Carbon::parse($attendance->work_date)->format('n月j日')
        );
    }

    /** @test */
    public function 出勤退勤時間が正しく表示される()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now(),
            'work_start_datetime' => now()->setTime(9,0),
            'work_end_datetime' => now()->setTime(18,0),
            'status' => 3
        ]);

        $response = $this->actingAs($user)
            ->get("/attendance/detail/{$attendance->id}");

        $response->assertSee(
            $attendance->work_start_datetime->format('H:i')
        );

        $response->assertSee(
            $attendance->work_end_datetime->format('H:i')
        );
    }

    /** @test */
    public function 休憩時間が正しく表示される()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now(),
            'work_start_datetime' => now(),
            'work_end_datetime' => now(),
            'status' => 3
        ]);

        $break = BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => now()->setTime(12,0),
            'break_end' => now()->setTime(13,0)
        ]);

        $response = $this->actingAs($user)
            ->get("/attendance/detail/{$attendance->id}");

        $response->assertSee(
            $break->break_start->format('H:i')
        );

        $response->assertSee(
            $break->break_end->format('H:i')
        );
    }
}