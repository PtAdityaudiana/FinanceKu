@extends('layouts.app')

@section('title', 'Dashboard - ' . config('app.name', 'FinanceKu'))

@php
    $formattedNetBalance = ($netBalance < 0 ? '-' : '') . 'Rp ' . number_format(abs($netBalance), 0, ',', '.');

    $statusBadgeClasses = match ($comparison['expense_status']) {
        'saving' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'overspending' => 'border-rose-200 bg-rose-50 text-rose-700',
        default => 'border-slate-200 bg-slate-100 text-slate-600',
    };

    $months = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
        7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
    ];
@endphp

@section('content')
    <div class="space-y-4">
        <!-- Header Sapaan & Badge Streak -->
        <header class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-xs font-semibold tracking-wider text-emerald-600 uppercase">FinanceKu</p>
                <h1 class="truncate text-xl font-bold tracking-tight text-slate-900">Halo, {{ $user->name }}</h1>
            </div>

            <div class="flex shrink-0 items-center gap-2">
                <div
                    title="Streak pencatatan harian"
                    class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-bold {{ $visibleStreak > 0 ? 'border-amber-200 bg-amber-50 text-amber-700' : 'border-slate-200 bg-white text-slate-500' }}"
                >
                    <span aria-hidden="true">🔥</span>
                    <span>{{ $visibleStreak }} Hari</span>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        title="Keluar"
                        aria-label="Keluar dari akun"
                        class="flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-100 hover:text-slate-800"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                        </svg>
                    </button>
                </form>
            </div>
        </header>

        <!-- Kartu Utama Sisa Uang -->
        <section class="rounded-3xl bg-gradient-to-br from-emerald-600 to-teal-700 p-5 text-white shadow-lg shadow-emerald-600/20">
            <p class="text-xs font-medium text-emerald-100">Total Sisa Uang</p>
            <p class="mt-1 text-3xl font-extrabold tracking-tight">{{ $formattedNetBalance }}</p>

            <div class="mt-5 grid grid-cols-2 gap-3 border-t border-white/15 pt-3.5">
                <div>
                    <p class="text-[11px] font-medium text-emerald-100">Masuk Hari Ini</p>
                    <p class="mt-0.5 text-sm font-bold text-white">
                        +Rp {{ number_format($comparison['today_income'], 0, ',', '.') }}
                    </p>
                </div>
                <div class="border-l border-white/15 pl-3">
                    <p class="text-[11px] font-medium text-emerald-100">Keluar Hari Ini</p>
                    <p class="mt-0.5 text-sm font-bold text-white">
                        -Rp {{ number_format($comparison['today_expense'], 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </section>

        <!-- Kartu Komparasi Hari Ini vs Kemarin -->
        <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between gap-2">
                <h2 class="text-sm font-bold text-slate-900">Hari Ini vs Kemarin</h2>
                <span class="rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $statusBadgeClasses }}">
                    {{ $comparison['expense_status_text'] }}
                </span>
            </div>

            <div class="mt-3.5 grid grid-cols-2 gap-3">
                <div class="rounded-xl bg-slate-50 p-3">
                    <p class="text-[11px] font-medium text-slate-500">Pengeluaran Hari Ini</p>
                    <p class="mt-1 text-base font-bold {{ $comparison['expense_status'] === 'overspending' ? 'text-rose-600' : ($comparison['expense_status'] === 'saving' ? 'text-emerald-600' : 'text-slate-900') }}">
                        Rp {{ number_format($comparison['today_expense'], 0, ',', '.') }}
                    </p>
                </div>
                <div class="rounded-xl bg-slate-50 p-3">
                    <p class="text-[11px] font-medium text-slate-500">Pengeluaran Kemarin</p>
                    <p class="mt-1 text-base font-bold text-slate-700">
                        Rp {{ number_format($comparison['yesterday_expense'], 0, ',', '.') }}
                    </p>
                </div>
            </div>

            <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-2.5 text-xs text-slate-500">
                <span>Pemasukan Hari Ini: <strong class="font-semibold text-emerald-600">Rp {{ number_format($comparison['today_income'], 0, ',', '.') }}</strong></span>
                <span>Kemarin: <strong class="font-semibold text-slate-700">Rp {{ number_format($comparison['yesterday_income'], 0, ',', '.') }}</strong></span>
            </div>
        </section>

        <!-- Daftar Aktivitas Terakhir (Max 5 Transaksi) -->
        <section class="space-y-2.5">
            <div class="flex items-center justify-between px-1">
                <h2 class="text-sm font-bold text-slate-900">Aktivitas Terakhir</h2>
                <a
                    href="{{ route('transactions.index') }}"
                    class="text-xs font-semibold text-emerald-600 transition hover:text-emerald-700"
                >
                    Lihat Semua
                </a>
            </div>

            @if ($recentTransactions->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-6 text-center">
                    <p class="text-sm font-semibold text-slate-800">Belum ada aktivitas transaksi</p>
                    <p class="mt-1 text-xs text-slate-500">Mulai catat pemasukan atau pengeluaran pertamamu hari ini.</p>
                    <button
                        type="button"
                        @click="$dispatch('open-quick-add')"
                        class="mt-4 inline-flex min-h-[44px] items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700"
                    >
                        Catat Transaksi
                    </button>
                </div>
            @else
                <div class="divide-y divide-slate-100 rounded-2xl border border-slate-200 bg-white shadow-sm">
                    @foreach ($recentTransactions as $transaction)
                        @php
                            $isIncome = $transaction->type === 'income';
                            $txDate = $transaction->transaction_date;
                            $dateText = $txDate->isToday()
                                ? 'Hari Ini'
                                : ($txDate->isYesterday()
                                    ? 'Kemarin'
                                    : $txDate->day . ' ' . ($months[$txDate->month] ?? $txDate->format('M')));
                        @endphp
                        <div class="flex items-center justify-between gap-3 p-3.5">
                            <div class="flex min-w-0 items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $transaction->category->color }}">
                                    <x-category-icon :name="$transaction->category->icon" class="h-5 w-5" />
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-900">
                                        {{ $transaction->category->name }}
                                    </p>
                                    <p class="truncate text-xs text-slate-500">
                                        {{ $dateText }} &middot; {{ $txDate->format('H:i') }}
                                        @if ($transaction->note)
                                            &middot; {{ $transaction->note }}
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <p class="shrink-0 text-sm font-bold {{ $isIncome ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $isIncome ? '+' : '-' }}Rp {{ number_format((float) $transaction->amount, 0, ',', '.') }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
