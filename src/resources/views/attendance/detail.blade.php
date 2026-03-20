@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')

<div class="attendance-detail">

<h2 class="page-title">勤怠詳細</h2>

<form method="POST" action="{{ route('attendance.request', $attendance->id ?: $attendance->work_date) }}">
    @csrf

    <input type="hidden" name="work_date" value="{{ $attendance->work_date }}">

    <table class="detail-table">

    {{-- 名前 --}}
    <tr>
        <th>名前</th>
        <td>
            {{ $attendance->user->name ?? auth()->user()->name }}
        </td>
    </tr>

    {{-- 日付 --}}
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

    {{-- 出退勤 --}}
    <tr>
        <th>出勤・退勤</th>
        <td>

        @if($pendingRequest)

            {{ \Carbon\Carbon::parse($pendingRequest->requested_work_start_datetime)->format('H:i') }}
            ～
            {{ \Carbon\Carbon::parse($pendingRequest->requested_work_end_datetime)->format('H:i') }}

        @else

            <div class="time-group">
                <input type="time" name="work_start_datetime"
                    value="{{ old('work_start_datetime', $attendance->work_start_datetime ? \Carbon\Carbon::parse($attendance->work_start_datetime)->format('H:i') : '') }}">

                <span class="time-sep">～</span>

                <input type="time" name="work_end_datetime"
                    value="{{ old('work_end_datetime', $attendance->work_end_datetime ? \Carbon\Carbon::parse($attendance->work_end_datetime)->format('H:i') : '') }}">
            </div>

            @error('work_start_datetime')
                <p class="error">{{ $message }}</p>
            @enderror

            @error('work_end_datetime')
                <p class="error">{{ $message }}</p>
            @enderror

        @endif

        </td>
    </tr>

    @php
        $breaks = $pendingRequest ? $pendingRequest->breaks : $attendance->breakTimes;
    @endphp

    {{-- 休憩 --}}
    @if($breaks->isEmpty())

    <tr>
        <th>休憩1</th>
        <td>

        @if($pendingRequest)
            {{-- 空表示 --}}
        @else
            <div class="time-group">
                <input type="time" name="break_start[]" value="{{ old('break_start.0') }}">
                <span class="time-sep">～</span>
                <input type="time" name="break_end[]" value="{{ old('break_end.0') }}">
            </div>

            @error("break_start.0")
                <p class="error">{{ $message }}</p>
            @enderror

            @error("break_end.0")
                <p class="error">{{ $message }}</p>
            @enderror
        @endif

        </td>
    </tr>

    @else

        @foreach($breaks as $index => $break)
        <tr>
            <th>休憩{{ $index+1 }}</th>
            <td>

            @if($pendingRequest)

                {{ \Carbon\Carbon::parse($break->break_start)->format('H:i') }}
                ～
                {{ $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '' }}

            @else

                <div class="time-group">
                    <input type="time" name="break_start[]"
                        value="{{ old("break_start.$index", \Carbon\Carbon::parse($break->break_start)->format('H:i')) }}">

                    <span class="time-sep">～</span>

                    <input type="time" name="break_end[]"
                        value="{{ old("break_end.$index", $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '') }}">
                </div>

                @error("break_start.$index")
                    <p class="error">{{ $message }}</p>
                @enderror

                @error("break_end.$index")
                    <p class="error">{{ $message }}</p>
                @enderror

            @endif

            </td>
        </tr>
        @endforeach

    @endif

    {{-- 休憩追加 --}}
    @if(!$pendingRequest && $breaks->isNotEmpty())
    @php
        $nextIndex = count($breaks);
    @endphp

    <tr>
        <th>休憩{{ $nextIndex + 1 }}</th>
        <td>

            <div class="time-group">
                <input type="time" name="break_start[]" value="{{ old("break_start.$nextIndex") }}">
                <span class="time-sep">～</span>
                <input type="time" name="break_end[]" value="{{ old("break_end.$nextIndex") }}">
            </div>

            @error("break_start.$nextIndex")
                <p class="error">{{ $message }}</p>
            @enderror

            @error("break_end.$nextIndex")
                <p class="error">{{ $message }}</p>
            @enderror

        </td>
    </tr>
    @endif

    {{-- 備考 --}}
    <tr>
        <th>備考</th>
        <td>

        @if($pendingRequest)

            {{ $pendingRequest->requested_note }}

        @else

            <textarea name="note" class="note-area">{{ old('note', $attendance->note) }}</textarea>

            @error('note')
                <p class="error">{{ $message }}</p>
            @enderror

        @endif

        </td>
    </tr>

    </table>

    {{-- 承認待ち --}}
    @if($pendingRequest)
        <p class="pending-message">
            *承認待ちのため修正はできません。
        </p>
    @endif

    {{-- ボタン --}}
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