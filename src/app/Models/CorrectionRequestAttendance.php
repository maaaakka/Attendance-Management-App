<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectionRequestAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'user_id',
        'requested_work_start_datetime',
        'requested_work_end_datetime',
        'requested_note',
        'status',
        'approved_by',
        'approved_at',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breaks()
    {
        return $this->hasMany(CorrectionRequestBreak::class, 'request_id');
    }

    public function approver()
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }
}
