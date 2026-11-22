@extends('layouts.app')

@section('title', 'Riwayat Transaksi - ' . config('app.name', 'FinanceKu'))

@section('content')
    <div
        x-data="{
            showFilters: {{ $hasActiveFilters ? 'true' : 'false' }},
            deleteTarget: null,
            confirmDelete(target) {
                this.deleteTarget = target;
            },
            closeDelete() {
                this.deleteTarget = null;
            }
        }"
        @keydown.escape.window="if (deleteTarget) closeDelete()"
        class="space-y-4"
    >
        <!-- Header & Filter Toggle -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900">Riwayat Transaksi</h1>
                <p class="text-xs text-slate-500">{{ $totalCount }} transaksi ditemukan</p>
            </div>
            <button
                type="button"
                @click="showFilters = !showFilters"
                class="inline-flex min-h-[44px] items-center gap-1.5 rounded-xl border px-3.5 py-2 text-xs font-semibold transition {{ $hasActiveFilters ? 'border-emerald-300 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                </svg>
                <span>Filter</span>
                @if ($hasActiveFilters)
                    <span class="h-2 w-2 rounded-full bg-emerald-600"></span>
                @endif
            </button>
        </div>

        <!-- Panel Filter Sederhana -->
        <div
            x-show="showFilters"
            x-transition
            x-cloak
            class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
        >
            <form method="GET" action="{{ route('transactions.index') }}" class="space-y-3.5">
                <!-- Filter Tipe -->
                <div>
                    <span class="mb-1.5 block text-xs font-semibold text-slate-600">Tipe Transaksi</span>
                    <div class="grid grid-cols-3 gap-1.5 rounded-xl bg-slate-100 p-1">
                        @foreach (['all' => 'Semua', 'expense' => 'Keluar', 'income' => 'Masuk'] as $value => $label)
                            <label class="cursor-pointer">
                                <input
                                    type="radio"
                                    name="type"
                                    value="{{ $value }}"
                                    class="peer sr-only"
                                    @checked($filters['type'] === $value)
                                >
                                <span class="flex min-h-[38px] items-center justify-center rounded-lg text-xs font-semibold text-slate-600 transition peer-checked:bg-white peer-checked:text-slate-900 peer-checked:shadow-sm">
                                    {{ $label }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Filter Kategori -->
                <div>
                    <label for="filter-category" class="mb-1 block text-xs font-semibold text-slate-600">Kategori</label>
                    <select
                        id="filter-category"
                        name="category_id"
                        class="block min-h-[44px] w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none"
                    >
                        <option value="">Semua Kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected($filters['category_id'] === $category->id)>
                                {{ $category->name }} ({{ $category->type === 'expense' ? 'Keluar' : 'Masuk' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Rentang Tanggal -->
                <div class="grid grid-cols-2 gap-2.5">
                    <div>
                        <label for="filter-start-date" class="mb-1 block text-xs font-semibold text-slate-600">Dari Tanggal</label>
                        <input
                            id="filter-start-date"
                            name="start_date"
                            type="date"
                            value="{{ $filters['start_date'] }}"
                            class="block min-h-[44px] w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none"
                        >
                    </div>
                    <div>
                        <label for="filter-end-date" class="mb-1 block text-xs font-semibold text-slate-600">Sampai Tanggal</label>
                        <input
                            id="filter-end-date"
                            name="end_date"
                            type="date"
                            value="{{ $filters['end_date'] }}"
                            class="block min-h-[44px] w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none"
                        >
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <button
                        type="submit"
                        class="flex min-h-[44px] flex-1 items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700"
                    >
                        Terapkan Filter
                    </button>
                    @if ($hasActiveFilters)
                        <a
                            href="{{ route('transactions.index') }}"
                            class="flex min-h-[44px] items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50"
                        >
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Daftar Transaksi Dikelompokkan Per Hari -->
        @forelse ($groupedTransactions as $group)
            <section class="space-y-2">
                <!-- Header Tanggal & Subtotal Harian -->
                <div class="flex items-center justify-between gap-2 px-1">
                    <h2 class="text-sm font-bold text-slate-800">{{ $group['label'] }}</h2>
                    <div class="flex flex-wrap items-center justify-end gap-2 text-xs font-semibold">
                        @if ($group['total_income'] > 0)
                            <span class="rounded-lg bg-emerald-50 px-2 py-0.5 text-emerald-700">
                                +Rp {{ number_format($group['total_income'], 0, ',', '.') }}
                            </span>
                        @endif
                        @if ($group['total_expense'] > 0)
                            <span class="rounded-lg bg-rose-50 px-2 py-0.5 text-rose-700">
                                -Rp {{ number_format($group['total_expense'], 0, ',', '.') }}
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Item Transaksi dalam Hari Tersebut -->
                <div class="divide-y divide-slate-100 rounded-2xl border border-slate-200 bg-white shadow-sm">
                    @foreach ($group['transactions'] as $transaction)
                        @php
                            $isIncome = $transaction->type === 'income';
                            $formattedNominal = number_format((float) $transaction->amount, 0, ',', '.');
                            $editPayload = [
                                'id' => $transaction->id,
                                'type' => $transaction->type,
                                'category_id' => $transaction->category_id,
                                'amount' => (float) $transaction->amount,
                                'note' => $transaction->note,
                                'transaction_date' => $transaction->transaction_date->format('Y-m-d\TH:i'),
                                'update_url' => route('transactions.update', $transaction),
                            ];
                            $deletePayload = [
                                'label' => $transaction->category->name . ($transaction->note ? ' - ' . $transaction->note : ''),
                                'amount' => ($isIncome ? '+' : '-') . 'Rp ' . $formattedNominal,
                                'url' => route('transactions.destroy', $transaction),
                            ];
                        @endphp
                        <div class="p-3.5">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex min-w-0 items-center gap-3">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $transaction->category->color }}">
                                        <x-category-icon :name="$transaction->category->icon" class="h-5 w-5" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-slate-900">
                                            {{ $transaction->category->name }}
                                        </p>
                                        <p class="truncate text-xs text-slate-500">
                                            {{ $transaction->transaction_date->format('H:i') }}
                                            @if ($transaction->note)
                                                &middot; {{ $transaction->note }}
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <div class="shrink-0 text-right">
                                    <p class="text-sm font-bold {{ $isIncome ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $isIncome ? '+' : '-' }}Rp {{ $formattedNominal }}
                                    </p>
                                </div>
                            </div>

                            <!-- Aksi Edit & Hapus -->
                            <div class="mt-2.5 flex items-center justify-end gap-2 border-t border-slate-100 pt-2">
                                <button
                                    type="button"
                                    @click="$dispatch('open-edit-transaction', {{ Js::from($editPayload) }})"
                                    class="inline-flex min-h-[36px] items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                                    </svg>
                                    <span>Edit</span>
                                </button>

                                <button
                                    type="button"
                                    @click="confirmDelete({{ Js::from($deletePayload) }})"
                                    class="inline-flex min-h-[36px] items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-semibold text-rose-600 transition hover:bg-rose-50"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                    <span>Hapus</span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center">
                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" />
                    </svg>
                </div>
                <p class="text-sm font-semibold text-slate-800">
                    {{ $hasActiveFilters ? 'Tidak ada transaksi sesuai filter' : 'Belum ada transaksi tercatat' }}
                </p>
                <p class="mt-1 text-xs text-slate-500">
                    {{ $hasActiveFilters ? 'Coba ubah atau reset filter untuk melihat catatan lainnya.' : 'Ketuk tombol + di bawah untuk mulai mencatat pemasukan atau pengeluaran.' }}
                </p>
                <div class="mt-4 flex justify-center gap-2">
                    @if ($hasActiveFilters)
                        <a
                            href="{{ route('transactions.index') }}"
                            class="inline-flex min-h-[44px] items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                        >
                            Reset Filter
                        </a>
                    @else
                        <button
                            type="button"
                            @click="$dispatch('open-quick-add')"
                            class="inline-flex min-h-[44px] items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700"
                        >
                            Catat Transaksi Pertama
                        </button>
                    @endif
                </div>
            </div>
        @endforelse

        <!-- Modal Konfirmasi Hapus -->
        <div
            x-show="deleteTarget !== null"
            x-cloak
            class="fixed inset-0 z-50 flex items-end justify-center sm:items-center"
            role="dialog"
            aria-modal="true"
            aria-labelledby="delete-confirm-title"
        >
            <div
                x-show="deleteTarget !== null"
                x-transition.opacity
                @click="closeDelete()"
                class="fixed inset-0 bg-slate-900/50 backdrop-blur-[1px]"
            ></div>

            <div
                x-show="deleteTarget !== null"
                x-transition
                class="relative z-10 w-full max-w-md rounded-t-3xl bg-white p-5 shadow-2xl sm:rounded-3xl"
            >
                <h2 id="delete-confirm-title" class="text-base font-bold text-slate-900">Hapus Transaksi?</h2>
                <p class="mt-1 text-sm text-slate-600">
                    Transaksi <span class="font-semibold text-slate-900" x-text="deleteTarget?.label"></span>
                    (<span class="font-semibold text-slate-900" x-text="deleteTarget?.amount"></span>) akan dihapus permanen.
                </p>

                <div class="mt-5 flex gap-2.5">
                    <button
                        type="button"
                        @click="closeDelete()"
                        class="flex min-h-[44px] flex-1 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                    >
                        Batal
                    </button>
                    <form method="POST" :action="deleteTarget?.url" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button
                            type="submit"
                            class="flex min-h-[44px] w-full items-center justify-center rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-700"
                        >
                            Ya, Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
