{{-- Logo resmi Bank Sulteng & Pill Identitas Sistem --}}
<div class="mb-5 flex flex-col items-center justify-center text-center">
    {{-- Logo Bank Sulteng: selalu bersih dan terlindungi kontrasnya --}}
    <div class="rounded-2xl bg-white p-3 shadow-sm ring-1 ring-slate-900/5 dark:bg-white dark:shadow-blue-900/20">
        <img src="{{ asset('images/logo-bank-sulteng.png') }}" alt="PT Bank Sulteng" class="h-14 sm:h-16 w-auto object-contain">
    </div>

    {{-- Live Status Indicator & Pill Kategori Sistem --}}
    <div class="mt-4 inline-flex items-center gap-2 rounded-full border border-blue-500/25 bg-blue-500/10 px-3.5 py-1 text-[11px] font-bold tracking-wider text-blue-700 uppercase dark:border-blue-400/30 dark:bg-blue-950/60 dark:text-blue-300">
        <span class="relative flex h-2 w-2">
            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
        </span>
        <span>Sistem Monitoring SLA ATM</span>
    </div>
</div>
