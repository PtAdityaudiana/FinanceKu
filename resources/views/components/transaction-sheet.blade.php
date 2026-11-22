@props([
    'categories',
])

@php
    $hasTransactionErrors = $errors->hasAny(['type', 'category_id', 'amount', 'note', 'transaction_date'])
        && ! request()->routeIs('transactions.edit');
    $oldAmountDigits = preg_replace('/\D/', '', (string) old('amount', ''));
    $categoriesById = $categories->keyBy('id')->map(fn ($category) => [
        'id' => $category->id,
        'type' => $category->type,
    ]);
@endphp

<div
    x-data="{
        open: {{ $hasTransactionErrors ? 'true' : 'false' }},
        mode: 'create',
        actionUrl: '{{ route('transactions.store') }}',
        type: '{{ old('type', 'expense') }}',
        categoryId: {{ old('category_id') ? (int) old('category_id') : 'null' }},
        rawAmount: '{{ $oldAmountDigits }}',
        formattedAmount: '',
        note: @js(old('note', '')),
        transactionDate: @js(old('transaction_date', now()->format('Y-m-d\TH:i'))),
        clientError: '',
        submitting: false,
        categoriesMap: @js($categoriesById),

        init() {
            if (this.rawAmount) {
                this.formattedAmount = this.formatThousands(this.rawAmount);
            }
        },

        currentLocalDateTime() {
            const now = new Date();
            const pad = (n) => String(n).padStart(2, '0');
            return `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;
        },

        formatThousands(digits) {
            return digits ? digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
        },

        onAmountInput(value) {
            const digits = String(value).replace(/\D/g, '').replace(/^0+/, '');
            this.rawAmount = digits;
            this.formattedAmount = this.formatThousands(digits);
            this.clientError = '';
        },

        setType(newType) {
            if (this.type === newType) {
                return;
            }
            this.type = newType;
            if (this.categoryId && this.categoriesMap[this.categoryId]?.type !== newType) {
                this.categoryId = null;
            }
            this.clientError = '';
        },

        selectCategory(id) {
            this.categoryId = id;
            this.clientError = '';
        },

        openCreate() {
            this.mode = 'create';
            this.actionUrl = '{{ route('transactions.store') }}';
            this.type = 'expense';
            this.categoryId = null;
            this.rawAmount = '';
            this.formattedAmount = '';
            this.note = '';
            this.transactionDate = this.currentLocalDateTime();
            this.clientError = '';
            this.submitting = false;
            this.open = true;
            this.$nextTick(() => this.$refs.amountInput?.focus());
        },

        openEdit(detail) {
            this.mode = 'edit';
            this.actionUrl = detail.update_url;
            this.type = detail.type;
            this.categoryId = Number(detail.category_id);
            const digits = String(Math.trunc(Number(detail.amount || 0))).replace(/^0+/, '');
            this.rawAmount = digits;
            this.formattedAmount = this.formatThousands(digits);
            this.note = detail.note || '';
            this.transactionDate = detail.transaction_date || this.currentLocalDateTime();
            this.clientError = '';
            this.submitting = false;
            this.open = true;
        },

        close() {
            this.open = false;
            this.clientError = '';
        },

        handleSubmit(event) {
            if (!this.rawAmount || Number(this.rawAmount) <= 0) {
                event.preventDefault();
                this.clientError = 'Masukkan nominal transaksi lebih dari 0.';
                this.$refs.amountInput?.focus();
                return;
            }
            if (!this.categoryId) {
                event.preventDefault();
                this.clientError = 'Pilih salah satu kategori terlebih dahulu.';
                return;
            }
            this.submitting = true;
        }
    }"
    @open-quick-add.window="openCreate()"
    @open-edit-transaction.window="openEdit($event.detail)"
    @keydown.escape.window="if (open) close()"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 flex items-end justify-center"
    aria-modal="true"
    role="dialog"
    aria-labelledby="transaction-sheet-title"
>
    <!-- Backdrop -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="close()"
        class="fixed inset-0 bg-slate-900/50 backdrop-blur-[1px]"
    ></div>

    <!-- Bottom Sheet Panel -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-250 transform"
        x-transition:enter-start="translate-y-full"
        x-transition:enter-end="translate-y-0"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="translate-y-0"
        x-transition:leave-end="translate-y-full"
        class="relative z-10 max-h-[92vh] w-full max-w-md overflow-y-auto rounded-t-3xl bg-white px-5 pt-3 pb-7 shadow-2xl"
    >
        <!-- Handle Bar -->
        <div class="mx-auto mb-3 h-1.5 w-12 rounded-full bg-slate-200"></div>

        <!-- Header -->
        <div class="mb-4 flex items-center justify-between">
            <h2
                id="transaction-sheet-title"
                class="text-lg font-bold text-slate-900"
                x-text="mode === 'edit' ? 'Edit Transaksi' : 'Catat Transaksi'"
            >
                Catat Transaksi
            </h2>
            <button
                type="button"
                @click="close()"
                aria-label="Tutup"
                class="flex h-10 w-10 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        @if ($hasTransactionErrors)
            <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2.5 text-xs font-medium text-rose-700" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <template x-if="clientError">
            <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2.5 text-xs font-medium text-rose-700" role="alert" x-text="clientError"></div>
        </template>

        <form method="POST" :action="actionUrl" @submit="handleSubmit($event)" class="space-y-4">
            @csrf
            <template x-if="mode === 'edit'">
                <input type="hidden" name="_method" value="PUT">
            </template>
            <input type="hidden" name="type" :value="type">
            <input type="hidden" name="category_id" :value="categoryId ?? ''">
            <input type="hidden" name="amount" :value="rawAmount">

            <!-- Toggle Tipe Transaksi -->
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

            <!-- Input Nominal Besar -->
            <div class="rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 focus-within:border-emerald-500 focus-within:bg-white focus-within:ring-2 focus-within:ring-emerald-500/20">
                <label for="quick-add-amount" class="block text-xs font-medium text-slate-500">Nominal</label>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-xl font-bold text-slate-400">Rp</span>
                    <input
                        id="quick-add-amount"
                        x-ref="amountInput"
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

            <!-- Pilih Kategori (1-Klik) -->
            <div>
                <div class="mb-2 flex items-center justify-between">
                    <span class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Pilih Kategori</span>
                    <span class="text-xs text-slate-400" x-text="type === 'expense' ? 'Pengeluaran' : 'Pemasukan'"></span>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    @foreach ($categories as $category)
                        <button
                            type="button"
                            x-show="type === '{{ $category->type }}'"
                            @click="selectCategory({{ $category->id }})"
                            :class="categoryId === {{ $category->id }}
                                ? 'border-emerald-500 bg-emerald-50/70 ring-2 ring-emerald-500/30'
                                : 'border-slate-200 bg-white hover:border-slate-300'"
                            class="flex min-h-[68px] flex-col items-center justify-center gap-1.5 rounded-2xl border px-2 py-2.5 text-center transition active:scale-[0.98]"
                        >
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl {{ $category->color }}">
                                <x-category-icon :name="$category->icon" class="h-5 w-5" />
                            </span>
                            <span class="line-clamp-1 text-xs font-medium text-slate-700">{{ $category->name }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Catatan Singkat -->
            <div>
                <label for="quick-add-note" class="block text-xs font-medium text-slate-600">Catatan (opsional)</label>
                <input
                    id="quick-add-note"
                    name="note"
                    type="text"
                    maxlength="255"
                    x-model="note"
                    placeholder="Contoh: Kopi susu, bayar listrik..."
                    class="mt-1 block min-h-[44px] w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none"
                >
            </div>

            <!-- Waktu Transaksi -->
            <div>
                <div class="flex items-center justify-between">
                    <label for="quick-add-date" class="block text-xs font-medium text-slate-600">Waktu Transaksi</label>
                    <button
                        type="button"
                        @click="transactionDate = currentLocalDateTime()"
                        class="text-xs font-semibold text-emerald-600 hover:text-emerald-700"
                    >
                        Sekarang
                    </button>
                </div>
                <input
                    id="quick-add-date"
                    name="transaction_date"
                    type="datetime-local"
                    required
                    x-model="transactionDate"
                    class="mt-1 block min-h-[44px] w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none"
                >
            </div>

            <!-- Submit -->
            <button
                type="submit"
                :disabled="submitting"
                :class="type === 'expense' ? 'bg-rose-600 hover:bg-rose-700 focus:ring-rose-500' : 'bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-500'"
                class="flex min-h-[48px] w-full items-center justify-center rounded-2xl px-4 py-3 text-sm font-semibold text-white shadow-sm transition focus:ring-2 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
            >
                <span x-show="!submitting" x-text="mode === 'edit' ? 'Simpan Perubahan' : 'Simpan Transaksi'">Simpan Transaksi</span>
                <span x-show="submitting" x-cloak>Menyimpan...</span>
            </button>
        </form>
    </div>
</div>
