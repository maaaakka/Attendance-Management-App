@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')

<div class="attendance-list">

<h1 class="page-title">{{ $user->name }} さんの勤怠</h1>

{{-- 月ナビ --}}
<div class="month-nav">

<a href="{{ url('/admin/attendance/staff/' . $user->id . '?month=' . \Carbon\Carbon::parse($month)->subMonth()->format('Y-m')) }}">
    ←前月
</a>

<div class="month">
    <span class="calendar-icon">📅</span>
    {{ \Carbon\Carbon::parse($month)->format('Y年m月') }}
</div>

<a href="{{ url('/admin/attendance/staff/' . $user->id . '?month=' . \Carbon\Carbon::parse($month)->addMonth()->format('Y-m')) }}">
    翌月→
</a>

</div>

<table class="attendance-table">
<thead>
<tr>
<th>日付</th>
<th>出勤</th>
<th>退勤</th>
<th>休憩</th>
<th>合計</th>
<th>詳細</th>
</tr>
</thead>

<tbody>
@foreach($dates as $date)

@php
$key = $date->format('Y-m-d');
$attendance = $attendances[$key] ?? null;

$breakTotal = 0;

if ($attendance) {
    foreach ($attendance->breakTimes as $break) {
        if ($break->break_end) {
            $breakTotal += strtotime($break->break_end) - strtotime($break->break_start);
        }
    }
}

$workTotal = 0;
if ($attendance && $attendance->work_end_datetime) {
    $workTotal =
        strtotime($attendance->work_end_datetime)
        - strtotime($attendance->work_start_datetime)
        - $breakTotal;
}
@endphp

<tr>

<td>{{ $date->format('m/d') }}</td>

<td>
{{ $attendance?->work_start_datetime
    ? \Carbon\Carbon::parse($attendance->work_start_datetime)->format('H:i')
    : '' }}
</td>

<td>
{{ $attendance?->work_end_datetime
    ? \Carbon\Carbon::parse($attendance->work_end_datetime)->format('H:i')
    : '' }}
</td>

<td>
{{ $breakTotal ? gmdate('H:i', floor($breakTotal / 60) * 60) : '' }}
</td>

<td>
{{ $workTotal ? gmdate('H:i', floor($workTotal / 60) * 60) : '' }}
</td>

<td>
@if($attendance)
    {{-- データあり → attendance_id --}}
    <a href="{{ url('/admin/attendance/' . $attendance->id) }}">
        詳細
    </a>
@else
    {{-- データなし → user_id + date --}}
    <a href="{{ url('/admin/attendance/' . $user->id . '?date=' . $key) }}">
        詳細
    </a>
@endif
</td>

</tr>

@endforeach

</tbody>

</table>

<div class="btn-area">
    <a href="{{ route('admin.attendance.staff.csv', [
        'id' => $user->id,
        'month' => $month
    ]) }}" class="csv-btn">
        CSV出力
    </a>
</div>

</div>

@endsection