<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 詳細画面に正しいデータが表示される()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        $date = Carbon::today();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => $date,
            'work_start_datetime' => $date->copy()->setTime(9, 0),
            'work_end_datetime' => $date->copy()->setTime(18, 0),
            'note' => 'テスト備考',
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => $date->copy()->setTime(12, 0),
            'break_end' => $date->copy()->setTime(13, 0),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get("/admin/attendance/{$attendance->id}");

        $response->assertSee($user->name);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
        $response->assertSee('テスト備考');
    }

    /** @test */
    public function 出勤が退勤より後だとエラー()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.attendance.update', $attendance->id), [
                'user_id' => $user->id,
                'work_date' => today(),
                'work_start_datetime' => '20:00',
                'work_end_datetime' => '10:00',
                'note' => 'テスト',
            ]);

        $response->assertSessionHasErrors([
            'work_start_datetime'
        ]);
    }

    /** @test */
    public function 休憩開始が退勤より後だとエラー()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.attendance.update', $attendance->id), [
                'user_id' => $user->id,
                'work_date' => today(),
                'work_start_datetime' => '09:00',
                'work_end_datetime' => '18:00',
                'break_start' => ['19:00'],
                'break_end' => ['19:30'],
                'note' => 'テスト',
            ]);

        $response->assertSessionHasErrors([
            'break_start.0'
        ]);
    }

    /** @test */
    public function 休憩終了が退勤より後だとエラー()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.attendance.update', $attendance->id), [
                'user_id' => $user->id,
                'work_date' => today(),
                'work_start_datetime' => '09:00',
                'work_end_datetime' => '18:00',
                'break_start' => ['17:00'],
                'break_end' => ['19:00'],
                'note' => 'テスト',
            ]);

        $response->assertSessionHasErrors([
            'break_end.0'
        ]);
    }

    /** @test */
    public function 備考未入力でエラー()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.attendance.update', $attendance->id), [
                'user_id' => $user->id,
                'work_date' => today(),
                'work_start_datetime' => '09:00',
                'work_end_datetime' => '18:00',
                'note' => '',
            ]);

        $response->assertSessionHasErrors([
            'note'
        ]);
    }
}