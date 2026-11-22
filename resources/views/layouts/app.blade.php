<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'FinanceKu'))</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
        <div x-data class="mx-auto min-h-screen w-full max-w-md pb-24">
            <x-toast />

            <main class="px-4 pt-5">
                @yield('content')
            </main>

            <!-- Thumb-Friendly Bottom Navigation + Floating Action Button -->
            <nav
                aria-label="Navigasi utama"
                class="fixed inset-x-0 bottom-0 z-30 mx-auto max-w-md border-t border-slate-200/80 bg-white/95 px-6 pt-1.5 pb-2 shadow-[0_-4px_20px_rgba(15,23,42,0.06)] backdrop-blur"
            >
                <div class="grid grid-cols-3 items-center">
                    <a
                        href="{{ route('home') }}"
                        class="flex min-h-[48px] flex-col items-center justify-center gap-0.5 rounded-xl text-xs font-medium transition {{ request()->routeIs('home') ? 'text-emerald-600' : 'text-slate-500 hover:text-slate-800' }}"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        <span>Beranda</span>
                    </a>

                    <div class="flex justify-center">
                        <button
                            type="button"
                            @click="$dispatch('open-quick-add')"
                            aria-label="Catat transaksi baru"
                            class="-mt-7 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-600 text-white shadow-lg shadow-emerald-600/30 ring-4 ring-slate-50 transition hover:bg-emerald-700 active:scale-95"
                        >
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="2.4" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                        </button>
                    </div>

                    <a
                        href="{{ route('transactions.index') }}"
                        class="flex min-h-[48px] flex-col items-center justify-center gap-0.5 rounded-xl text-xs font-medium transition {{ request()->routeIs('transactions.*') ? 'text-emerald-600' : 'text-slate-500 hover:text-slate-800' }}"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" />
                        </svg>
                        <span>Riwayat</span>
                    </a>
                </div>
            </nav>

            <!-- Global Quick Add & Edit Bottom Sheet -->
            <x-transaction-sheet :categories="$quickAddCategories" />
        </div>
    </body>
</html>
