<x-filament-panels::page>
    @php
        $report = $this->reportData;
        $vendorName = strtoupper($this->selectedVendorName);
        $monthName = strtoupper($this->selectedMonthName);
        $year = $this->tahun;
        $totalMachines = $report['total_machines'];
        $cleanMachines = $report['clean_machines'];
        $problemMachines = $report['problem_machines'];
        $avgSla = $report['average_sla'];
        $isTargetMet = $avgSla >= 95.0;
    @endphp

    <style>
        @media print {
            @page {
                size: A4 landscape;
                margin: 12mm 10mm;
            }
            body {
                background: white !important;
                color: #000 !important;
                font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif !important;
                font-size: 9pt !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .fi-topbar, .fi-sidebar, .no-print, nav, header {
                display: none !important;
            }
            .print-only {
                display: block !important;
            }
            .print-container {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border: none !important;
                background: transparent !important;
            }
            table {
                width: 100% !important;
                font-size: 8.5pt !important;
                border-collapse: collapse !important;
            }
            th, td {
                padding: 4px 6px !important;
                border: 1px solid #4b5563 !important;
                color: #000 !important;
                background: transparent !important;
            }
            thead th {
                background-color: #f3f4f6 !important;
                font-weight: bold !important;
                text-align: center !important;
            }
            tfoot tr {
                background-color: #f3f4f6 !important;
                font-weight: bold !important;
            }
            .print-break-inside-avoid {
                break-inside: avoid !important;
                page-break-inside: avoid !important;
            }
        }
        @media screen {
            .print-only {
                display: none !important;
            }
        }
    </style>

    <!-- Header Dokumen Formal Khusus Saat Cetak (Print Only) -->
    <div class="print-only mb-6">
        <div class="flex items-center justify-between border-b-2 border-gray-900 pb-3">
            <div>
                <h1 class="text-lg font-bold uppercase tracking-wider text-black">PT. BANK PEMBANGUNAN DAERAH SULAWESI TENGAH</h1>
                <p class="text-xs font-semibold text-gray-700">DIVISI TEKNOLOGI INFORMASI - MONITORING ATM & CRM</p>
                <h2 class="mt-1 text-sm font-bold text-black">LAPORAN SERVICE LEVEL AGREEMENT (SLA) ATM & CRM</h2>
            </div>
            <div class="text-right text-xs">
                <div class="font-bold text-black">VENDOR: {{ $vendorName }}</div>
                <div>PERIODE: {{ $monthName }} {{ $year }}</div>
                <div class="text-[10px] text-gray-600">Dicetak: {{ now()->translatedFormat('d F Y H:i') }} WITA</div>
            </div>
        </div>
    </div>

    <!-- AREA LAYAR UTAMA (No Print) -->
    <div class="no-print space-y-5">
        <!-- 1. Header Banner & Identitas Laporan -->
        <div class="relative overflow-hidden rounded-2xl border border-gray-200/90 bg-white p-5 shadow-xs transition-colors dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700 ring-1 ring-blue-700/10 dark:bg-blue-950/50 dark:text-blue-300 dark:ring-blue-400/20 mb-2">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" />
                        </svg>
                        <span>Bank Sulteng • Monitoring Performa ATM & CRM</span>
                    </div>

                    <h1 class="text-xl font-bold tracking-tight text-gray-900 sm:text-2xl dark:text-white">
                        Laporan Service Level Agreement (SLA)
                    </h1>

                    <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                        <span class="inline-flex items-center gap-1.5 font-medium text-gray-900 dark:text-gray-200">
                            <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                            Vendor: <strong class="text-blue-600 dark:text-blue-400">{{ $vendorName }}</strong>
                        </span>
                        <span class="text-gray-300 dark:text-gray-700">•</span>
                        <span>Periode: <strong class="text-gray-900 dark:text-gray-200">{{ $monthName }} {{ $year }}</strong></span>
                        <span class="text-gray-300 dark:text-gray-700">•</span>
                        <span>Koefisien: <span class="font-mono">{{ number_format($this->koefisien, 0, ',', '.') }} Menit</span> (30 Hari)</span>
                    </div>
                </div>

                <!-- Tombol Aksi Cepat (Cetak & Ekspor) -->
                <div class="flex flex-wrap items-center gap-2.5">
                    <button
                        type="button"
                        onclick="window.print()"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-xs transition-all hover:bg-gray-50 hover:text-gray-900 focus:outline-hidden focus:ring-2 focus:ring-blue-500/20 active:bg-gray-100 cursor-pointer dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:hover:text-white"
                        title="Cetak atau simpan sebagai PDF"
                    >
                        <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.656l10.5 0Z" />
                        </svg>
                        <span>Cetak Laporan</span>
                    </button>

                    <button
                        type="button"
                        wire:click="exportCsv"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-xs transition-all hover:bg-emerald-500 focus:outline-hidden focus:ring-2 focus:ring-emerald-500/30 active:bg-emerald-700 cursor-pointer disabled:opacity-50"
                        title="Unduh data dalam format CSV/Excel"
                    >
                        <svg wire:loading.remove wire:target="exportCsv" class="h-4 w-4 text-emerald-100" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        <svg wire:loading wire:target="exportCsv" class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Export CSV / Excel</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- 2. Pilihan Vendor (Pill Navigation Modern) -->
        <div class="flex items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-1.5 rounded-2xl border border-gray-200/80 bg-gray-100/70 p-1.5 dark:border-gray-800 dark:bg-gray-900/60">
                <span class="px-2.5 text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Vendor:</span>

                <button
                    type="button"
                    wire:click="$set('vendor_id', '')"
                    class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-semibold transition-all duration-200 cursor-pointer {{ empty($vendor_id) ? 'bg-white text-blue-600 shadow-xs ring-1 ring-black/5 dark:bg-gray-800 dark:text-blue-400 dark:ring-white/10' : 'text-gray-600 hover:text-gray-900 hover:bg-white/60 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800/60' }}"
                >
                    <span>Semua Vendor</span>
                </button>

                @foreach ($this->vendors as $v)
                    <button
                        type="button"
                        wire:click="$set('vendor_id', '{{ $v->id }}')"
                        class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-semibold transition-all duration-200 cursor-pointer {{ $vendor_id == (string)$v->id ? 'bg-white text-blue-600 shadow-xs ring-1 ring-black/5 dark:bg-gray-800 dark:text-blue-400 dark:ring-white/10' : 'text-gray-600 hover:text-gray-900 hover:bg-white/60 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800/60' }}"
                    >
                        <span>{{ $v->nama_vendor }}</span>
                        <span class="rounded-md px-1.5 py-0.5 text-[10px] font-bold {{ $vendor_id == (string)$v->id ? 'bg-blue-100 text-blue-700 dark:bg-blue-950/70 dark:text-blue-300' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
                            {{ $v->terminals()->count() }}
                        </span>
                    </button>
                @endforeach
            </div>

            @if(!empty($search) || !empty($vendor_id))
                <button
                    type="button"
                    wire:click="resetFilters"
                    class="hidden sm:inline-flex items-center gap-1.5 rounded-xl border border-dashed border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-500 hover:border-gray-400 hover:text-gray-700 transition-colors cursor-pointer dark:border-gray-700 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:text-gray-200"
                    title="Reset semua filter ke kondisi awal"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    <span>Reset Filter</span>
                </button>
            @endif
        </div>

        <!-- 3. Filter Bar (Bulan, Tahun, & Pencarian) -->
        <div class="flex flex-col gap-3 rounded-2xl border border-gray-200/90 bg-white p-3.5 shadow-xs transition-colors sm:flex-row sm:items-center sm:justify-between dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-wrap items-center gap-3">
                <!-- Selector Bulan -->
                <div class="flex items-center gap-2">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Bulan:</span>
                    <div class="relative">
                        <select
                            wire:model.live="bulan"
                            class="appearance-none rounded-xl border border-gray-300 bg-gray-50/50 py-1.5 pl-3 pr-8 text-xs font-semibold text-gray-900 transition-colors focus:border-blue-500 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-blue-500/20 cursor-pointer dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-blue-400"
                        >
                            @foreach ($this->months as $key => $name)
                                <option value="{{ $key }}" class="dark:bg-gray-800 dark:text-gray-100">{{ $name }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-400">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Selector Tahun -->
                <div class="flex items-center gap-2">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Tahun:</span>
                    <div class="relative">
                        <select
                            wire:model.live="tahun"
                            class="appearance-none rounded-xl border border-gray-300 bg-gray-50/50 py-1.5 pl-3 pr-8 text-xs font-semibold text-gray-900 transition-colors focus:border-blue-500 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-blue-500/20 cursor-pointer dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-blue-400"
                        >
                            @foreach ($this->years as $y)
                                <option value="{{ $y }}" class="dark:bg-gray-800 dark:text-gray-100">{{ $y }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-400">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Input Pencarian -->
                <div class="relative min-w-[240px] flex-1 sm:w-72 sm:flex-initial">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </div>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Cari profil, cabang, lokasi, SN..."
                        class="w-full rounded-xl border border-gray-300 bg-gray-50/50 py-1.5 pl-9 pr-8 text-xs text-gray-900 placeholder:text-gray-400 transition-colors focus:border-blue-500 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder:text-gray-500 dark:focus:border-blue-400"
                    />
                    @if(!empty($search))
                        <button
                            type="button"
                            wire:click="clearSearch"
                            class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer"
                            title="Hapus pencarian"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    @endif
                </div>
            </div>

            <!-- Counter Info Data -->
            <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                <span>Ditemukan:</span>
                <span class="inline-flex items-center rounded-lg bg-gray-100 px-2 py-0.5 font-bold text-gray-900 dark:bg-gray-800 dark:text-gray-200">
                    {{ count($report['rows']) }} Mesin
                </span>
            </div>
        </div>

        <!-- 4. Kartu Metrik Ringkasan KPI Eksekutif -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Kartu 1: SLA Rata-rata -->
            <div class="relative overflow-hidden rounded-2xl border border-gray-200/90 bg-white p-5 shadow-xs transition-colors dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Rata-rata SLA Vendor</span>
                    <div class="rounded-xl p-2 {{ $isTargetMet ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400' : 'bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                        </svg>
                    </div>
                </div>

                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold tracking-tight {{ $isTargetMet ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                        {{ number_format($avgSla, 2) }}%
                    </span>
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-bold {{ $isTargetMet ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950/70 dark:text-rose-300' }}">
                        {{ $isTargetMet ? 'Target Tercapai' : 'Di Bawah Target' }}
                    </span>
                </div>

                <!-- Target Progress Bar -->
                <div class="mt-3">
                    <div class="flex justify-between text-[10px] text-gray-500 dark:text-gray-400 mb-1">
                        <span>Standar Minimum: ≥ 95.00%</span>
                        <span>{{ number_format($avgSla, 1) }}% / 100%</span>
                    </div>
                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                        <div class="h-full rounded-full transition-all duration-500 {{ $isTargetMet ? 'bg-emerald-500' : 'bg-rose-500' }}" style="width: {{ min(100, max(0, $avgSla)) }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Kartu 2: Total Mesin Terpantau -->
            <div class="relative overflow-hidden rounded-2xl border border-gray-200/90 bg-white p-5 shadow-xs transition-colors dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Mesin ATM / CRM</span>
                    <div class="rounded-xl bg-blue-50 p-2 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 0 1-3-3m3 3a3 3 0 1 0 0 6h13.5a3 3 0 1 0 0-6m-16.5-3a3 3 0 0 1 3-3h13.5a3 3 0 0 1 3 3m-19.5 0a4.5 4.5 0 0 1 .9-2.7L5.75 5.1a2.25 2.25 0 0 1 1.77-1.1h9.96a2.25 2.25 0 0 1 1.77 1.1l2.1 3.45a4.5 4.5 0 0 1 .9 2.7" />
                        </svg>
                    </div>
                </div>

                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">
                        {{ $totalMachines }}
                    </span>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Unit Terminal</span>
                </div>

                <div class="mt-4 flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                    <span class="inline-flex items-center gap-1 font-semibold text-emerald-600 dark:text-emerald-400">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        {{ $cleanMachines }} Normal
                    </span>
                    <span class="text-gray-300 dark:text-gray-700">•</span>
                    <span class="inline-flex items-center gap-1 font-semibold {{ $problemMachines > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-500' }}">
                        <span class="h-1.5 w-1.5 rounded-full {{ $problemMachines > 0 ? 'bg-rose-500' : 'bg-gray-400' }}"></span>
                        {{ $problemMachines }} Kendala
                    </span>
                </div>
            </div>

            <!-- Kartu 3: Total Down Time -->
            <div class="relative overflow-hidden rounded-2xl border border-gray-200/90 bg-white p-5 shadow-xs transition-colors dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Akumulasi Down Time</span>
                    <div class="rounded-xl bg-amber-50 p-2 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                </div>

                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold tracking-tight text-amber-600 dark:text-amber-400 font-mono">
                        {{ number_format($report['total_downtime'], 0, ',', '.') }}
                    </span>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Menit</span>
                </div>

                <div class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                    <span>Estimasi durasi: </span>
                    <strong class="font-semibold text-gray-700 dark:text-gray-300">
                        ~{{ number_format($report['total_downtime'] / 60, 1, ',', '.') }} Jam
                    </strong>
                    @if($report['total_downtime'] >= 1440)
                        <span class="text-gray-400 dark:text-gray-500">({{ round($report['total_downtime'] / 1440, 1) }} Hari)</span>
                    @endif
                </div>
            </div>

            <!-- Kartu 4: Kesehatan Operasional Jaringan -->
            <div class="relative overflow-hidden rounded-2xl border border-gray-200/90 bg-white p-5 shadow-xs transition-colors dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Kesehatan Jaringan ATM</span>
                    <div class="rounded-xl bg-teal-50 p-2 text-teal-600 dark:bg-teal-950/60 dark:text-teal-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                        </svg>
                    </div>
                </div>

                @php
                    $healthyPercent = $totalMachines > 0 ? round(($cleanMachines / $totalMachines) * 100, 1) : 100;
                @endphp

                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold tracking-tight text-teal-600 dark:text-teal-400">
                        {{ $healthyPercent }}%
                    </span>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Operasional 100%</span>
                </div>

                <div class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                    <span>Sebanyak </span>
                    <strong class="font-semibold text-gray-900 dark:text-white">{{ $cleanMachines }}</strong>
                    <span> dari </span>
                    <strong class="font-semibold text-gray-900 dark:text-white">{{ $totalMachines }}</strong>
                    <span> unit tanpa insiden</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. Lembar Tabel Data SLA Modern -->
    <div class="print-container mt-6 overflow-hidden rounded-2xl border border-gray-200/90 bg-white shadow-xs transition-colors dark:border-gray-800 dark:bg-gray-900">
        <!-- Subheader Tabel (Tampak Layar) -->
        <div class="no-print flex flex-col gap-1 border-b border-gray-200/90 bg-gray-50/75 px-6 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-gray-800 dark:bg-gray-800/50">
            <div>
                <h2 class="text-sm font-bold uppercase tracking-wider text-gray-900 dark:text-white">
                    Rincian Kinerja SLA ATM & CRM — {{ $vendorName }}
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Periode: {{ $monthName }} {{ $year }} • Koefisien Standar: {{ number_format($this->koefisien, 0, ',', '.') }} Menit (30 Hari)
                </p>
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400">
                <span>Target SLA: </span>
                <span class="font-semibold text-emerald-600 dark:text-emerald-400">≥ 95.00%</span>
            </div>
        </div>

        <!-- Tabel Data Interaktif -->
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left text-xs">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-100/70 text-[11px] font-bold uppercase tracking-wider text-gray-600 dark:border-gray-800 dark:bg-gray-800/80 dark:text-gray-300">
                        <th scope="col" class="w-12 px-3.5 py-3 text-center">No</th>
                        <th scope="col" class="min-w-[180px] px-4 py-3">Profil Terminal</th>
                        <th scope="col" class="min-w-[150px] px-4 py-3">Cabang / Unit Kerja</th>
                        <th scope="col" class="min-w-[200px] px-4 py-3">Lokasi & Tipe Mesin</th>
                        <th scope="col" class="w-32 px-3.5 py-3 text-center">
                            Down Time<br><span class="text-[9px] font-normal lowercase tracking-normal text-gray-500 dark:text-gray-400">(menit)</span>
                        </th>
                        <th scope="col" class="w-32 px-3.5 py-3 text-center">
                            Uptime<br><span class="text-[9px] font-normal lowercase tracking-normal text-gray-500 dark:text-gray-400">(menit)</span>
                        </th>
                        <th scope="col" class="w-28 px-3.5 py-3 text-center">
                            Koefisien<br><span class="text-[9px] font-normal lowercase tracking-normal text-gray-500 dark:text-gray-400">(menit)</span>
                        </th>
                        <th scope="col" class="w-32 px-4 py-3 text-center">
                            Uptime SLA<br><span class="text-[9px] font-normal lowercase tracking-normal text-gray-500 dark:text-gray-400">(persentase)</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60">
                    @forelse ($report['rows'] as $row)
                        @php
                            $hasProblem = $row['downtime_menit'] > 0;
                            $pct = $row['uptime_persen'];
                        @endphp
                        <tr class="transition-colors hover:bg-blue-50/40 dark:hover:bg-gray-800/50 {{ $hasProblem ? 'bg-rose-50/20 dark:bg-rose-950/10' : '' }}">
                            <!-- Kolom 1: No -->
                            <td class="px-3.5 py-3 text-center font-medium text-gray-500 dark:text-gray-400">
                                {{ $row['no'] }}
                            </td>

                            <!-- Kolom 2: Profil Terminal & Indikator Masalah -->
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-gray-900 dark:text-white">{{ $row['profil'] }}</span>
                                    @if ($row['tiket_count'] > 0)
                                        <span class="inline-flex items-center gap-1 rounded-md bg-rose-100 px-1.5 py-0.5 text-[10px] font-bold text-rose-700 dark:bg-rose-950/80 dark:text-rose-300" title="{{ $row['tiket_count'] }} tiket insiden bulan ini">
                                            <svg class="h-2.5 w-2.5" fill="currentColor" viewBox="0 0 8 8">
                                                <circle cx="4" cy="4" r="3"/>
                                            </svg>
                                            {{ $row['tiket_count'] }} Insiden
                                        </span>
                                    @endif
                                </div>
                                <div class="mt-0.5 text-[11px] text-gray-500 dark:text-gray-400">
                                    Vendor: {{ $row['vendor'] }}
                                </div>
                            </td>

                            <!-- Kolom 3: Cabang -->
                            <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-200">
                                {{ $row['cabang'] }}
                            </td>

                            <!-- Kolom 4: Lokasi & Detail Unit -->
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                <div class="font-medium">{{ $row['lokasi'] }}</div>
                                <div class="mt-0.5 flex items-center gap-2 text-[10px] text-gray-500 dark:text-gray-400">
                                    <span class="rounded bg-gray-100 px-1.5 py-0.5 font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        {{ $row['tipe_mesin'] }}
                                    </span>
                                    <span>SN: <span class="font-mono">{{ $row['serial_number'] }}</span></span>
                                    @if($row['luno'] && $row['luno'] !== '-')
                                        <span>• Luno: <span class="font-mono">{{ $row['luno'] }}</span></span>
                                    @endif
                                </div>
                            </td>

                            <!-- Kolom 5: Down Time (Menit) -->
                            <td class="px-3.5 py-3 text-center font-mono">
                                @if ($hasProblem)
                                    <span class="inline-flex items-center rounded-lg bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-700 ring-1 ring-rose-600/10 dark:bg-rose-950/50 dark:text-rose-400 dark:ring-rose-400/20">
                                        {{ number_format($row['downtime_menit'], 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-gray-400 dark:text-gray-600">-</span>
                                @endif
                            </td>

                            <!-- Kolom 6: Uptime (Menit) -->
                            <td class="px-3.5 py-3 text-center font-mono font-medium text-emerald-700 dark:text-emerald-400">
                                {{ number_format($row['uptime_menit'], 0, ',', '.') }}
                            </td>

                            <!-- Kolom 7: Koefisien -->
                            <td class="px-3.5 py-3 text-center font-mono text-gray-500 dark:text-gray-400">
                                {{ number_format($row['koefisien'], 0, ',', '.') }}
                            </td>

                            <!-- Kolom 8: Uptime (%) -->
                            <td class="px-4 py-3 text-center">
                                @if ($pct >= 99.0)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-extrabold text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                        </svg>
                                        {{ number_format($pct, $pct == 100 ? 0 : 2) }}%
                                    </span>
                                @elseif ($pct >= 95.0)
                                    <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-1 text-xs font-bold text-blue-800 dark:bg-blue-950/70 dark:text-blue-300">
                                        {{ number_format($pct, 2) }}%
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2.5 py-1 text-xs font-extrabold text-rose-800 dark:bg-rose-950/70 dark:text-rose-300">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                        </svg>
                                        {{ number_format($pct, 2) }}%
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="rounded-full bg-gray-100 p-3 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                        </svg>
                                    </div>
                                    <h3 class="mt-3 text-sm font-bold text-gray-900 dark:text-white">Tidak ada data mesin ATM ditemukan</h3>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        Tidak ditemukan terminal untuk kriteria vendor atau kata kunci pencarian yang dimasukkan.
                                    </p>
                                    @if(!empty($search) || !empty($vendor_id))
                                        <button
                                            type="button"
                                            wire:click="resetFilters"
                                            class="mt-3 inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-3.5 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-blue-500 cursor-pointer"
                                        >
                                            Reset Filter Pencarian
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                <!-- Footer Total & SLA Rata-rata -->
                @if (count($report['rows']) > 0)
                    <tfoot>
                        <tr class="border-t-2 border-gray-300 bg-gray-100/90 font-bold text-gray-900 dark:border-gray-700 dark:bg-gray-800/90 dark:text-white">
                            <td colspan="4" class="px-6 py-4 text-right text-xs uppercase tracking-wider">
                                Total & SLA Rata-rata ({{ $vendorName }})
                            </td>
                            <td class="px-3.5 py-4 text-center font-mono text-xs text-rose-600 dark:text-rose-400">
                                {{ number_format($report['total_downtime'], 0, ',', '.') }}
                            </td>
                            <td class="px-3.5 py-4 text-center font-mono text-xs text-emerald-700 dark:text-emerald-400">
                                {{ number_format($report['total_uptime_menit'], 0, ',', '.') }}
                            </td>
                            <td class="px-3.5 py-4 text-center font-mono text-xs text-gray-500 dark:text-gray-400">
                                {{ number_format($this->koefisien, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="inline-flex items-center rounded-xl px-3 py-1 text-sm font-black text-white shadow-xs {{ $isTargetMet ? 'bg-emerald-600' : 'bg-rose-600' }}">
                                    {{ number_format($avgSla, 2) }}%
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        <!-- Bagian Catatan Standar & Tanda Tangan Mengetahui Pimpinan -->
        <div class="border-t border-gray-200 bg-gray-50/60 p-6 transition-colors dark:border-gray-800 dark:bg-gray-800/40 print-break-inside-avoid">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between text-xs text-gray-600 dark:text-gray-400">
                <!-- Catatan Teknis -->
                <div class="space-y-1">
                    <div class="font-bold text-gray-900 dark:text-white uppercase tracking-wider text-[11px]">
                        Catatan Ketentuan SLA Bank Sulteng:
                    </div>
                    <div class="text-[11px] text-gray-500 dark:text-gray-400">
                        1. Koefisien operasional standar bulanan dihitung: <strong>30 hari × 24 jam × 60 menit = 43.200 menit</strong>.
                    </div>
                    <div class="text-[11px] text-gray-500 dark:text-gray-400">
                        2. Rumus Uptime (%) = <code>((Koefisien - Down Time) / Koefisien) × 100</code>.
                    </div>
                    <div class="text-[11px] text-gray-500 dark:text-gray-400">
                        3. Target SLA minimum yang disepakati bersama vendor adalah sebesar <strong>≥ 95.00%</strong>.
                    </div>
                </div>

                <!-- Blok Pengesahan Pimpinan -->
                <div class="text-center sm:min-w-[240px] sm:pr-4">
                    <div class="text-[11px] text-gray-500 dark:text-gray-400">Palu, {{ now()->translatedFormat('d F Y') }}</div>
                    <div class="mt-1 font-semibold text-gray-800 dark:text-gray-200">Mengetahui,</div>
                    <div class="font-bold text-gray-900 dark:text-white">Divisi Teknologi Informasi</div>
                    <div class="h-16 flex items-center justify-center">
                        <span class="text-[10px] italic text-gray-400 dark:text-gray-600 print-only">(Tanda Tangan & Cap Resmi)</span>
                    </div>
                    <div class="font-bold text-gray-900 underline dark:text-white">Muh. Abduh Bundung</div>
                    <div class="text-[11px] text-gray-500 dark:text-gray-400">Pemimpin Divisi</div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
