@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-md">
        <div class="card-glass p-6">
            <h2 class="brand-title mb-1 text-2xl font-bold">Admin Login</h2>
            <p class="mb-6 text-sm text-white/70">Masuk untuk mengelola data dealer, sales, SPK, dan DO.</p>

            <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="mb-1 block text-sm text-white/80">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-lg border border-white/20 bg-black/30 px-3 py-2 text-white focus:border-red-500 focus:outline-none">
                </div>

                <div>
                    <label class="mb-1 block text-sm text-white/80">Password</label>
                    <input type="password" name="password" required class="w-full rounded-lg border border-white/20 bg-black/30 px-3 py-2 text-white focus:border-red-500 focus:outline-none">
                </div>

                <label class="flex items-center gap-2 text-sm text-white/70">
                    <input type="checkbox" name="remember" value="1" class="rounded border-white/30 bg-transparent text-red-600 focus:ring-red-500">
                    Remember me
                </label>

                <button type="submit" class="w-full rounded-lg bg-red-600 px-4 py-2 font-semibold text-white hover:bg-red-500">
                    Login
                </button>
            </form>
        </div>
    </div>
@endsection
