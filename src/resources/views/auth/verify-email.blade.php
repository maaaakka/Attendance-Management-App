@extends('layouts.app')

@php
$hideHeaderNav = true;
@endphp

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')

<div class="auth-wrapper">

    <div class="auth-card">

        <p class="auth-text">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>

        {{-- メール確認ボタン --}}
        <a href="http://localhost:8025" target="_blank" class="auth-main-btn">
            認証はこちらから
        </a>

        {{-- 再送メッセージ --}}
        @if (session('status') == 'verification-link-sent')
            <p class="auth-success">
                認証メールを再送しました。
            </p>
        @endif

        {{-- 再送ボタン --}}
        <form method="POST" action="{{ route('verification.send') }}" class="resend-form">
            @csrf
            <button type="submit" class="auth-resend">
                認証メールを再送する
            </button>
        </form>

    </div>

</div>

@endsection