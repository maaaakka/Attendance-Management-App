<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function その日の全ユーザーの勤怠情報が表示される()
    {
        // 管理者
        $admin = Admin::factory()->create();

        // 一般ユーザー
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $today = Carbon::today();

        // 勤怠
        Attendance::create([
            'user_id' => $user1->id,
            'work_date' => $today,
            'work_start_datetime' => $today->copy()->setTime(9, 0),
            'work_end_datetime' => $today->copy()->setTime(18, 0),
        ]);

        Attendance::create([
            'user_id' => $user2->id,
            'work_date' => $today,
            'work_start_datetime' => $today->copy()->setTime(10, 0),
            'work_end_datetime' => $today->copy()->setTime(19, 0),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/list');

        $response->assertStatus(200);

        // 名前表示
        $response->assertSee($user1->name);
        $response->assertSee($user2->name);

        // 時刻確認（フォーマットに注意）
        $response->assertSee('09:00');
        $response->assertSee('10:00');
    }

    /** @test */
    public function 現在の日付が表示される()
    {
        $admin = Admin::factory()->create();

        $today = Carbon::today();

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/list');

        $response->assertSee($today->format('Y/m/d'));
    }

    /** @test */
    public function 前日ボタンで前日の勤怠が表示される()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        $yesterday = now()->subDay();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $yesterday,
            'work_start_datetime' => $yesterday->copy()->setTime(9, 0),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/list?date=' . $yesterday->toDateString());

        // ✅ ここが正しい位置
        $response->assertSee($yesterday->format('Y/m/d'));
        $response->assertSee($user->name);
    }

    /** @test */
    public function 翌日ボタンで翌日の勤怠が表示される()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        $tomorrow = now()->addDay();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $tomorrow,
            'work_start_datetime' => $tomorrow->copy()->setTime(9, 0),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/list?date=' . $tomorrow->toDateString());

        // ✅ フォーマット統一
        $response->assertSee($tomorrow->format('Y/m/d'));
        $response->assertSee($user->name);
    }
}