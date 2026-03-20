@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')

<div class="request-list">

<h2 class="page-title">申請一覧</h2>

<div class="tab-menu">
    <a href="?status=pending" class="{{ request('status') !== 'approved' ? 'active' : '' }}">
        承認待ち
    </a>
    <a href="?status=approved" class="{{ request('status') === 'approved' ? 'active' : '' }}">
        承認済み
    </a>
</div>

<table class="request-table">

<thead>
<tr>
<th>状態</th>
<th>名前</th>
<th>対象日時</th>
<th>申請理由</th>
<th>申請日時</th>
<th>詳細</th>
</tr>
</thead>

<tbody>

@if(request('status') === 'approved')

@foreach($approvedRequests as $request)

<tr>

<td>承認済み</td>

<td>{{ $request->user->name }}</td>

<td>
{{ \Carbon\Carbon::parse($request->attendance->work_date)->format('Y/m/d') }}
</td>

<td>{{ $request->note }}</td>

<td>
{{ \Carbon\Carbon::parse($request->created_at)->format('Y/m/d') }}
</td>

<td>
<a href="{{ route('attendance.detail',$request->attendance_id) }}">
詳細
</a>
</td>

</tr>

@endforeach

@else

@foreach($pendingRequests as $request)

<tr>

<td>承認待ち</td>

<td>{{ $request->user->name }}</td>

<td>
{{ \Carbon\Carbon::parse($request->attendance->work_date)->format('Y/m/d') }}
</td>

<td>{{ $request->requested_note }}</td>

<td>
{{ \Carbon\Carbon::parse($request->created_at)->format('Y/m/d') }}
</td>

<td>
<a href="{{ route('attendance.detail',$request->attendance_id) }}">
詳細
</a>
</td>

</tr>

@endforeach

@endif

</tbody>

</table>

</div>

@endsection