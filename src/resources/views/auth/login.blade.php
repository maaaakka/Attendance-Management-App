@extends('layouts.app')

@section('title')
ログイン
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')

<div class="auth-card">

<h1 class="auth-title">ログイン</h1>

<form method="POST" action="/login">
@csrf

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

<button class="auth-button">ログインする
</button>

</form>

<div class="auth-link">
    <a href="/register">会員登録はこちら</a>
</div>

</div>

@endsection