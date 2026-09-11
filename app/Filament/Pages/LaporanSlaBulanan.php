<?php

namespace App\Filament\Pages;

use App\Models\Terminal;
use App\Models\Tiket;
use App\Models\Vendor;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanSlaBulanan extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;

    protected static ?string $navigationLabel = 'Laporan SLA Bulanan';

    protected static ?string $title = 'Laporan SLA ATM Bulanan';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.laporan-sla-bulanan';

    public string $bulan = '01';

    public string $tahun = '2025';

    public string $vendor_id = '';

    public int $koefisien = 43200;

    public string $search = '';

    public function mount(): void
    {
        $this->bulan = date('m');
        $this->tahun = date('Y');

        // Jika data seeder awal banyak di Jan 2025, defaultkan ke Jan 2025 jika belum ada tiket di bulan sekarang
        if (Tiket::whereMonth('mulai', 1)->whereYear('mulai', 2025)->exists() && ! Tiket::whereMonth('mulai', (int) $this->bulan)->whereYear('mulai', (int) $this->tahun)->exists()) {
            $this->bulan = '01';
            $this->tahun = '2025';
        }

        $defaultVendor = Vendor::where('nama_vendor', 'SRISHINDU')->first() ?? Vendor::first();
        $this->vendor_id = (string) ($defaultVendor?->id ?? '');
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $defaultVendor = Vendor::where('nama_vendor', 'SRISHINDU')->first() ?? Vendor::first();
        $this->vendor_id = (string) ($defaultVendor?->id ?? '');

        if (Tiket::whereMonth('mulai', 1)->whereYear('mulai', 2025)->exists() && ! Tiket::whereMonth('mulai', (int) date('m'))->whereYear('mulai', (int) date('Y'))->exists()) {
            $this->bulan = '01';
            $this->tahun = '2025';
        } else {
            $this->bulan = date('m');
            $this->tahun = date('Y');
        }
    }

    public function clearSearch(): void
    {
        $this->search = '';
    }

    public function getVendorsProperty()
    {
        return Vendor::orderBy('nama_vendor')->get();
    }

    public function getSelectedVendorNameProperty(): string
    {
        if (empty($this->vendor_id)) {
            return 'SEMUA VENDOR';
        }

        return Vendor::find($this->vendor_id)?->nama_vendor ?? 'SEMUA VENDOR';
    }

    public function getMonthsProperty(): array
    {
        return [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];
    }

    public function getYearsProperty(): array
    {
        $currentYear = (int) date('Y');
        $years = [];
        for ($y = $currentYear - 2; $y <= $currentYear + 1; $y++) {
            $years[(string) $y] = (string) $y;
        }

        return $years;
    }

    public function getSelectedMonthNameProperty(): string
    {
        return $this->months[$this->bulan] ?? $this->bulan;
    }

    public function getReportDataProperty(): array
    {
        $query = Terminal::with(['cabang', 'vendor']);

        if (! empty($this->vendor_id)) {
            $query->where('vendor_id', $this->vendor_id);
        }

        if (! empty($this->search)) {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('profil', 'like', "%{$s}%")
                    ->orWhere('nama_lokasi', 'like', "%{$s}%")
                    ->orWhere('cabang_text', 'like', "%{$s}%")
                    ->orWhere('luno', 'like', "%{$s}%")
                    ->orWhere('serial_number', 'like', "%{$s}%")
                    ->orWhereHas('cabang', fn ($c) => $c->where('nama_cabang', 'like', "%{$s}%")->orWhere('label_cabang', 'like', "%{$s}%"));
            });
        }

        $terminals = $query->orderBy('cabang_id')->orderBy('urutan_cabang')->get();

        $monthInt = (int) $this->bulan;
        $yearInt = (int) $this->tahun;

        $startOfMonth = Carbon::createFromDate($yearInt, $monthInt, 1)->startOfMonth();
        $endOfMonth = Carbon::createFromDate($yearInt, $monthInt, 1)->endOfMonth();

        // Ambil seluruh tiket yang relevan pada rentang bulan ini
        $terminalIds = $terminals->pluck('id');
        $tikets = Tiket::whereIn('terminal_id', $terminalIds)
            ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('mulai', [$startOfMonth, $endOfMonth])
                    ->orWhere(function ($sub) use ($startOfMonth, $endOfMonth) {
                        $sub->where('mulai', '<=', $endOfMonth)
                            ->where(function ($s) use ($startOfMonth) {
                                $s->where('selesai', '>=', $startOfMonth)
                                    ->orWhereNull('selesai');
                            });
                    });
            })
            ->get()
            ->groupBy('terminal_id');

        $rows = [];
        $totalDownTime = 0;
        $totalUptimeMenit = 0;
        $sumUptimePersen = 0;
        $problemCount = 0;

        foreach ($terminals as $index => $terminal) {
            $terminalTikets = $tikets->get($terminal->id, collect());
            $downTime = (int) $terminalTikets->sum('durasi_menit');

            if ($downTime > 0) {
                $problemCount++;
            }

            $uptimeMenit = max(0, $this->koefisien - $downTime);
            $uptimePersen = $this->koefisien > 0 ? round(($uptimeMenit / $this->koefisien) * 100, 2) : 100.0;

            $totalDownTime += $downTime;
            $totalUptimeMenit += $uptimeMenit;
            $sumUptimePersen += $uptimePersen;

            $rows[] = [
                'no' => $index + 1,
                'id' => $terminal->id,
                'profil' => $terminal->profil,
                'cabang' => $terminal->cabang?->label_cabang ?? $terminal->cabang_text ?? '-',
                'lokasi' => $terminal->nama_lokasi,
                'tipe_mesin' => $terminal->tipe_mesin ?? '-',
                'luno' => $terminal->luno ?? '-',
                'serial_number' => $terminal->serial_number ?? '-',
                'vendor' => $terminal->vendor?->nama_vendor ?? '-',
                'downtime_menit' => $downTime,
                'uptime_menit' => $uptimeMenit,
                'koefisien' => $this->koefisien,
                'uptime_persen' => $uptimePersen,
                'tiket_count' => $terminalTikets->count(),
            ];
        }

        $count = count($rows);
        $averageSla = $count > 0 ? round($sumUptimePersen / $count, 2) : 100.0;

        return [
            'rows' => $rows,
            'total_machines' => $count,
            'problem_machines' => $problemCount,
            'clean_machines' => $count - $problemCount,
            'total_downtime' => $totalDownTime,
            'total_uptime_menit' => $totalUptimeMenit,
            'average_sla' => $averageSla,
        ];
    }

    public function exportCsv(): StreamedResponse
    {
        $data = $this->reportData;
        $vendorName = $this->selectedVendorName;
        $monthName = $this->selectedMonthName;
        $year = $this->tahun;

        $fileName = "Laporan_SLA_{$vendorName}_{$monthName}_{$year}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        return response()->stream(function () use ($data, $vendorName, $monthName, $year) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ["LAPORAN SLA ATM {$vendorName} BULAN {$monthName} {$year}"]);
            fputcsv($handle, []);
            fputcsv($handle, [
                'No',
                'Profil ATM',
                'CABANG/KCP/KAS',
                'Lokasi',
                'DOWN TIME (MENIT)',
                'UPTIME (MENIT)',
                'KOEFISIEN',
                'UPTIME (%)',
                'Total Tiket',
            ]);

            foreach ($data['rows'] as $row) {
                fputcsv($handle, [
                    $row['no'],
                    $row['profil'],
                    $row['cabang'],
                    $row['lokasi'],
                    $row['downtime_menit'],
                    $row['uptime_menit'],
                    $row['koefisien'],
                    $row['uptime_persen'],
                    $row['tiket_count'],
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, [
                'SLA RATA-RATA VENDOR',
                '',
                '',
                '',
                $data['total_downtime'],
                $data['total_uptime_menit'],
                '',
                $data['average_sla'].'%',
                '',
            ]);

            fclose($handle);
        }, 200, $headers);
    }
}
