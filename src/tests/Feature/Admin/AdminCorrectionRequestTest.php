<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use App\Models\CorrectionRequestAttendance;
use Carbon\Carbon;

class AdminCorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 承認待ちの修正申請が全て表示される()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now(),
        ]);

        $request = CorrectionRequestAttendance::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_work_start_datetime' => now()->setTime(9,0),
            'requested_work_end_datetime' => now()->setTime(18,0),
            'requested_note' => '修正',
            'status' => CorrectionRequestAttendance::STATUS_PENDING
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/stamp_correction_request/list?status=pending');

        $response->assertSee($user->name);
        $response->assertSee('修正');
    }

    /** @test */
    public function 承認済みの修正申請が全て表示される()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now(),
        ]);

        $request = CorrectionRequestAttendance::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_work_start_datetime' => now()->setTime(9,0),
            'requested_work_end_datetime' => now()->setTime(18,0),
            'requested_note' => '承認済み',
            'status' => CorrectionRequestAttendance::STATUS_APPROVED
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/stamp_correction_request/list?status=approved');

        $response->assertSee('承認済み');
    }

    /** @test */
    public function 修正申請の詳細が正しく表示される()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now(),
        ]);

        $request = CorrectionRequestAttendance::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_work_start_datetime' => now()->setTime(9,0),
            'requested_work_end_datetime' => now()->setTime(18,0),
            'requested_note' => '詳細テスト',
            'status' => CorrectionRequestAttendance::STATUS_PENDING
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/stamp_correction_request/approve/' . $request->id);

        $response->assertSee('詳細テスト');
    }

    /** @test */
    public function 修正申請が承認され勤怠が更新される()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now(),
            'work_start_datetime' => now()->setTime(9,0),
            'work_end_datetime' => now()->setTime(18,0),
        ]);

        $request = CorrectionRequestAttendance::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_work_start_datetime' => now()->setTime(10,0),
            'requested_work_end_datetime' => now()->setTime(19,0),
            'requested_note' => '変更',
            'status' => CorrectionRequestAttendance::STATUS_PENDING
        ]);

        $this->actingAs($admin, 'admin')
            ->post('/stamp_correction_request/approve/' . $request->id);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'work_start_datetime' => now()->setTime(10,0)->format('Y-m-d H:i:s'),
            'work_end_datetime' => now()->setTime(19,0)->format('Y-m-d H:i:s'),
        ]);

        $this->assertDatabaseHas('correction_request_attendances', [
            'id' => $request->id,
            'status' => CorrectionRequestAttendance::STATUS_APPROVED
        ]);
    }
}