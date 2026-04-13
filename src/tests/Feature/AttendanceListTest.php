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

    public function 自分の勤怠情報が全て表示されている()
    {
        $user = User::factory()->create();
        /** @test */
        // 勤怠データ作成
        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now(),
            'work_start_datetime' => now(),
            'work_end_datetime' => now(),
            'status' => Attendance::STATUS_LEFT
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);

        // DBに存在するか確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id
        ]);
    }

    /** @test */
    public function 勤怠一覧画面に現在の月が表示される()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertSee(now()->format('m'));
    }

    /** @test */
    public function 前月ボタンで前月の情報が表示される()
    {
        $user = User::factory()->create();

        $previousMonth = now()->subMonth()->format('Y-m');

        $response = $this->actingAs($user)
            ->get('/attendance/list?month='.$previousMonth);

        $response->assertStatus(200);
    }

    /** @test */
    public function 翌月ボタンで翌月の情報が表示される()
    {
        $user = User::factory()->create();

        $nextMonth = now()->addMonth()->format('Y-m');

        $response = $this->actingAs($user)
            ->get('/attendance/list?month='.$nextMonth);

        $response->assertStatus(200);
    }
}