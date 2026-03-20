<?php

namespace App\Http\Controllers;

use App\Models\CorrectionRequestAttendance;
use Illuminate\Support\Facades\Auth;

class StampCorrectionRequestController extends Controller
{

    public function list()
    {

        // 承認待ち
        $pendingRequests = CorrectionRequestAttendance::with('attendance','user')
            ->where('correction_request_attendances.user_id', Auth::id())
            ->where('correction_request_attendances.status', CorrectionRequestAttendance::STATUS_PENDING)
            ->join('attendances', 'correction_request_attendances.attendance_id', '=', 'attendances.id')
            ->orderBy('attendances.work_date', 'asc')
            ->select('correction_request_attendances.*')
            ->get();

        // 承認済み
        $approvedRequests = CorrectionRequestAttendance::with('attendance','user')
            ->where('correction_request_attendances.user_id', Auth::id())
            ->where('correction_request_attendances.status', CorrectionRequestAttendance::STATUS_APPROVED)
            ->join('attendances', 'correction_request_attendances.attendance_id', '=', 'attendances.id')
            ->orderBy('attendances.work_date', 'asc')
            ->select('correction_request_attendances.*')
            ->get();

        return view('stamp_correction_request.list', compact(
            'pendingRequests',
            'approvedRequests'
        ));

    }

}