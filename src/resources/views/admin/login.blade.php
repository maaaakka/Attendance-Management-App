@extends('layouts.admin_app')

@section('title')
ログイン
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')

<div class="auth-card">

<h1 class="auth-title">管理者ログイン</h1>

<form method="POST" action="/admin/login">
@csrf
<input type="hidden" name="is_admin" value="1">

<div class="form-group">
    <label>メールアドレス</label>
    <input type="email" name="email" value="{{ old('email') }}">
    @error('email')
        <p class="error">{{ $message }}</p>
    @enderror
</div>

<div class="form-group">
    <label>パスワード</label>
    <input type="password" name="password">
    @error('password')
        <p class="error">{{ $message }}</p>
    @enderror
</div>

<button class="auth-button">管理者ログインする
</button>

</form>

</div>

@endsection