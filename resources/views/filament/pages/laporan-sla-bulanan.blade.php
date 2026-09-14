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
        $daysCount = $report['days_in_month'] ?? $this->daysInMonth;
        $koefisienMenit = $report['koefisien'] ?? $this->koefisien;
    @endphp

    <style>
        @media print {
            @page {
                size: A4 landscape;
                margin: 10mm;
            }
            body {
                background: white !important;
                color: #000 !important;
                font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif !important;
                font-size: 10pt !important;
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
                font-size: 9.5pt !important;
                border-collapse: collapse !important;
            }
            th, td {
                padding: 6px 8px !important;
                border: 1px solid #334155 !important;
                color: #000 !important;
                background: transparent !important;
            }
            thead th {
                background-color: #f1f5f9 !important;
                font-weight: bold !important;
                text-align: center !important;
            }
            tfoot tr {
                background-color: #f1f5f9 !important;
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
        <div class="flex items-center justify-between border-b-2 border-black pb-4">
            <div>
                <h1 class="text-xl font-black uppercase tracking-wider text-black">PT. BANK PEMBANGUNAN DAERAH SULAWESI TENGAH</h1>
                <p class="text-sm font-bold text-gray-800">DIVISI TEKNOLOGI INFORMASI — MONITORING OPERASIONAL ATM & CRM</p>
                <h2 class="mt-1.5 text-base font-extrabold text-black">LAPORAN SERVICE LEVEL AGREEMENT (SLA) BULANAN</h2>
            </div>
            <div class="text-right text-sm">
                <div class="font-extrabold text-black text-base">VENDOR: {{ $vendorName }}</div>
                <div class="font-semibold text-gray-900">PERIODE: {{ $monthName }} {{ $year }}</div>
                <div class="text-xs text-gray-700">Dicetak: {{ now()->translatedFormat('d F Y H:i') }} WITA</div>
            </div>
        </div>
    </div>

    <!-- AREA LAYAR UTAMA (No Print) -->
    <div class="no-print space-y-6">
        <!-- 1. Header Banner & Identitas Laporan (Aksesibilitas Tinggi, Huruf Jelas) -->
        <div class="relative overflow-hidden rounded-2xl border-2 border-slate-200 bg-white p-6 shadow-sm transition-colors dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-blue-100 px-3.5 py-1.5 text-sm font-bold text-blue-900 ring-1 ring-blue-700/20 dark:bg-blue-950 dark:text-blue-200 dark:ring-blue-400/30 mb-2.5">
                        <svg class="h-4 w-4 shrink-0 text-blue-700 dark:text-blue-300" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" />
                        </svg>
                        <span>Bank Sulteng • Monitoring Performa ATM & CRM</span>
                    </div>

                    <h1 class="text-2xl font-black tracking-tight text-slate-900 sm:text-3xl dark:text-white">
                        Laporan Service Level Agreement (SLA)
                    </h1>

                    <div class="mt-2.5 flex flex-wrap items-center gap-3 text-sm text-slate-700 dark:text-slate-300">
                        <span class="inline-flex items-center gap-2 font-bold text-slate-900 dark:text-white">
                            <span class="h-2.5 w-2.5 rounded-full bg-blue-600"></span>
                            Vendor: <strong class="text-blue-700 dark:text-blue-400">{{ $vendorName }}</strong>
                        </span>
                        <span class="text-slate-400 dark:text-slate-600">•</span>
                        <span>Periode: <strong class="text-slate-900 dark:text-white">{{ $monthName }} {{ $year }}</strong></span>
                        <span class="text-slate-400 dark:text-slate-600">•</span>
                        <span>Koefisien Riil: <strong class="font-mono text-slate-900 dark:text-white">{{ number_format($koefisienMenit, 0, ',', '.') }} Menit</strong> ({{ $daysCount }} Hari)</span>
                    </div>
                </div>

                <!-- Tombol Aksi Unduh Excel Spreadsheet -->
                <div class="flex flex-wrap items-center gap-3">
                    <button
                        type="button"
                        wire:click="exportExcel"
                        wire:loading.attr="disabled"
                        class="inline-flex min-h-[44px] items-center gap-2.5 rounded-xl bg-emerald-700 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:bg-emerald-600 focus:outline-hidden focus:ring-3 focus:ring-emerald-500/40 active:scale-95 cursor-pointer disabled:opacity-50"
                        title="Unduh seluruh baris data ke file spreadsheet Excel (.xlsx)"
                    >
                        <svg wire:loading.remove wire:target="exportExcel" class="h-5 w-5 text-emerald-100" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        <svg wire:loading wire:target="exportExcel" class="h-5 w-5 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Unduh Excel</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- 2. Pilihan Vendor Tab Navigasi Ramah Sentuhan -->
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2 rounded-2xl border-2 border-slate-200 bg-slate-100/90 p-2 dark:border-slate-800 dark:bg-slate-900/80">
                <span class="px-3 text-xs font-black uppercase tracking-wider text-slate-600 dark:text-slate-400">Pilih Vendor:</span>

                <button
                    type="button"
                    wire:click="$set('vendor_id', '')"
                    class="inline-flex min-h-[38px] items-center gap-2 rounded-xl px-4 py-2 text-sm font-bold transition-all cursor-pointer {{ empty($vendor_id) ? 'bg-white text-blue-700 shadow-sm ring-1 ring-slate-900/10 dark:bg-slate-800 dark:text-blue-300' : 'text-slate-700 hover:text-slate-900 hover:bg-white/70 dark:text-slate-300 dark:hover:text-white' }}"
                >
                    <span>Semua Vendor</span>
                </button>

                @foreach ($this->vendors as $v)
                    <button
                        type="button"
                        wire:click="$set('vendor_id', '{{ $v->id }}')"
                        class="inline-flex min-h-[38px] items-center gap-2 rounded-xl px-4 py-2 text-sm font-bold transition-all cursor-pointer {{ $vendor_id == (string)$v->id ? 'bg-white text-blue-700 shadow-sm ring-1 ring-slate-900/10 dark:bg-slate-800 dark:text-blue-300' : 'text-slate-700 hover:text-slate-900 hover:bg-white/70 dark:text-slate-300 dark:hover:text-white' }}"
                    >
                        <span>{{ $v->nama_vendor }}</span>
                        <span class="rounded-lg px-2 py-0.5 text-xs font-black {{ $vendor_id == (string)$v->id ? 'bg-blue-100 text-blue-900 dark:bg-blue-950 dark:text-blue-200' : 'bg-slate-200 text-slate-800 dark:bg-slate-700 dark:text-slate-200' }}">
                            {{ $v->terminals()->count() }} Unit
                        </span>
                    </button>
                @endforeach
            </div>

            @if(!empty($search) || !empty($vendor_id))
                <button
                    type="button"
                    wire:click="resetFilters"
                    class="inline-flex min-h-[38px] items-center gap-2 rounded-xl border-2 border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:border-slate-400 hover:text-slate-900 transition-colors cursor-pointer dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                    title="Kembalikan semua filter ke kondisi awal"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    <span>Reset Filter</span>
                </button>
            @endif
        </div>

        <!-- 3. Filter Bar (Bulan, Tahun, & Pencarian Berukuran Besar & Jelas) -->
        <div class="flex flex-col gap-4 rounded-2xl border-2 border-slate-200 bg-white p-4 shadow-sm transition-colors sm:flex-row sm:items-center sm:justify-between dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-wrap items-center gap-4">
                <!-- Selector Bulan -->
                <div class="flex items-center gap-2.5">
                    <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Bulan:</span>
                    <div class="relative">
                        <select
                            wire:model.live="bulan"
                            class="min-h-[44px] appearance-none rounded-xl border-2 border-slate-300 bg-slate-50 py-2 pl-4 pr-10 text-sm font-bold text-slate-900 transition-colors focus:border-blue-600 focus:bg-white focus:outline-hidden focus:ring-3 focus:ring-blue-500/20 cursor-pointer dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        >
                            @foreach ($this->months as $key => $name)
                                <option value="{{ $key }}" class="dark:bg-slate-800 dark:text-white">{{ $name }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-600 dark:text-slate-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Selector Tahun -->
                <div class="flex items-center gap-2.5">
                    <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Tahun:</span>
                    <div class="relative">
                        <select
                            wire:model.live="tahun"
                            class="min-h-[44px] appearance-none rounded-xl border-2 border-slate-300 bg-slate-50 py-2 pl-4 pr-10 text-sm font-bold text-slate-900 transition-colors focus:border-blue-600 focus:bg-white focus:outline-hidden focus:ring-3 focus:ring-blue-500/20 cursor-pointer dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        >
                            @foreach ($this->years as $y)
                                <option value="{{ $y }}" class="dark:bg-slate-800 dark:text-white">{{ $y }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-600 dark:text-slate-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Input Pencarian -->
                <div class="relative min-w-[260px] flex-1 sm:w-80 sm:flex-initial">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-500">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </div>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Cari profil, cabang, lokasi, SN..."
                        class="min-h-[44px] w-full rounded-xl border-2 border-slate-300 bg-slate-50 py-2 pl-10 pr-9 text-sm text-slate-900 font-medium placeholder:text-slate-500 transition-colors focus:border-blue-600 focus:bg-white focus:outline-hidden focus:ring-3 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-400"
                    />
                    @if(!empty($search))
                        <button
                            type="button"
                            wire:click="clearSearch"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-500 hover:text-slate-800 dark:hover:text-white cursor-pointer"
                            title="Hapus kata kunci pencarian"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    @endif
                </div>
            </div>

            <!-- Counter Info Data -->
            <div class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                <span>Ditemukan:</span>
                <span class="inline-flex items-center rounded-xl bg-slate-100 border border-slate-300 px-3 py-1 font-black text-slate-900 dark:bg-slate-800 dark:border-slate-700 dark:text-white">
                    {{ count($report['rows']) }} Mesin ATM/CRM
                </span>
            </div>
        </div>

        <!-- 4. Kartu Metrik Ringkasan KPI Eksekutif (Tipografi Besar & Jelas untuk Lansia) -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Kartu 1: SLA Rata-rata -->
            <div class="relative overflow-hidden rounded-2xl border-2 border-slate-200 bg-white p-6 shadow-sm transition-colors dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-black uppercase tracking-wider text-slate-600 dark:text-slate-400">Rata-rata SLA Vendor</span>
                    <div class="rounded-xl p-2.5 {{ $isTargetMet ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' }}">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                        </svg>
                    </div>
                </div>

                <div class="mt-4 flex items-baseline gap-3">
                    <span class="text-4xl font-black font-mono tracking-tight {{ $isTargetMet ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400' }}">
                        {{ number_format($avgSla, 2) }}%
                    </span>
                </div>

                <div class="mt-2.5">
                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-black {{ $isTargetMet ? 'bg-emerald-100 text-emerald-900 border border-emerald-300 dark:bg-emerald-950/80 dark:text-emerald-200 dark:border-emerald-800' : 'bg-rose-100 text-rose-900 border border-rose-300 dark:bg-rose-950/80 dark:text-rose-200 dark:border-rose-800' }}">
                        @if($isTargetMet)
                            <svg class="h-3.5 w-3.5 text-emerald-700 dark:text-emerald-300" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            <span>Memenuhi Target SLA</span>
                        @else
                            <svg class="h-3.5 w-3.5 text-rose-700 dark:text-rose-300" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                            <span>Di Bawah Target SLA</span>
                        @endif
                    </span>
                </div>

                <!-- Target Progress Bar -->
                <div class="mt-4">
                    <div class="flex justify-between text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5">
                        <span>Standar Minimum: ≥ 95.00%</span>
                        <span>{{ number_format($avgSla, 1) }}%</span>
                    </div>
                    <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
                        <div class="h-full rounded-full transition-all duration-500 {{ $isTargetMet ? 'bg-emerald-600' : 'bg-rose-600' }}" style="width: {{ min(100, max(0, $avgSla)) }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Kartu 2: Total Mesin Terpantau -->
            <div class="relative overflow-hidden rounded-2xl border-2 border-slate-200 bg-white p-6 shadow-sm transition-colors dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-black uppercase tracking-wider text-slate-600 dark:text-slate-400">Total Mesin ATM / CRM</span>
                    <div class="rounded-xl bg-blue-100 p-2.5 text-blue-800 dark:bg-blue-950 dark:text-blue-300">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 0 1-3-3m3 3a3 3 0 1 0 0 6h13.5a3 3 0 1 0 0-6m-16.5-3a3 3 0 0 1 3-3h13.5a3 3 0 0 1 3 3m-19.5 0a4.5 4.5 0 0 1 .9-2.7L5.75 5.1a2.25 2.25 0 0 1 1.77-1.1h9.96a2.25 2.25 0 0 1 1.77 1.1l2.1 3.45a4.5 4.5 0 0 1 .9 2.7" />
                        </svg>
                    </div>
                </div>

                <div class="mt-4 flex items-baseline gap-2.5">
                    <span class="text-4xl font-black font-mono tracking-tight text-slate-900 dark:text-white">
                        {{ $totalMachines }}
                    </span>
                    <span class="text-sm font-bold text-slate-600 dark:text-slate-400">Unit Terminal</span>
                </div>

                <div class="mt-4 flex items-center gap-3 text-sm font-bold text-slate-700 dark:text-slate-300">
                    <span class="inline-flex items-center gap-1.5 text-emerald-700 dark:text-emerald-400">
                        <span class="h-2 w-2 rounded-full bg-emerald-600"></span>
                        {{ $cleanMachines }} Normal
                    </span>
                    <span class="text-slate-400 dark:text-slate-600">•</span>
                    <span class="inline-flex items-center gap-1.5 {{ $problemMachines > 0 ? 'text-rose-700 dark:text-rose-400' : 'text-slate-600 dark:text-slate-400' }}">
                        <span class="h-2 w-2 rounded-full {{ $problemMachines > 0 ? 'bg-rose-600' : 'bg-slate-400' }}"></span>
                        {{ $problemMachines }} Kendala
                    </span>
                </div>
            </div>

            <!-- Kartu 3: Akumulasi Down Time Riil -->
            <div class="relative overflow-hidden rounded-2xl border-2 border-slate-200 bg-white p-6 shadow-sm transition-colors dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-black uppercase tracking-wider text-slate-600 dark:text-slate-400">Total Down Time Riil</span>
                    <div class="rounded-xl bg-amber-100 p-2.5 text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                </div>

                <div class="mt-4 flex items-baseline gap-2.5">
                    <span class="text-4xl font-black font-mono tracking-tight text-amber-700 dark:text-amber-400">
                        {{ number_format($report['total_downtime'], 0, ',', '.') }}
                    </span>
                    <span class="text-sm font-bold text-slate-600 dark:text-slate-400">Menit</span>
                </div>

                <div class="mt-4 text-sm font-semibold text-slate-700 dark:text-slate-300">
                    <span>Durasi gangguan: </span>
                    <strong class="font-extrabold text-slate-900 dark:text-white">
                        ~{{ number_format($report['total_downtime'] / 60, 1, ',', '.') }} Jam
                    </strong>
                    @if($report['total_downtime'] >= 1440)
                        <span class="text-slate-500 dark:text-slate-400">({{ round($report['total_downtime'] / 1440, 1) }} Hari)</span>
                    @endif
                </div>
            </div>

            <!-- Kartu 4: Rasio Uptime Total -->
            <div class="relative overflow-hidden rounded-2xl border-2 border-slate-200 bg-white p-6 shadow-sm transition-colors dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-black uppercase tracking-wider text-slate-600 dark:text-slate-400">Kesehatan Jaringan ATM</span>
                    <div class="rounded-xl bg-teal-100 p-2.5 text-teal-800 dark:bg-teal-950 dark:text-teal-300">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                        </svg>
                    </div>
                </div>

                @php
                    $healthyPercent = $totalMachines > 0 ? round(($cleanMachines / $totalMachines) * 100, 1) : 100;
                @endphp

                <div class="mt-4 flex items-baseline gap-2.5">
                    <span class="text-4xl font-black font-mono tracking-tight text-teal-700 dark:text-teal-400">
                        {{ $healthyPercent }}%
                    </span>
                    <span class="text-sm font-bold text-slate-600 dark:text-slate-400">Bebas Insiden</span>
                </div>

                <div class="mt-4 text-sm font-semibold text-slate-700 dark:text-slate-300">
                    <strong class="font-extrabold text-slate-900 dark:text-white">{{ $cleanMachines }}</strong>
                    <span> dari </span>
                    <strong class="font-extrabold text-slate-900 dark:text-white">{{ $totalMachines }}</strong>
                    <span> unit beroperasi penuh</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. Lembar Tabel Data SLA Modern & Ramah Lansia (Kontras Tinggi, Font Jelas, Zebra Striping) -->
    <div class="print-container mt-6 overflow-hidden rounded-2xl border-2 border-slate-300 bg-white shadow-sm transition-colors dark:border-slate-800 dark:bg-slate-900">
        <!-- Subheader Tabel (Tampak Layar) -->
        <div class="no-print flex flex-col gap-2 border-b-2 border-slate-200 bg-slate-100/90 px-6 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800 dark:bg-slate-800/80">
            <div>
                <h2 class="text-base font-extrabold uppercase tracking-wide text-slate-900 dark:text-white">
                    Rincian Kinerja SLA ATM & CRM — {{ $vendorName }}
                </h2>
                <p class="text-sm font-semibold text-slate-600 dark:text-slate-400">
                    Periode: {{ $monthName }} {{ $year }} • Koefisien Operasional Riil: {{ number_format($koefisienMenit, 0, ',', '.') }} Menit ({{ $daysCount }} Hari)
                </p>
            </div>
            <div class="text-sm font-extrabold">
                <span class="text-slate-700 dark:text-slate-300">Target Standar SLA: </span>
                <span class="rounded-lg bg-emerald-100 border border-emerald-300 px-2.5 py-1 text-emerald-900 dark:bg-emerald-950 dark:border-emerald-800 dark:text-emerald-300">
                    ≥ 95.00%
                </span>
            </div>
        </div>

        <!-- Tabel Data Interaktif Berkontras Tinggi -->
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left text-sm">
                <thead>
                    <tr class="border-b-2 border-slate-300 bg-slate-200/90 text-xs font-black uppercase tracking-wider text-slate-800 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                        <th scope="col" class="w-14 px-4 py-3.5 text-center">No</th>
                        <th scope="col" class="min-w-[190px] px-4 py-3.5">Profil Terminal</th>
                        <th scope="col" class="min-w-[160px] px-4 py-3.5">Cabang / Unit Kerja</th>
                        <th scope="col" class="min-w-[220px] px-4 py-3.5">Lokasi & Tipe Mesin</th>
                        <th scope="col" class="w-36 px-4 py-3.5 text-center">
                            Down Time<br><span class="text-xs font-semibold lowercase tracking-normal text-slate-600 dark:text-slate-400">(menit)</span>
                        </th>
                        <th scope="col" class="w-36 px-4 py-3.5 text-center">
                            Uptime<br><span class="text-xs font-semibold lowercase tracking-normal text-slate-600 dark:text-slate-400">(menit)</span>
                        </th>
                        <th scope="col" class="w-32 px-4 py-3.5 text-center">
                            Koefisien<br><span class="text-xs font-semibold lowercase tracking-normal text-slate-600 dark:text-slate-400">(menit)</span>
                        </th>
                        <th scope="col" class="w-36 px-4 py-3.5 text-center">
                            Uptime SLA<br><span class="text-xs font-semibold lowercase tracking-normal text-slate-600 dark:text-slate-400">(persentase)</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-slate-200 dark:divide-slate-800">
                    @forelse ($report['rows'] as $row)
                        @php
                            $hasProblem = $row['downtime_menit'] > 0;
                            $pct = $row['uptime_persen'];
                        @endphp
                        <tr class="transition-colors hover:bg-blue-50/80 dark:hover:bg-slate-800/60 {{ $hasProblem ? 'bg-rose-50/50 dark:bg-rose-950/25' : 'even:bg-slate-50/60 dark:even:bg-slate-800/40' }}">
                            <!-- Kolom 1: No -->
                            <td class="px-4 py-3.5 text-center font-bold text-slate-600 dark:text-slate-400 text-sm">
                                {{ $row['no'] }}
                            </td>

                            <!-- Kolom 2: Profil Terminal & Indikator Masalah -->
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-2">
                                    <span class="font-extrabold text-base text-slate-900 dark:text-slate-100">{{ $row['profil'] }}</span>
                                    @if ($row['tiket_count'] > 0)
                                        <span class="inline-flex items-center gap-1 rounded-md bg-rose-200 px-2 py-0.5 text-xs font-black text-rose-900 dark:bg-rose-950 dark:text-rose-200 border border-rose-300 dark:border-rose-800" title="{{ $row['tiket_count'] }} tiket insiden bulan ini">
                                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 8 8">
                                                <circle cx="4" cy="4" r="3"/>
                                            </svg>
                                            {{ $row['tiket_count'] }} Insiden
                                        </span>
                                    @endif
                                </div>
                                <div class="mt-0.5 text-xs font-semibold text-slate-600 dark:text-slate-400">
                                    Vendor: <span class="font-bold text-slate-800 dark:text-slate-200">{{ $row['vendor'] }}</span>
                                </div>
                            </td>

                            <!-- Kolom 3: Cabang -->
                            <td class="px-4 py-3.5 font-bold text-slate-800 dark:text-slate-200 text-sm">
                                {{ $row['cabang'] }}
                            </td>

                            <!-- Kolom 4: Lokasi & Detail Unit -->
                            <td class="px-4 py-3.5 text-slate-800 dark:text-slate-200">
                                <div class="font-bold text-sm">{{ $row['lokasi'] }}</div>
                                <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-600 dark:text-slate-400 font-semibold">
                                    <span class="rounded-md bg-slate-200 px-2 py-0.5 font-bold text-slate-800 dark:bg-slate-700 dark:text-slate-200">
                                        {{ $row['tipe_mesin'] }}
                                    </span>
                                    <span>SN: <span class="font-mono font-bold text-slate-900 dark:text-slate-200">{{ $row['serial_number'] }}</span></span>
                                    @if($row['luno'] && $row['luno'] !== '-')
                                        <span>• Luno: <span class="font-mono font-bold text-slate-900 dark:text-slate-200">{{ $row['luno'] }}</span></span>
                                    @endif
                                </div>
                            </td>

                            <!-- Kolom 5: Down Time (Menit) -->
                            <td class="px-4 py-3.5 text-center font-mono font-bold text-base">
                                @if ($hasProblem)
                                    <span class="inline-flex items-center rounded-lg bg-rose-100 px-3 py-1 text-sm font-black text-rose-800 border border-rose-300 dark:bg-rose-950 dark:text-rose-300 dark:border-rose-800">
                                        {{ number_format($row['downtime_menit'], 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-slate-400 dark:text-slate-600 font-normal">0</span>
                                @endif
                            </td>

                            <!-- Kolom 6: Uptime (Menit) -->
                            <td class="px-4 py-3.5 text-center font-mono font-bold text-sm text-emerald-800 dark:text-emerald-400">
                                {{ number_format($row['uptime_menit'], 0, ',', '.') }}
                            </td>

                            <!-- Kolom 7: Koefisien -->
                            <td class="px-4 py-3.5 text-center font-mono font-semibold text-sm text-slate-600 dark:text-slate-400">
                                {{ number_format($row['koefisien'], 0, ',', '.') }}
                            </td>

                            <!-- Kolom 8: Uptime (%) -->
                            <td class="px-4 py-3.5 text-center">
                                @if ($pct >= 99.0)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 border border-emerald-300 px-3 py-1 text-sm font-black text-emerald-900 dark:bg-emerald-950 dark:border-emerald-800 dark:text-emerald-300">
                                        <svg class="h-4 w-4 shrink-0 text-emerald-700 dark:text-emerald-300" fill="none" viewBox="0 0 24 24" stroke-width="2.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                        </svg>
                                        {{ number_format($pct, $pct == 100 ? 0 : 2) }}%
                                    </span>
                                @elseif ($pct >= 95.0)
                                    <span class="inline-flex items-center rounded-full bg-blue-100 border border-blue-300 px-3 py-1 text-sm font-black text-blue-900 dark:bg-blue-950 dark:border-blue-800 dark:text-blue-300">
                                        {{ number_format($pct, 2) }}%
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-100 border border-rose-300 px-3 py-1 text-sm font-black text-rose-900 dark:bg-rose-950 dark:border-rose-800 dark:text-rose-300">
                                        <svg class="h-4 w-4 shrink-0 text-rose-700 dark:text-rose-300" fill="none" viewBox="0 0 24 24" stroke-width="2.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                        </svg>
                                        {{ number_format($pct, 2) }}%
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-14 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="rounded-full bg-slate-100 p-4 text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                                        <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                        </svg>
                                    </div>
                                    <h3 class="mt-4 text-base font-extrabold text-slate-900 dark:text-white">Tidak ada data mesin ATM ditemukan</h3>
                                    <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-400 font-medium">
                                        Tidak ditemukan terminal untuk kriteria vendor atau kata kunci pencarian yang dimasukkan.
                                    </p>
                                    @if(!empty($search) || !empty($vendor_id))
                                        <button
                                            type="button"
                                            wire:click="resetFilters"
                                            class="mt-4 inline-flex min-h-[40px] items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-blue-500 cursor-pointer"
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
                        <tr class="border-t-4 border-slate-400 bg-slate-200/95 font-extrabold text-slate-900 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                            <td colspan="4" class="px-6 py-4.5 text-right text-sm uppercase tracking-wider">
                                Total Akumulasi & Rata-rata SLA ({{ $vendorName }})
                            </td>
                            <td class="px-4 py-4.5 text-center font-mono text-base text-rose-700 dark:text-rose-400">
                                {{ number_format($report['total_downtime'], 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-4.5 text-center font-mono text-base text-emerald-800 dark:text-emerald-400">
                                {{ number_format($report['total_uptime_menit'], 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-4.5 text-center font-mono text-base text-slate-700 dark:text-slate-300">
                                {{ number_format($koefisienMenit, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-4.5 text-center">
                                <span class="inline-flex items-center rounded-xl px-4 py-1.5 text-base font-black text-white shadow-md {{ $isTargetMet ? 'bg-emerald-700' : 'bg-rose-700' }}">
                                    {{ number_format($avgSla, 2) }}%
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</x-filament-panels::page>
