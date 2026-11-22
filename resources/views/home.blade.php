@extends('layouts.app')

@section('title', 'Beranda - ' . config('app.name', 'FinanceKu'))

@section('content')
    <div class="space-y-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold tracking-wider text-emerald-600 uppercase">FinanceKu</p>
                    <h1 class="mt-1 text-xl font-bold text-slate-900">Halo, {{ auth()->user()->name }}</h1>
                    <p class="mt-0.5 text-sm text-slate-500">{{ auth()->user()->email }}</p>
                </div>
                <div class="shrink-0 rounded-xl bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700">
                    Streak: {{ auth()->user()->current_streak }} hari
                </div>
            </div>

            <div class="mt-5 rounded-xl border border-slate-100 bg-slate-50 p-4 text-sm text-slate-600">
                Ketuk tombol <span class="font-semibold text-emerald-600">+</span> di navigasi bawah untuk mencatat transaksi dengan cepat, atau buka halaman riwayat untuk melihat dan memfilter catatan Anda.
            </div>

            <div class="mt-5 grid grid-cols-2 gap-3">
                <button
                    type="button"
                    @click="$dispatch('open-quick-add')"
                    class="flex min-h-[44px] items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700"
                >
                    Catat Cepat
                </button>
                <a
                    href="{{ route('transactions.index') }}"
                    class="flex min-h-[44px] items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                >
                    Lihat Riwayat
                </a>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    type="submit"
                    class="flex min-h-[44px] w-full items-center justify-center rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
                >
                    Keluar dari Akun
                </button>
            </form>
        </div>
    </div>
@endsection
