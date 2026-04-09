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
            <a href="{{ Auth::guard('admin')->check() ? '/admin/attendance/list' : '/' }}">
                <img src="{{ asset('images/COACHTECHlogo.png') }}" alt="COACHTECH">
            </a>
        </div>

        @if(Auth::guard('admin')->check())
            @if (!isset($hideHeaderNav))
            <nav class="nav">
                <a href="/admin/attendance/list">勤怠一覧</a>
                <a href="/admin/staff/list">スタッフ一覧</a>
                <a href="/stamp_correction_request/list">申請一覧</a>

                {{-- 管理者用のログアウトフォーム --}}
                <form method="POST" action="{{ route('admin.logout') }}" class="logout-form">
                    @csrf
                    <button type="submit" class="logout-button">
                        ログアウト
                    </button>
                </form>
            </nav>
            @endif
        @endif

    </div>
</header>
    @if(session('success'))
        <div class="toast">
            {{ session('success') }}
        </div>
    @endif
<main>
    @yield('content')
</main>
</body>
</html>