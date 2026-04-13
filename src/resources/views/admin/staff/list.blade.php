@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')

<div class="attendance-list staff-list">

<h1 class="page-title">スタッフ一覧</h1>

<table class="attendance-table staff-table">

<thead>
<tr>
    <th>名前</th>
    <th>メールアドレス</th>
    <th>月次勤怠</th>
</tr>
</thead>

<tbody>

@foreach($users as $user)
<tr>
    <td>{{ $user->name }}</td>
    <td>{{ $user->email }}</td>

    <td>
        <a href="{{ url('/admin/attendance/staff/' . $user->id) }}">
            詳細
        </a>
    </td>
</tr>
@endforeach

</tbody>

</table>

    <div class="pagination-wrapper">
        {{ $users->links() }}
    </div>

</div>

@endsection