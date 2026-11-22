@extends('layouts.app')

@section('title', 'Edit Transaksi - ' . config('app.name', 'FinanceKu'))

@php
    $initialDigits = preg_replace('/\D/', '', (string) old('amount', (int) $transaction->amount));
    $categoriesById = $categories->keyBy('id')->map(fn ($category) => [
        'id' => $category->id,
        'type' => $category->type,
    ]);
@endphp

@section('content')
    <div
        x-data="{
            type: '{{ old('type', $transaction->type) }}',
            categoryId: {{ (int) old('category_id', $transaction->category_id) }},
            rawAmount: '{{ $initialDigits }}',
            formattedAmount: '',
            categoriesMap: @js($categoriesById),

            init() {
                this.formattedAmount = this.formatThousands(this.rawAmount);
            },

            formatThousands(digits) {
                return digits ? digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
            },

            onAmountInput(value) {
                const digits = String(value).replace(/\D/g, '').replace(/^0+/, '');
                this.rawAmount = digits;
                this.formattedAmount = this.formatThousands(digits);
            },

            setType(newType) {
                if (this.type === newType) {
                    return;
                }
                this.type = newType;
                if (this.categoryId && this.categoriesMap[this.categoryId]?.type !== newType) {
                    this.categoryId = null;
                }
            }
        }"
        class="space-y-4"
    >
        <div class="flex items-center gap-3">
            <a
                href="{{ route('transactions.index') }}"
                aria-label="Kembali ke riwayat"
                class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
            </a>
            <h1 class="text-xl font-bold text-slate-900">Edit Transaksi</h1>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            @if ($errors->any())
                <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2.5 text-xs font-medium text-rose-700" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('transactions.update', $transaction) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="redirect_to" value="index">
                <input type="hidden" name="type" :value="type">
                <input type="hidden" name="category_id" :value="categoryId ?? ''">
                <input type="hidden" name="amount" :value="rawAmount">

                <div class="grid grid-cols-2 gap-2 rounded-2xl bg-slate-100 p-1">
                    <button
                        type="button"
                        @click="setType('expense')"
                        :class="type === 'expense' ? 'bg-white text-rose-600 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                        class="min-h-[44px] rounded-xl text-sm font-semibold transition"
                    >
                        Pengeluaran
                    </button>
                    <button
                        type="button"
                        @click="setType('income')"
                        :class="type === 'income' ? 'bg-white text-emerald-600 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                        class="min-h-[44px] rounded-xl text-sm font-semibold transition"
                    >
                        Pemasukan
                    </button>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 focus-within:border-emerald-500 focus-within:bg-white focus-within:ring-2 focus-within:ring-emerald-500/20">
                    <label for="edit-amount" class="block text-xs font-medium text-slate-500">Nominal</label>
                    <div class="mt-1 flex items-baseline gap-2">
                        <span class="text-xl font-bold text-slate-400">Rp</span>
                        <input
                            id="edit-amount"
                            type="text"
                            inputmode="numeric"
                            autocomplete="off"
                            :value="formattedAmount"
                            @input="onAmountInput($event.target.value); $event.target.value = formattedAmount"
                            placeholder="0"
                            class="w-full bg-transparent text-3xl font-bold tracking-tight text-slate-900 placeholder:text-slate-300 focus:outline-none"
                        >
                    </div>
                </div>

                <div>
                    <span class="mb-2 block text-xs font-semibold tracking-wide text-slate-500 uppercase">Kategori</span>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach ($categories as $category)
                            <button
                                type="button"
                                x-show="type === '{{ $category->type }}'"
                                @click="categoryId = {{ $category->id }}"
                                :class="categoryId === {{ $category->id }}
                                    ? 'border-emerald-500 bg-emerald-50/70 ring-2 ring-emerald-500/30'
                                    : 'border-slate-200 bg-white hover:border-slate-300'"
                                class="flex min-h-[68px] flex-col items-center justify-center gap-1.5 rounded-2xl border px-2 py-2.5 text-center transition"
                            >
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl {{ $category->color }}">
                                    <x-category-icon :name="$category->icon" class="h-5 w-5" />
                                </span>
                                <span class="line-clamp-1 text-xs font-medium text-slate-700">{{ $category->name }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label for="edit-note" class="block text-xs font-medium text-slate-600">Catatan (opsional)</label>
                    <input
                        id="edit-note"
                        name="note"
                        type="text"
                        maxlength="255"
                        value="{{ old('note', $transaction->note) }}"
                        placeholder="Catatan singkat"
                        class="mt-1 block min-h-[44px] w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none"
                    >
                </div>

                <div>
                    <label for="edit-date" class="block text-xs font-medium text-slate-600">Waktu Transaksi</label>
                    <input
                        id="edit-date"
                        name="transaction_date"
                        type="datetime-local"
                        required
                        value="{{ old('transaction_date', $transaction->transaction_date->format('Y-m-d\TH:i')) }}"
                        class="mt-1 block min-h-[44px] w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none"
                    >
                </div>

                <button
                    type="submit"
                    class="flex min-h-[48px] w-full items-center justify-center rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700"
                >
                    Simpan Perubahan
                </button>
            </form>
        </div>
    </div>
@endsection
