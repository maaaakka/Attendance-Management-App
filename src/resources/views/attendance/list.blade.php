@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')

<div class="attendance-list">

<h1 class="page-title">勤怠一覧</h1>

<div class="month-nav">

<a href="{{ route('attendance.list',['month'=>\Carbon\Carbon::parse($month)->subMonth()->format('Y-m')]) }}">
<span class="arrow">←</span> 前月
</a>

<div class="month">
    <span class="calendar-icon">📅</span>
{{ \Carbon\Carbon::parse($month)->format('Y/m') }}
</div>

<a href="{{ route('attendance.list',['month'=>\Carbon\Carbon::parse($month)->addMonth()->format('Y-m')]) }}">
翌月 <span class="arrow">→</span>
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
$attendance = $attendances[$date->format('Y-m-d')] ?? null;
@endphp

<tr>

<!-- 日付 -->
<td>
{{ $date->format('m/d') }}({{ ['日','月','火','水','木','金','土'][$date->dayOfWeek] }})
</td>

<!-- 出勤時間 -->
<td>
{{ $attendance?->work_start_datetime 
    ? \Carbon\Carbon::parse($attendance->work_start_datetime)->format('H:i') 
    : '' }}
</td>

<!-- 退勤時間 -->
<td>
{{ $attendance?->work_end_datetime 
    ? \Carbon\Carbon::parse($attendance->work_end_datetime)->format('H:i') 
    : '' }}
</td>

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

<td>

<a href="{{ route('attendance.detail', $attendance?->id ?? $date->format('Y-m-d')) }}">
詳細
</a>

</td>

</tr>

@endforeach

</tbody>

</table>

</div>

@endsection