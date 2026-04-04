<?php

namespace App\Http\Controllers;

use App\Models\CorrectionRequestAttendance;
use Illuminate\Support\Facades\Auth;

class StampCorrectionRequestController extends Controller
{

    public function list()
    {
        // 未ログインなら弾く
        if (!auth('web')->check() && !auth('admin')->check()) {
            return redirect('/login');
        }

        // 管理者判定
        $isAdmin = auth('admin')->check();

        // ユーザー取得
        $user = $isAdmin
            ? auth('admin')->user()
            : auth('web')->user();

        // 承認待ち
        $pendingQuery = CorrectionRequestAttendance::with('attendance','user')
            ->where('correction_request_attendances.status', CorrectionRequestAttendance::STATUS_PENDING)
            ->join('attendances', 'correction_request_attendances.attendance_id', '=', 'attendances.id')
            ->orderBy('attendances.work_date', 'asc')
            ->select('correction_request_attendances.*');

        // 一般ユーザーのみ絞る
        if (!$isAdmin) {
            $pendingQuery->where('correction_request_attendances.user_id', $user->id);
        }

        $pendingRequests = $pendingQuery->paginate(5, ['*'], 'pending_page');

        // 承認済み
        $approvedQuery = CorrectionRequestAttendance::with('attendance','user')
            ->where('correction_request_attendances.status', CorrectionRequestAttendance::STATUS_APPROVED)
            ->join('attendances', 'correction_request_attendances.attendance_id', '=', 'attendances.id')
            ->orderBy('attendances.work_date', 'asc')
            ->select('correction_request_attendances.*');

        if (!$isAdmin) {
            $approvedQuery->where('correction_request_attendances.user_id', $user->id);
        }

        $approvedRequests = $approvedQuery->paginate(5, ['*'], 'approved_page');

        // ビュー分岐
        if ($isAdmin) {
            return view('admin.stamp_correction_request.list', compact(
                'pendingRequests',
                'approvedRequests'
            ));
        }

        return view('stamp_correction_request.list', compact(
            'pendingRequests',
            'approvedRequests'
        ));
    }

    public function approve($id)
    {
        $request = CorrectionRequestAttendance::with('attendance', 'user')
            ->findOrFail($id);

        return view('admin.stamp_correction_request.approve', compact('request'));
    }

    public function approveUpdate($id)
    {
        $request = CorrectionRequestAttendance::with('attendance', 'breaks')->findOrFail($id);

        // ① ステータス更新
        $request->status = CorrectionRequestAttendance::STATUS_APPROVED;
        $request->save();

        // ② 勤怠更新
        $attendance = $request->attendance;

        $attendance->work_start_datetime = $request->requested_work_start_datetime;
        $attendance->work_end_datetime = $request->requested_work_end_datetime;
        $attendance->note = $request->requested_note;

        $attendance->save();

        // ③ 休憩更新
        $existingBreaks = $attendance->breakTimes;

        foreach ($request->breaks as $index => $break) {

            if (isset($existingBreaks[$index])) {
                // 上書き
                $existingBreaks[$index]->update([
                    'break_start' => $break->break_start,
                    'break_end' => $break->break_end,
                ]);
            } else {
                // 新規追加
                $attendance->breakTimes()->create([
                    'break_start' => $break->break_start,
                    'break_end' => $break->break_end,
                ]);
            }
        }

        // 余分削除
        if (count($existingBreaks) > count($request->breaks)) {
            foreach ($existingBreaks->slice(count($request->breaks)) as $break) {
                $break->delete();
            }
        }

        $attendance->updateStatus();
        $attendance->save();

        return back();
    }
}