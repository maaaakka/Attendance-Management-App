@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')

<div class="attendance-detail">

<h2 class="page-title">勤怠詳細</h2>

<form method="POST" action="{{ route('attendance.request',$attendance->id) }}">
@csrf

<table class="detail-table">

<tr>
<th>名前</th>
<td>
{{ $attendance->user->name ?? auth()->user()->name }}
</td>
</tr>

<tr>
<th>日付</th>
<td>

<div class="date-row">

<span class="year">
{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年') }}
</span>

<span class="month-day">
{{ \Carbon\Carbon::parse($attendance->work_date)->format('n月j日') }}
</span>

</div>

</td>
</tr>
<th>出勤・退勤</th>
<td>

@if(!$pendingRequest)

<div class="time-group">

<input type="time"
name="work_start_datetime"
value="{{ $attendance->work_start_datetime ? \Carbon\Carbon::parse($attendance->work_start_datetime)->format('H:i') : '' }}">

<span class="time-sep">～</span>

<input type="time"
name="work_end_datetime"
value="{{ $attendance->work_end_datetime ? \Carbon\Carbon::parse($attendance->work_end_datetime)->format('H:i') : '' }}">

</div>
@error('work_start_datetime')
<p class="error">{{ $message }}</p>
@enderror
@error('work_end_datetime')
<p class="error">{{ $message }}</p>
@enderror
@else

{{ \Carbon\Carbon::parse($attendance->work_start_datetime)->format('H:i') }}
～
{{ \Carbon\Carbon::parse($attendance->work_end_datetime)->format('H:i') }}

@endif

</td>
</tr>


{{-- 休憩 --}}
@foreach($attendance->breakTimes as $index => $break)

<tr>

<th>休憩{{ $index+1 }}</th>

<td>

@if(!$pendingRequest)

<div class="time-group">

<input type="time"
name="break_start[]"
value="{{ \Carbon\Carbon::parse($break->break_start)->format('H:i') }}">

<span class="time-sep">～</span>

<input type="time"
name="break_end[]"
value="{{ $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '' }}">

</div>

{{-- エラー表示 --}}
@error("break_start.$index")
<p class="error">{{ $message }}</p>
@enderror

@error("break_end.$index")
<p class="error">{{ $message }}</p>
@enderror

@else

{{ \Carbon\Carbon::parse($break->break_start)->format('H:i') }}
～
{{ $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '' }}

@endif

</td>

</tr>

@endforeach


{{-- 休憩追加 --}}
@if(!$pendingRequest)

@php
$nextIndex = count($attendance->breakTimes);
@endphp

<tr>

<th>休憩{{ $nextIndex + 1 }}</th>

<td>

<div class="time-group">

<input type="time" name="break_start[]">

<span class="time-sep">～</span>

<input type="time" name="break_end[]">

</div>

{{-- 追加休憩のエラー --}}
@error("break_start.$nextIndex")
<p class="error">{{ $message }}</p>
@enderror

@error("break_end.$nextIndex")
<p class="error">{{ $message }}</p>
@enderror

</td>

</tr>

@endif

<th>備考</th>

<td>

@if(!$pendingRequest)

<textarea name="note" class="note-area">
{{ $attendance->note }}
</textarea>
@error('note')
<p class="error">{{ $message }}</p>
@enderror
@else

{{ $attendance->note }}

@endif

</td>

</tr>

</table>


@if($pendingRequest)

<p class="pending-message">
*承認待ちのため修正はできません。
</p>

@endif


@if(!$pendingRequest)

<div class="btn-area">

<button type="submit" class="btn-edit">
修正
</button>

</div>

@endif

</form>

</div>

@endsection