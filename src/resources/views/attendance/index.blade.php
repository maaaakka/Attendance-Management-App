@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')

<div class="attendance-wrapper">

    <div class="attendance-card">

        {{-- ステータス表示 --}}
        <div class="status">
            @if ($status == 0)
                勤務外
            @elseif ($status == 1)
                出勤中
            @elseif ($status == 2)
                休憩中
            @elseif ($status == 3)
                退勤済
            @endif
        </div>

        {{-- 日付 --}}
        <div class="date">
            {{ now()->format('Y年n月j日 (D)') }}
        </div>

        {{-- 時刻 --}}
        <div class="time">
            {{ now()->format('H:i') }}
        </div>

        {{-- ボタン --}}
        <div class="buttons">

            {{-- 出勤前 --}}
            @if ($status == 0)
                <button class="btn-work">出勤</button>
            @endif

            {{-- 出勤中 --}}
            @if ($status == 1)
                <button class="btn-work">退勤</button>
                <button class="btn-rest">休憩入</button>
            @endif

            {{-- 休憩中 --}}
            @if ($status == 2)
                <button class="btn-rest">休憩戻</button>
            @endif

            {{-- 退勤済 --}}
            @if ($status == 3)
                <p>お疲れ様でした。</p>
            @endif

        </div>

    </div>

</div>

@endsection