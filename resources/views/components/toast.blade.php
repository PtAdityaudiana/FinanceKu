@if (session('success') || session('error'))
    @php
        $isError = session()->has('error');
        $message = session('error') ?? session('success');
    @endphp
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 3500)"
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        x-cloak
        role="status"
        class="pointer-events-none fixed inset-x-0 top-4 z-50 mx-auto max-w-md px-4"
    >
        <div class="pointer-events-auto flex items-center justify-between gap-3 rounded-2xl border px-4 py-3 text-sm font-medium shadow-lg {{ $isError ? 'border-rose-200 bg-rose-600 text-white' : 'border-emerald-200 bg-emerald-600 text-white' }}">
            <div class="flex items-center gap-2.5">
                @if ($isError)
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>
                @else
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                @endif
                <span>{{ $message }}</span>
            </div>
            <button
                type="button"
                @click="show = false"
                aria-label="Tutup notifikasi"
                class="rounded-lg p-1 text-white/80 transition hover:bg-white/15 hover:text-white"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
@endif
