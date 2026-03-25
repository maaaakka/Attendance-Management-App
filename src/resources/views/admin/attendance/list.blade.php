@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')

<div class="attendance-list">

<h2 class="page-title">勤怠一覧</h2>

{{-- 日付ナビ --}}
<div class="month-nav">

<a href="{{ url('/admin/attendance/list?date=' . \Carbon\Carbon::parse($date)->subDay()->format('Y-m-d')) }}">
<span class="arrow">←</span> 前日
</a>

<div class="month">
    <span class="calendar-icon">📅</span>
    {{ \Carbon\Carbon::parse($date)->format('Y/m/d') }}
</div>

<a href="{{ url('/admin/attendance/list?date=' . \Carbon\Carbon::parse($date)->addDay()->format('Y-m-d')) }}">
翌日 <span class="arrow">→</span>
</a>

</div>

<table class="attendance-table">

<thead>
<tr>
<th>名前</th>
<th>出勤</th>
<th>退勤</th>
<th>休憩</th>
<th>合計</th>
<th>詳細</th>
</tr>
</thead>

<tbody>

@foreach($users as $user)

@php
$attendance = $user->attendances->first();
@endphp

<tr>

<!-- 名前 -->
<td>{{ $user->name }}</td>

<!-- 出勤 -->
<td>
{{ $attendance?->work_start_datetime 
    ? \Carbon\Carbon::parse($attendance->work_start_datetime)->format('H:i') 
    : '' }}
</td>

<!-- 退勤 -->
<td>
{{ $attendance?->work_end_datetime 
    ? \Carbon\Carbon::parse($attendance->work_end_datetime)->format('H:i') 
    : '' }}
</td>

<!-- 休憩 -->
<td>
@php
$breakTotal = 0;

if ($attendance) {
    foreach ($attendance->breakTimes as $break) {
        if ($break->break_end) {
            $breakTotal += strtotime($break->break_end) - strtotime($break->break_start);
        }
    }
}
@endphp

{{ $breakTotal ? gmdate('H:i', floor($breakTotal / 60) * 60) : '' }}
</td>

<!-- 合計 -->
<td>
@php
$workTotal = 0;

if ($attendance && $attendance->work_end_datetime) {
    $workTotal = strtotime($attendance->work_end_datetime)
        - strtotime($attendance->work_start_datetime)
        - $breakTotal;
}
@endphp

{{ $workTotal ? gmdate('H:i', floor($workTotal / 60) * 60) : '' }}
</td>

<!-- 詳細 -->
<td>
@if($attendance)
<a href="{{ url('/admin/attendance/' . $attendance->id) }}">
詳細
</a>
@endif
</td>

</tr>

@endforeach

</tbody>

</table>

</div>

@endsection