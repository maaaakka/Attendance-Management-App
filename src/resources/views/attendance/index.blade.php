@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')

@php use App\Models\Attendance; @endphp

<div class="attendance-wrapper">

    <div class="attendance-card">

        {{-- ステータス表示 --}}
        <div class="status">
            @if ($status == Attendance::STATUS_OFF_WORK)
                勤務外
            @elseif ($status == Attendance::STATUS_WORKING)
                出勤中
            @elseif ($status == Attendance::STATUS_ON_BREAK)
                休憩中
            @elseif ($status == Attendance::STATUS_LEFT)
                退勤済
            @endif
        </div>

        {{-- 日付 --}}
        @php
        $week = ['日','月','火','水','木','金','土'];
        @endphp

        <div class="date">
        {{ now()->format('Y年n月j日') }}（{{ $week[now()->dayOfWeek] }}）
        </div>

        {{-- 時刻 --}}
        <div class="time">
            {{ now()->format('H:i') }}
        </div>

        {{-- ボタン --}}
        <div class="buttons">

            {{-- 出勤前 --}}
            @if ($status == Attendance::STATUS_OFF_WORK)
            <form method="POST" action="{{ route('attendance.start') }}">
                @csrf
                <button class="btn-work">出勤</button>
            </form>
            @endif

            {{-- 出勤中 --}}
            @if ($status == Attendance::STATUS_WORKING)
            <form method="POST" action="{{ route('attendance.end') }}">
                @csrf
                <button class="btn-work">退勤</button>
            </form>

            <form method="POST" action="{{ route('attendance.break.start') }}">
                @csrf
                <button class="btn-rest">休憩入</button>
            </form>
            @endif

            {{-- 休憩中 --}}
            @if ($status == Attendance::STATUS_ON_BREAK)
            <form method="POST" action="{{ route('attendance.break.end') }}">
                @csrf
                <button class="btn-rest">休憩戻</button>
            </form>
            @endif

            {{-- 退勤済 --}}
            @if ($status == Attendance::STATUS_LEFT)
                <p>お疲れ様でした。</p>
            @endif

        </div>

    </div>

</div>
@endsection