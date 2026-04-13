<?php

namespace App\Http\Controllers;

use App\Models\CorrectionRequestAttendance;
use Illuminate\Support\Facades\Auth;

class StampCorrectionRequestController extends Controller
{

    public function list()
    {
        if (!auth('web')->check() && !auth('admin')->check()) {
            return redirect('/login');
        }

        $isAdmin = auth('admin')->check();

        $user = $isAdmin
            ? auth('admin')->user()
            : auth('web')->user();

        $pendingQuery = CorrectionRequestAttendance::with('attendance','user')
            ->where('correction_request_attendances.status', CorrectionRequestAttendance::STATUS_PENDING)
            ->join('attendances', 'correction_request_attendances.attendance_id', '=', 'attendances.id')
            ->orderBy('attendances.work_date', 'asc')
            ->select('correction_request_attendances.*');

        if (!$isAdmin) {
            $pendingQuery->where('correction_request_attendances.user_id', $user->id);
        }

        $pendingRequests = $pendingQuery->paginate(5, ['*'], 'pending_page');

        $approvedQuery = CorrectionRequestAttendance::with('attendance','user')
            ->where('correction_request_attendances.status', CorrectionRequestAttendance::STATUS_APPROVED)
            ->join('attendances', 'correction_request_attendances.attendance_id', '=', 'attendances.id')
            ->orderBy('attendances.work_date', 'asc')
            ->select('correction_request_attendances.*');

        if (!$isAdmin) {
            $approvedQuery->where('correction_request_attendances.user_id', $user->id);
        }

        $approvedRequests = $approvedQuery->paginate(5, ['*'], 'approved_page');

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

        $request->status = CorrectionRequestAttendance::STATUS_APPROVED;
        $request->save();

        $attendance = $request->attendance;

        $attendance->work_start_datetime = $request->requested_work_start_datetime;
        $attendance->work_end_datetime = $request->requested_work_end_datetime;
        $attendance->note = $request->requested_note;

        $attendance->save();

        $existingBreaks = $attendance->breakTimes;

        foreach ($request->breaks as $index => $break) {

            if (isset($existingBreaks[$index])) {
                $existingBreaks[$index]->update([
                    'break_start' => $break->break_start,
                    'break_end' => $break->break_end,
                ]);
            } else {
                $attendance->breakTimes()->create([
                    'break_start' => $break->break_start,
                    'break_end' => $break->break_end,
                ]);
            }
        }

        if (count($existingBreaks) > count($request->breaks)) {
            foreach ($existingBreaks->slice(count($request->breaks)) as $break) {
                $break->delete();
            }
        }

        $attendance->updateStatus();
        $attendance->save();

        return back()->with('success', '承認に成功しました');;
    }
}