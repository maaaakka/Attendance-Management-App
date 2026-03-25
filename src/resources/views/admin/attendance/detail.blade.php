@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')

<div class="attendance-detail">

<h2 class="page-title">勤怠詳細</h2>

<form method="POST" action="{{ route('admin.attendance.update', $attendance->id) }}">
    @csrf

    <table class="detail-table">

    {{-- 名前 --}}
    <tr>
        <th>名前</th>
        <td>{{ $attendance->user->name }}</td>
    </tr>

    {{-- 日付 --}}
    <tr>
        <th>日付</th>
        <td>
            {{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年n月j日') }}
        </td>
    </tr>

    {{-- 出退勤 --}}
    <tr>
        <th>出勤・退勤</th>
        <td>
            <div class="time-group">
                <input type="time" name="work_start_datetime"
                    value="{{ old('work_start_datetime', \Carbon\Carbon::parse($attendance->work_start_datetime)->format('H:i')) }}">

                <span>～</span>

                <input type="time" name="work_end_datetime"
                    value="{{ old('work_end_datetime', \Carbon\Carbon::parse($attendance->work_end_datetime)->format('H:i')) }}">
            </div>
        </td>
    </tr>

    {{-- 休憩 --}}
    @foreach($attendance->breakTimes as $index => $break)
    <tr>
        <th>休憩{{ $index + 1 }}</th>
        <td>
            <div class="time-group">
                <input type="time" name="break_start[]"
                    value="{{ old("break_start.$index", \Carbon\Carbon::parse($break->break_start)->format('H:i')) }}">

                <span>～</span>

                <input type="time" name="break_end[]"
                    value="{{ old("break_end.$index", $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '') }}">
            </div>
        </td>
    </tr>
    @endforeach

    {{-- 休憩追加 --}}
    <tr>
        <th>休憩{{ count($attendance->breakTimes) + 1 }}</th>
        <td>
            <div class="time-group">
                <input type="time" name="break_start[]">
                <span>～</span>
                <input type="time" name="break_end[]">
            </div>
        </td>
    </tr>

    {{-- 備考 --}}
    <tr>
        <th>備考</th>
        <td>
            <textarea name="note">{{ old('note', $attendance->note) }}</textarea>
        </td>
    </tr>

    </table>

    <div class="btn-area">
        <button type="submit" class="btn-edit">
            修正
        </button>
    </div>

</form>

</div>

@endsection