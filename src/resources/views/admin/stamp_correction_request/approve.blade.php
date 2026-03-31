@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')

<div class="attendance-detail">

<h1 class="page-title">勤怠詳細</h1>

<form method="POST" action="{{ route('stamp_correction_request.approve.update', $request->id) }}">
    @csrf

    <table class="detail-table">

    {{-- 名前 --}}
    <tr>
        <th>名前</th>
        <td>
            {{ $request->user->name }}
        </td>
    </tr>

    {{-- 日付 --}}
    <tr>
        <th>日付</th>
        <td>
            <div class="date-row">
                <span class="year">
                {{ \Carbon\Carbon::parse($request->attendance->work_date)->format('Y年') }}
                </span>
                <span class="month-day">
                    {{ \Carbon\Carbon::parse($request->attendance->work_date)->format('n月j日') }}
                </span>
            </div>
        </td>
    </tr>

    {{-- 出退勤 --}}
    <tr>
        <th>出勤・退勤</th>
        <td>
            <div class="time-range">
                <span>{{ \Carbon\Carbon::parse($request->requested_work_start_datetime)->format('H:i') }}</span>
                <span class="tilde">〜</span>
                <span>{{ \Carbon\Carbon::parse($request->requested_work_end_datetime)->format('H:i') }}</span>
            </div>
        </td>
    </tr>

    @php
        $breaks = $request->breaks;
    @endphp

    {{-- 休憩がある場合 --}}
    @if($breaks->isNotEmpty())

        @foreach($breaks as $index => $break)
        <tr>
            <th>休憩{{ $index + 1 }}</th>
            <td>
                <div class="time-range">
                    <span>{{ \Carbon\Carbon::parse($break->break_start)->format('H:i') }}</span>
                    <span class="tilde">〜</span>
                    <span>{{ $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '' }}</span>
                </div>
            </td>
        </tr>
        @endforeach

    {{-- 休憩がない場合 --}}
    @else

        <tr>
            <th>休憩1</th>
            <td>
                {{-- 空 or ハイフン --}}
            </td>
        </tr>

    @endif
    {{-- 備考 --}}
    <tr>
        <th>備考</th>
        <td>
            {{ $request->requested_note }}
        </td>
    </tr>

    </table>

    {{-- 承認ボタン --}}
    <div class="btn-area">
    @if($request->status == \App\Models\CorrectionRequestAttendance::STATUS_APPROVED)

        <button class="btn-approve" disabled style="background-color: gray;">
            承認済み
        </button>

    @else

        <button type="submit" class="btn-approve">
            承認する
        </button>

    @endif
</div>

</form>

</div>
@endsection