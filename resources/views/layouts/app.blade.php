<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Honda Babel Leaderboard' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen">
    <div class="mx-auto min-h-screen max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <header class="card-glass mb-6 border-red-600/40 px-5 py-4">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="brand-title text-xs text-red-400">HONDA BABEL SALES PERFORMANCE</p>
                    <h1 class="brand-title text-2xl font-bold text-white">WEB LEADERBOARD</h1>
                </div>

                <nav class="flex flex-wrap items-center gap-2 text-sm">
                    <a class="rounded-lg border border-white/20 px-3 py-1.5 hover:border-red-500 hover:text-red-300" href="{{ route('leaderboard.index') }}">Leaderboard</a>
                    <a class="rounded-lg border border-white/20 px-3 py-1.5 hover:border-red-500 hover:text-red-300" href="{{ route('leaderboard.tv') }}">TV Mode</a>
                    @auth
                        @if(auth()->user()->role === 'admin')
                            <a class="rounded-lg border border-white/20 px-3 py-1.5 hover:border-red-500 hover:text-red-300" href="{{ route('admin.dealers.index') }}">Admin</a>
                        @endif
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="rounded-lg border border-red-600/50 bg-red-600/20 px-3 py-1.5 text-red-100 hover:bg-red-600/35" type="submit">Logout</button>
                        </form>
                    @else
                        <a class="rounded-lg border border-red-600/50 bg-red-600/20 px-3 py-1.5 text-red-100 hover:bg-red-600/35" href="{{ route('login') }}">Admin Login</a>
                    @endauth
                </nav>
            </div>
        </header>

        @if(session('status'))
            <div class="mb-4 rounded-xl border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 rounded-xl border border-red-500/50 bg-red-500/10 px-4 py-3 text-sm text-red-200">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </div>

    @stack('scripts')
</body>
</html>
