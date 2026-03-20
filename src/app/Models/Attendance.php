<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    // ⭐ ステータス定数
    const STATUS_OFF_WORK = 1;   // 勤務外
    const STATUS_WORKING = 2;    // 勤務中
    const STATUS_ON_BREAK = 3;   // 休憩中
    const STATUS_LEFT = 4;       // 退勤済

    protected $fillable = [
        'user_id',
        'work_date',
        'work_start_datetime',
        'work_end_datetime',
        'note',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function correctionRequests()
    {
        return $this->hasMany(CorrectionRequestAttendance::class);
    }
}