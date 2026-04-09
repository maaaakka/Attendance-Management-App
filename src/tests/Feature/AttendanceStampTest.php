<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceStampTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 現在日時が正しく表示される()
    {
        Carbon::setTestNow(Carbon::create(2026,3,15,9,0));

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee(now()->year);
    }

    /** @test */
    public function 勤務外ステータスが表示される()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('勤務外');
    }

    /** @test */
    public function 出勤中ステータスが表示される()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id'=>$user->id,
            'work_date'=>now(),
            'work_start_datetime'=>now(),
            'status'=>Attendance::STATUS_WORKING
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('出勤中');
    }

    /** @test */
    public function 休憩中ステータスが表示される()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now(),
            'work_start_datetime' => now(),
        ]);

        \App\Models\BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => now(),
            'break_end' => null,
        ]);

        $attendance->refresh();
        $attendance->save();

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('休憩中');
    }

    /** @test */
    public function 退勤済ステータスが表示される()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id'=>$user->id,
            'work_date'=>now(),
            'work_start_datetime'=>now(),
            'work_end_datetime'=>now(),
            'status'=>Attendance::STATUS_LEFT
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('退勤済');
    }

    /** @test */
    public function 出勤ボタンが正しく機能する()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/attendance/start');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => Attendance::STATUS_WORKING
        ]);

        $response->assertRedirect();
    }

    /** @test */
    public function 退勤済ユーザーは出勤ボタンが表示されない()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now(),
            'work_start_datetime' => now()->setTime(9,0),
            'work_end_datetime' => now()->setTime(18,0),
            'status' => Attendance::STATUS_LEFT
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertDontSee('出勤');
    }

    /** @test */
    public function 出勤時刻が勤怠一覧画面に表示される()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/attendance/start');

        $response = $this->actingAs($user)->get('/attendance/list');

        $attendance = Attendance::where('user_id',$user->id)->first();

        $response->assertSee(
            \Carbon\Carbon::parse($attendance->work_start_datetime)->format('H:i')
        );
    }

    /** @test */
    public function 休憩ボタンが正しく機能する()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now(),
            'work_start_datetime' => now(),
            'status' => Attendance::STATUS_WORKING
        ]);

        $this->actingAs($user)->post('/attendance/break/start');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => Attendance::STATUS_ON_BREAK
        ]);
    }

    /** @test */
    public function 休憩は一日に何回でもできる()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now(),
            'work_start_datetime' => now(),
            'status' => Attendance::STATUS_WORKING
        ]);

        $this->actingAs($user)->post('/attendance/break/start');
        $this->actingAs($user)->post('/attendance/break/end');

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('休憩入');
    }

    /** @test */
    public function 休憩戻ボタンが正しく機能する()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now(),
            'work_start_datetime' => now(),
            'status' => Attendance::STATUS_ON_BREAK
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => now(),
            'break_end' => null
        ]);

        $this->actingAs($user)->post('/attendance/break/end');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => Attendance::STATUS_WORKING
        ]);
    }

    /** @test */
    public function 休憩戻は一日に何回でもできる()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now(),
            'work_start_datetime' => now(),
            'status' => Attendance::STATUS_WORKING
        ]);

        $this->actingAs($user)->post('/attendance/break/start');
        $this->actingAs($user)->post('/attendance/break/end');
        $this->actingAs($user)->post('/attendance/break/start');

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('休憩戻');
    }

    /** @test */
    public function 休憩時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/attendance/start');
        $this->actingAs($user)->post('/attendance/break/start');
        $this->actingAs($user)->post('/attendance/break/end');

        $response = $this->actingAs($user)->get('/attendance/list');

        $break = BreakTime::first();

        $response->assertSee(
            \Carbon\Carbon::parse($break->break_start)->format('H:i')
        );
    }

    /** @test */
    public function 退勤ボタンが正しく機能する()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now(),
            'work_start_datetime' => now(),
            'status' => Attendance::STATUS_WORKING
        ]);

        $this->actingAs($user)->post('/attendance/end');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => Attendance::STATUS_LEFT
        ]);
    }

    /** @test */
    public function 退勤時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/attendance/start');
        $this->actingAs($user)->post('/attendance/end');

        $response = $this->actingAs($user)->get('/attendance/list');

        $attendance = Attendance::where('user_id',$user->id)->first();

        $response->assertSee(
            \Carbon\Carbon::parse($attendance->work_end_datetime)->format('H:i')
        );
    }
}