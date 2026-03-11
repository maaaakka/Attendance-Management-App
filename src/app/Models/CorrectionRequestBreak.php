<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectionRequestBreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'break_id',
        'break_start',
        'break_end',
    ];

    public function request()
    {
        return $this->belongsTo(CorrectionRequestAttendance::class, 'request_id');
    }

    public function breakTime()
    {
        return $this->belongsTo(BreakTime::class, 'break_id');
    }
}
