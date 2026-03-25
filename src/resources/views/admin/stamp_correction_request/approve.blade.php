@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')

<div class="attendance-detail">

<h2 class="page-title">勤怠詳細</h2>

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
            {{ \Carbon\Carbon::parse($request->attendance->work_date)->format('Y年n月j日') }}
        </td>
    </tr>

    {{-- 出退勤 --}}
    <tr>
        <th>出勤・退勤</th>
        <td>
            {{ \Carbon\Carbon::parse($request->requested_work_start_datetime)->format('H:i') }}
            ～
            {{ \Carbon\Carbon::parse($request->requested_work_end_datetime)->format('H:i') }}
        </td>
    </tr>

    @php
        $breakCount = count($request->breaks);
    @endphp
    {{-- 休憩 --}}
    @foreach($request->breaks as $index => $break)
    <tr>
        <th>休憩{{ $index + 1 }}</th>
        <td>
            {{ \Carbon\Carbon::parse($break->break_start)->format('H:i') }}
            ～
            {{ $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '' }}
        </td>
    </tr>
    @endforeach

    <tr>
    <th>休憩{{ $breakCount + 1 }}</th>
    <td>
        {{-- 空表示 or ハイフン --}}
        
    </td>
</tr>
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