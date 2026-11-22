@extends('layouts.app')

@section('title', config('app.name', 'FinanceKu'))

@section('content')
    <div class="flex min-h-screen flex-col justify-between px-5 py-8">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold tracking-wider text-emerald-600 uppercase">FinanceKu</p>
                    <h1 class="mt-1 text-xl font-bold text-slate-900">Halo, {{ auth()->user()->name }}</h1>
                    <p class="mt-0.5 text-sm text-slate-500">{{ auth()->user()->email }}</p>
                </div>
                <div class="rounded-xl bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700">
                    Streak: {{ auth()->user()->current_streak }} hari
                </div>
            </div>

            <div class="mt-6 rounded-xl border border-slate-100 bg-slate-50 p-4 text-sm text-slate-600">
                Autentikasi aktif. Fondasi database, model, dan seeder sudah siap untuk tahap pengembangan berikutnya.
            </div>

            <form method="POST" action="{{ route('logout') }}" class="mt-6">
                @csrf
                <button
                    type="submit"
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                >
                    Keluar
                </button>
            </form>
        </div>
    </div>
@endsection
