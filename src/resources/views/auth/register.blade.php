@extends('layouts.app')

@section('title')
会員登録
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')

<div class="auth-card">

<h1 class="auth-title">会員登録</h1>

<form method="POST" action="/register">
@csrf

<div class="form-group">
    <label>名前</label>
    <input type="text" name="name" value="{{ old('name') }}">
    @error('name')
        <p class="error">{{ $message }}</p>
    @enderror
</div>

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

<div class="form-group">
    <label>パスワード確認</label>
    <input type="password" name="password_confirmation">
</div>

<button class="auth-button">登録する
</button>

</form>

<div class="auth-link">
    <a href="/login">ログインはこちら</a>
</div>

</div>

@endsection