@extends('layouts.app')

@section('title', 'Masuk - ' . config('app.name', 'FinanceKu'))

@section('content')
    <div class="flex min-h-screen flex-col justify-center px-5 py-10">
        <div class="mb-8 text-center">
            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-sm">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 0 0-2.25-2.25H15a3 3 0 1 1-6 0H5.25A2.25 2.25 0 0 0 3 12m18 0v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 9m18 0V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v3" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">FinanceKu</h1>
            <p class="mt-1 text-sm text-slate-500">Masuk ke catatan keuangan pribadi Anda</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            @if ($errors->any())
                <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('login.store') }}"
                x-data="{ showPassword: false, submitting: false }"
                @submit="submitting = true"
                class="space-y-5"
            >
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="email"
                        placeholder="admin@financeku.test"
                        class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none"
                    >
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                    <div class="relative mt-1.5">
                        <input
                            id="password"
                            name="password"
                            :type="showPassword ? 'text' : 'password'"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="block w-full rounded-xl border border-slate-300 bg-white py-2.5 pr-16 pl-3.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none"
                        >
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 flex items-center px-3.5 text-xs font-medium text-slate-500 hover:text-slate-700"
                            x-text="showPassword ? 'Sembunyi' : 'Lihat'"
                        >
                            Lihat
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <label for="remember" class="inline-flex cursor-pointer items-center gap-2.5 text-sm text-slate-600">
                        <input type="hidden" name="remember" value="0">
                        <input
                            id="remember"
                            name="remember"
                            type="checkbox"
                            value="1"
                            checked
                            class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                        >
                        <span>Tetap masuk di perangkat ini</span>
                    </label>
                </div>

                <button
                    type="submit"
                    :disabled="submitting"
                    class="flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <span x-show="!submitting">Masuk</span>
                    <span x-show="submitting" x-cloak>Memproses...</span>
                </button>
            </form>
        </div>
    </div>
@endsection
