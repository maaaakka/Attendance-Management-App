<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH</title>

    <link rel="stylesheet" href="{{ asset('css/common.css') }}">

    @yield('css')
</head>

<body>

<header class="header">
    <div class="header-inner">

        {{-- ロゴ（常に表示） --}}
        <div class="logo">
            <a href="/attendance">
                <img src="{{ asset('images/COACHTECHlogo.png') }}" alt="COACHTECH">
            </a>
        </div>

        {{-- ログインしているときだけ表示 --}}
        @auth
        @if (!isset($hideHeaderNav))
        <nav class="nav">

            <a href="/attendance">勤怠</a>
            <a href="/attendance/list">勤怠一覧</a>
            <a href="/stamp_correction_request/list">申請</a>

            <form method="POST" action="{{ route('logout') }}" class="logout-form">
                @csrf
                <button type="submit" class="logout-button">
                    ログアウト
                </button>
            </form>

        </nav>
        @endif
        @endauth

    </div>
</header>

<main>
    @yield('content')
</main>

</body>
</html>