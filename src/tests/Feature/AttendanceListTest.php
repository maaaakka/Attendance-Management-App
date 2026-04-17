<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;


class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 自分の勤怠情報が全て表示されている()
    {
        $user = User::factory()->create();

        $today = now();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => $today,
            'work_start_datetime' => $today,
            'work_end_datetime' => $today,
            'status' => Attendance::STATUS_LEFT
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/list?month='.$today->format('Y-m'));

        $response->assertStatus(200);

        $response->assertSee($today->format('m/d'));
    }

    /** @test */
    public function 勤怠一覧画面に現在の月が表示される()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertSee(now()->format('Y/m'));
    }

    /** @test */
    public function 前月ボタンで前月の情報が表示される()
    {
        $user = User::factory()->create();

        $previousMonth = now()->subMonth();

        $response = $this->actingAs($user)
            ->get('/attendance/list?month='.$previousMonth->format('Y-m'));

        $response->assertSee($previousMonth->format('Y/m'));
    }

    /** @test */
    public function 翌月ボタンで翌月の情報が表示される()
    {
        $user = User::factory()->create();

        $nextMonth = now()->addMonth();

        $response = $this->actingAs($user)
            ->get('/attendance/list?month='.$nextMonth->format('Y-m'));

        $response->assertSee($nextMonth->format('Y/m'));
    }

    /** @test */
    public function 詳細ボタンで勤怠詳細画面に遷移する()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now(),
            'work_start_datetime' => now(),
            'work_end_datetime' => now(),
            'status' => Attendance::STATUS_LEFT
        ]);

        $response = $this->actingAs($user)
            ->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
    }
}