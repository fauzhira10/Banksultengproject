{{-- Ringkasan tiket di modal "Tutup Tiket": memastikan pengguna menutup tiket yang benar --}}
@php
    /** @var \App\Models\Tiket $tiket */
    $lokasi = $tiket->lokasi ?? $tiket->terminal?->nama_lokasi ?? '-';
    $atmId = $tiket->atm_id ?? $tiket->terminal?->luno ?? '-';
@endphp

<div class="overflow-hidden rounded-xl bg-gray-50 text-start ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
    <div class="flex items-center justify-between gap-3 border-b border-gray-200 px-4 py-2.5 dark:border-white/10">
        <span class="font-mono text-sm font-semibold text-blue-700 dark:text-blue-400">{{ $tiket->nomor_tiket }}</span>

        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800 ring-1 ring-amber-600/20 ring-inset dark:bg-amber-500/15 dark:text-amber-300 dark:ring-amber-400/25">
            <span class="size-1.5 rounded-full bg-amber-500"></span>
            Open
        </span>
    </div>

    <dl class="grid gap-3 px-4 py-3 text-sm">
        <div>
            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Permasalahan</dt>
            <dd class="mt-0.5 font-semibold text-gray-950 dark:text-white">{{ $tiket->permasalahan }}</dd>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Lokasi</dt>
                <dd class="mt-0.5 text-gray-800 dark:text-gray-200">{{ $lokasi }}</dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">ID / LUNO</dt>
                <dd class="mt-0.5 font-mono text-gray-800 dark:text-gray-200">{{ $atmId }}</dd>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Waktu Open</dt>
                <dd class="mt-0.5 text-gray-800 dark:text-gray-200">{{ $tiket->mulai?->format('d/m/Y H:i') ?? '-' }}</dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Sudah Berjalan</dt>
                <dd class="mt-0.5 font-semibold text-amber-700 dark:text-amber-400">{{ $runningDuration }}</dd>
            </div>
        </div>
    </dl>
</div>
