@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')

<div class="attendance-detail">

<h1 class="page-title">勤怠詳細</h1>

@if($attendance->id)
    {{-- 更新 --}}
    <form method="POST" action="{{ route('admin.attendance.update', $attendance->id) }}">
@else
    {{-- 新規作成 --}}
    <form method="POST" action="{{ route('admin.attendance.store') }}">
@endif

@csrf

<input type="hidden" name="user_id" value="{{ $attendance->user_id }}">
<input type="hidden" name="work_date" value="{{ $attendance->work_date }}">

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
            <div class="time-group">
                <input type="time" name="work_start_datetime"
                    value="{{ old('work_start_datetime',
                        $attendance->work_start_datetime
                            ? \Carbon\Carbon::parse($attendance->work_start_datetime)->format('H:i')
                            : '') }}" {{ $pendingRequest ? 'disabled' : '' }}>

                <span>～</span>

                <input type="time" name="work_end_datetime"
                    value="{{ old('work_end_datetime',
                        $attendance->work_end_datetime
                            ? \Carbon\Carbon::parse($attendance->work_end_datetime)->format('H:i')
                            : '') }}" {{ $pendingRequest ? 'disabled' : '' }}>
            </div>

            @error('work_start_datetime')
                <p class="error">{{ $message }}</p>
            @enderror

            @error('work_end_datetime')
                <p class="error">{{ $message }}</p>
            @enderror
        </td>
    </tr>
    {{-- 休憩 --}}
    @foreach($attendance->breakTimes as $index => $break)
    <tr>
        <th>休憩{{ $index + 1 }}</th>
        <td>
            <div class="time-group">

                <input type="time" name="break_start[]"
                    value="{{ old("break_start.$index",
                        $break->break_start
                            ? \Carbon\Carbon::parse($break->break_start)->format('H:i')
                            : '') }}" {{ $pendingRequest ? 'disabled' : '' }}>

                <span>～</span>

                <input type="time" name="break_end[]"
                    value="{{ old("break_end.$index",
                        $break->break_end
                            ? \Carbon\Carbon::parse($break->break_end)->format('H:i')
                            : '') }}"
                            {{ $pendingRequest ? 'disabled' : '' }}>

            </div>

            @error("break_start.$index")
                <p class="error">{{ $message }}</p>
            @enderror

            @error("break_end.$index")
                <p class="error">{{ $message }}</p>
            @enderror
        </td>
    </tr>
    @endforeach

    {{-- 休憩追加 --}}
    @php
    $nextIndex = count($attendance->breakTimes);
    @endphp

    <tr>
        <th>休憩{{ $nextIndex + 1 }}</th>
        <td>
            <div class="time-group">
                <input type="time" name="break_start[]"
                    value="{{ old("break_start.$nextIndex") }}"
                    {{ $pendingRequest ? 'disabled' : '' }}>

                <span>～</span>

                <input type="time" name="break_end[]"
                    value="{{ old("break_end.$nextIndex") }}"
                    {{ $pendingRequest ? 'disabled' : '' }}>
            </div>

            @error("break_start.$nextIndex")
                <p class="error">{{ $message }}</p>
            @enderror

            @error("break_end.$nextIndex")
                <p class="error">{{ $message }}</p>
            @enderror
        </td>
    </tr>

    {{-- 備考 --}}
    <tr>
        <th>備考</th>
        <td>

        @if($pendingRequest)

            {{-- 申請中 → 編集不可 --}}
            <textarea name="note" class="note-area" {{ $pendingRequest ? 'disabled' : '' }}>
                {{ old('note', $attendance->note) }}
            </textarea>

        @else

            {{-- 通常 → 編集可能 --}}
            <textarea name="note" class="note-area">
                {{ old('note', $attendance->note) }}
            </textarea>

            @error('note')
                <p class="error">{{ $message }}</p>
            @enderror

        @endif

        </td>
    </tr>

    </table>

    {{-- 修正ボタン --}}
    @if(!$pendingRequest)
        <div class="btn-area">
            <button type="submit" class="btn-edit">
                修正
            </button>
        </div>
    @endif

    {{-- 申請中メッセージ --}}
    @if($pendingRequest)
        <p class="pending-message">
            ※修正申請中のため修正はできません。
        </p>
    @endif
</form>

</div>

@endsection