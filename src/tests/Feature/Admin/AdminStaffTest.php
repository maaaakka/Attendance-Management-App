<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminStaffTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 管理者が全ユーザーの氏名とメールを確認できる()
    {
        $admin = Admin::factory()->create();

        $users = User::factory()->count(3)->create();

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/staff/list');

        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }
    }

    /** @test */
    public function ユーザーの勤怠情報が正しく表示される()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        $date = Carbon::today();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $date,
            'work_start_datetime' => $date->copy()->setTime(9,0),
            'work_end_datetime' => $date->copy()->setTime(18,0),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/staff/' . $user->id);

        $response->assertSee($user->name);
        $response->assertSee($date->format('Y年m月'));
    }

    /** @test */
    public function 前月ボタンで前月の勤怠が表示される()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        $lastMonth = Carbon::now()->subMonth();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $lastMonth,
            'work_start_datetime' => $lastMonth->copy()->setTime(9,0),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/staff/' . $user->id . '?month=' . $lastMonth->format('Y-m'));

        $response->assertSee($lastMonth->format('Y-m'));
    }

    /** @test */
    public function 翌月ボタンで翌月の勤怠が表示される()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        $nextMonth = Carbon::now()->addMonth();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $nextMonth,
            'work_start_datetime' => $nextMonth->copy()->setTime(9,0),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/staff/' . $user->id . '?month=' . $nextMonth->format('Y-m'));

        $response->assertSee($nextMonth->format('Y-m'));
    }

    /** @test */
    public function 詳細ボタンで勤怠詳細画面に遷移できる()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now(),
            'work_start_datetime' => now()->setTime(9,0),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/' . $attendance->id);

        $response->assertStatus(200);
    }
}