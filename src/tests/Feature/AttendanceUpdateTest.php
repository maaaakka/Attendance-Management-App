<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\CorrectionRequestAttendance;
use App\Models\CorrectionRequestBreak;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceUpdateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
public function 出勤時間が退勤時間より後になっている場合エラー()
{
    $user = \App\Models\User::factory()->create();

    $attendance = \App\Models\Attendance::create([
        'user_id'=>$user->id,
        'work_date'=>now(),
        'work_start_datetime'=>now()->setTime(9,0),
        'work_end_datetime'=>now()->setTime(18,0),
        'status'=>3
    ]);

    $response = $this->actingAs($user)->post("/attendance/request/{$attendance->id}",[
        'work_start'=>'19:00',
        'work_end'=>'18:00',
        'note'=>'修正'
    ]);

    $response->assertSessionHasErrors();
}

/** @test */
public function 休憩開始が退勤より後だとエラー()
{
    $user = \App\Models\User::factory()->create();

    $attendance = \App\Models\Attendance::create([
        'user_id'=>$user->id,
        'work_date'=>now(),
        'work_start_datetime'=>now()->setTime(9,0),
        'work_end_datetime'=>now()->setTime(18,0),
        'status'=>3
    ]);

    $response = $this->actingAs($user)->post("/attendance/request/{$attendance->id}",[
        'work_start'=>'09:00',
        'work_end'=>'18:00',
        'break_start'=>'19:00',
        'break_end'=>'19:30',
        'note'=>'修正'
    ]);

    $response->assertSessionHasErrors();
}
/** @test */
public function 休憩終了が退勤より後だとエラー()
{
    $user = \App\Models\User::factory()->create();

    $attendance = \App\Models\Attendance::create([
        'user_id'=>$user->id,
        'work_date'=>now(),
        'work_start_datetime'=>now()->setTime(9,0),
        'work_end_datetime'=>now()->setTime(18,0),
        'status'=>3
    ]);

    $response = $this->actingAs($user)->post("/attendance/request/{$attendance->id}",[
        'work_start'=>'09:00',
        'work_end'=>'18:00',
        'break_start'=>'12:00',
        'break_end'=>'19:00',
        'note'=>'修正'
    ]);

    $response->assertSessionHasErrors();
}

/** @test */
public function 備考未入力でエラー()
{
    $user = \App\Models\User::factory()->create();

    $attendance = \App\Models\Attendance::create([
        'user_id'=>$user->id,
        'work_date'=>now(),
        'work_start_datetime'=>now()->setTime(9,0),
        'work_end_datetime'=>now()->setTime(18,0),
        'status'=>3
    ]);

    $response = $this->actingAs($user)->post("/attendance/request/{$attendance->id}",[
        'work_start'=>'09:00',
        'work_end'=>'18:00',
        'note'=>''
    ]);

    $response->assertSessionHasErrors('note');
}

/** @test */
public function 修正申請が作成される()
{
    $user = \App\Models\User::factory()->create();

    $attendance = \App\Models\Attendance::create([
        'user_id'=>$user->id,
        'work_date'=>now(),
        'work_start_datetime'=>now()->setTime(9,0),
        'work_end_datetime'=>now()->setTime(18,0),
        'status'=>3
    ]);

    $this->actingAs($user)->post("/attendance/request/{$attendance->id}",[
        'work_start_datetime'=>'09:30',
        'work_end_datetime'=>'18:00',
        'note'=>'修正'
    ]);

    $this->assertDatabaseHas('correction_request_attendances',[
        'attendance_id'=>$attendance->id
    ]);
}

/** @test */
public function 申請一覧が表示される()
{
    $user = \App\Models\User::factory()->create();

    $response = $this->actingAs($user)
        ->get('/stamp_correction_request/list');

    $response->assertStatus(200);
}

/** @test */
public function 承認済み一覧が表示される()
{
    $user = \App\Models\User::factory()->create();

    $response = $this->actingAs($user)
        ->get('/stamp_correction_request/list?status=approved');

    $response->assertStatus(200);
}

/** @test */
public function 申請詳細に遷移できる()
{
    $user = \App\Models\User::factory()->create();

    $attendance = \App\Models\Attendance::create([
        'user_id'=>$user->id,
        'work_date'=>now(),
        'work_start_datetime'=>now(),
        'work_end_datetime'=>now(),
        'status'=>3
    ]);

    $response = $this->actingAs($user)
        ->get("/attendance/detail/{$attendance->id}");

    $response->assertStatus(200);
}

}
