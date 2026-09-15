<?php

namespace App\Filament\Pages;

use App\Models\Terminal;
use App\Models\Tiket;
use App\Models\Vendor;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Border;
use OpenSpout\Common\Entity\Style\BorderPart;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

    public int $koefisien = 44640;

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
        $this->koefisien = $this->daysInMonth * 24 * 60;
    }

    public function updatedBulan(): void
    {
        $this->koefisien = $this->daysInMonth * 24 * 60;
    }

    public function updatedTahun(): void
    {
        $this->koefisien = $this->daysInMonth * 24 * 60;
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

        $this->koefisien = $this->daysInMonth * 24 * 60;
    }

    public function clearSearch(): void
    {
        $this->search = '';
    }

    public function getVendorsProperty()
    {
        return Vendor::withCount('terminals')->orderBy('nama_vendor')->get();
    }

    public function getSelectedVendorNameProperty(): string
    {
        if (empty($this->vendor_id)) {
            return 'SEMUA VENDOR';
        }

        return $this->vendors->firstWhere('id', (int) $this->vendor_id)?->nama_vendor ?? 'SEMUA VENDOR';
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

    public function getDaysInMonthProperty(): int
    {
        $month = max(1, min(12, (int) $this->bulan));
        $year = (int) $this->tahun ?: (int) date('Y');

        return Carbon::createFromDate($year, $month, 1)->daysInMonth;
    }

    public function getKoefisienProperty(): int
    {
        return $this->daysInMonth * 24 * 60;
    }

    public function getReportDataProperty(): array
    {
        $query = Terminal::with(['cabang:id,nama_cabang,label_cabang', 'vendor:id,nama_vendor'])
            ->select(['id', 'profil', 'cabang_id', 'cabang_text', 'urutan_cabang', 'nama_lokasi', 'luno', 'serial_number', 'tipe_mesin', 'vendor_id']);

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

        $monthInt = max(1, min(12, (int) $this->bulan));
        $yearInt = (int) $this->tahun ?: (int) date('Y');

        $startOfMonth = Carbon::createFromDate($yearInt, $monthInt, 1)->startOfMonth();
        $endOfMonth = Carbon::createFromDate($yearInt, $monthInt, 1)->endOfMonth();
        $daysInMonth = $startOfMonth->daysInMonth;
        $koefisien = $daysInMonth * 24 * 60;
        $this->koefisien = $koefisien;
        $now = now();

        // Ambil seluruh tiket yang relevan pada rentang bulan ini (hanya kolom waktu kalkulasi)
        $terminalIds = $terminals->pluck('id');
        $tikets = Tiket::whereIn('terminal_id', $terminalIds)
            ->select(['id', 'terminal_id', 'mulai', 'selesai'])
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

            // Hitung interval downtime riil yang jatuh di bulan yang diobservasi
            $intervals = [];
            foreach ($terminalTikets as $tiket) {
                if (! $tiket->mulai) {
                    continue;
                }

                $tMulai = Carbon::parse($tiket->mulai);
                $tSelesai = $tiket->selesai ? Carbon::parse($tiket->selesai) : null;

                if ($tMulai->greaterThan($endOfMonth)) {
                    continue;
                }
                if ($tSelesai && $tSelesai->lessThan($startOfMonth)) {
                    continue;
                }

                // Potong batas awal dan batas akhir downtime di dalam bulan berjalan
                $wStart = $tMulai->lessThan($startOfMonth) ? $startOfMonth->copy() : $tMulai->copy();

                if ($tSelesai) {
                    $wEnd = $tSelesai->greaterThan($endOfMonth) ? $endOfMonth->copy() : $tSelesai->copy();
                } else {
                    // Tiket masih Open
                    if ($now->lessThan($startOfMonth)) {
                        continue;
                    }
                    $wEnd = $now->lessThan($endOfMonth) ? $now->copy() : $endOfMonth->copy();
                }

                if ($wEnd->greaterThan($wStart)) {
                    $intervals[] = [
                        'start' => $wStart->timestamp,
                        'end' => $wEnd->timestamp,
                    ];
                }
            }

            if (empty($intervals)) {
                $downTime = 0;
            } else {
                // Urutkan dan gabungkan interval gangguan yang overlap
                usort($intervals, fn ($a, $b) => $a['start'] <=> $b['start']);
                $merged = [];
                $curr = $intervals[0];

                for ($i = 1; $i < count($intervals); $i++) {
                    if ($intervals[$i]['start'] <= $curr['end']) {
                        $curr['end'] = max($curr['end'], $intervals[$i]['end']);
                    } else {
                        $merged[] = $curr;
                        $curr = $intervals[$i];
                    }
                }
                $merged[] = $curr;

                $totalSecs = 0;
                foreach ($merged as $item) {
                    $totalSecs += ($item['end'] - $item['start']);
                }
                $downTime = (int) round($totalSecs / 60);
                $downTime = min($downTime, $koefisien);
            }

            if ($downTime > 0) {
                $problemCount++;
            }

            $uptimeMenit = max(0, $koefisien - $downTime);
            $uptimePersen = $koefisien > 0 ? round(($uptimeMenit / $koefisien) * 100, 2) : 100.0;

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
                'koefisien' => $koefisien,
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
            'days_in_month' => $daysInMonth,
            'koefisien' => $koefisien,
        ];
    }

    public function exportExcel(): BinaryFileResponse
    {
        $data = $this->reportData;
        $vendorName = strtoupper($this->selectedVendorName);
        $monthName = strtoupper($this->selectedMonthName);
        $year = $this->tahun;

        $fileName = "Laporan_SLA_{$vendorName}_{$monthName}_{$year}.xlsx";
        $fileName = (string) preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $fileName);

        $rowCount = count($data['rows']);

        $options = new Options;
        $options->setColumnWidth(10, 1); // No
        $options->setColumnWidth(22, 2); // Profil ATM
        $options->setColumnWidth(32, 3); // CABANG/KCP/KAS
        $options->setColumnWidth(48, 4); // LOKASI
        $options->setColumnWidth(24, 5); // DOWN TIME (MENIT)
        $options->setColumnWidth(24, 6); // UPTIME (MENIT)
        $options->setColumnWidth(20, 7); // KOEFISIEN
        $options->setColumnWidth(18, 8); // UPTIME (%)

        // Merge Baris 1 (A1:H1) untuk Judul Laporan
        $options->mergeCells(0, 1, 7, 1);

        if ($rowCount > 0) {
            // Merge Baris Summary A:D
            $summaryRowIndex = $rowCount + 3;
            $options->mergeCells(0, $summaryRowIndex, 3, $summaryRowIndex);
        }

        $tempFilePath = tempnam(sys_get_temp_dir(), 'sla_report_').'.xlsx';

        $writer = new Writer($options);
        $writer->openToFile($tempFilePath);

        $border = new Border(
            new BorderPart(Border::TOP, Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::BOTTOM, Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::LEFT, Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::RIGHT, Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
        );

        $titleStyle = (new Style)
            ->setFontBold()
            ->setFontSize(12)
            ->setBackgroundColor('A6A6A6')
            ->setCellAlignment(CellAlignment::CENTER)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setBorder($border);

        $headerStyle = (new Style)
            ->setFontBold()
            ->setFontSize(11)
            ->setBackgroundColor('A6A6A6')
            ->setCellAlignment(CellAlignment::CENTER)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setShouldWrapText(true)
            ->setBorder($border);

        $centerStyle = (new Style)
            ->setFontSize(11)
            ->setCellAlignment(CellAlignment::CENTER)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setBorder($border);

        $leftStyle = (new Style)
            ->setFontSize(11)
            ->setCellAlignment(CellAlignment::LEFT)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setShouldWrapText(true)
            ->setBorder($border);

        $rightNumericStyle = (new Style)
            ->setFontSize(11)
            ->setFormat('#,##0')
            ->setCellAlignment(CellAlignment::RIGHT)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setBorder($border);

        $greenRightNumericStyle = (new Style)
            ->setFontSize(11)
            ->setBackgroundColor('92D050')
            ->setFormat('#,##0')
            ->setCellAlignment(CellAlignment::RIGHT)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setBorder($border);

        $greenCenterStyle = (new Style)
            ->setFontSize(11)
            ->setBackgroundColor('92D050')
            ->setCellAlignment(CellAlignment::CENTER)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setBorder($border);

        $summaryLeftStyle = (new Style)
            ->setFontBold()
            ->setFontSize(11)
            ->setBackgroundColor('A6A6A6')
            ->setCellAlignment(CellAlignment::LEFT)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setShouldWrapText(true)
            ->setBorder($border);

        $summaryRightNumericStyle = (new Style)
            ->setFontBold()
            ->setFontSize(11)
            ->setBackgroundColor('A6A6A6')
            ->setFormat('#,##0')
            ->setCellAlignment(CellAlignment::RIGHT)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setBorder($border);

        $summaryCenterStyle = (new Style)
            ->setFontBold()
            ->setFontSize(11)
            ->setBackgroundColor('A6A6A6')
            ->setCellAlignment(CellAlignment::CENTER)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setBorder($border);

        // Baris 1: Judul Laporan
        $title = "LAPORAN SLA ATM {$vendorName} BULAN {$monthName} {$year}";
        $titleRow = new Row([
            Cell::fromValue($title, $titleStyle),
            Cell::fromValue('', $titleStyle),
            Cell::fromValue('', $titleStyle),
            Cell::fromValue('', $titleStyle),
            Cell::fromValue('', $titleStyle),
            Cell::fromValue('', $titleStyle),
            Cell::fromValue('', $titleStyle),
            Cell::fromValue('', $titleStyle),
        ]);
        $titleRow->setHeight(34);
        $writer->addRow($titleRow);

        // Baris 2: Header Kolom
        $headerRow = new Row([
            Cell::fromValue('No', $headerStyle),
            Cell::fromValue('Profil ATM', $headerStyle),
            Cell::fromValue('CABANG/KCP/KAS', $headerStyle),
            Cell::fromValue('LOKASI', $headerStyle),
            Cell::fromValue("DOWN TIME\n(MENIT)", $headerStyle),
            Cell::fromValue("UPTIME\n(MENIT)", $headerStyle),
            Cell::fromValue('KOEFISIEN', $headerStyle),
            Cell::fromValue("UPTIME\n(%)", $headerStyle),
        ]);
        $headerRow->setHeight(30);
        $writer->addRow($headerRow);

        // Baris Data Mesin ATM
        foreach ($data['rows'] as $row) {
            $pct = $row['uptime_persen'];
            $pctDisplay = ((float) $pct == (int) $pct) ? (int) $pct : $pct;

            $dataRow = new Row([
                Cell::fromValue($row['no'], $centerStyle),
                Cell::fromValue($row['profil'], $leftStyle),
                Cell::fromValue($row['cabang'], $leftStyle),
                Cell::fromValue($row['lokasi'], $leftStyle),
                $row['downtime_menit'] == 0
                    ? Cell::fromValue('-', $greenCenterStyle)
                    : Cell::fromValue((int) $row['downtime_menit'], $greenRightNumericStyle),
                Cell::fromValue((int) $row['uptime_menit'], $greenRightNumericStyle),
                Cell::fromValue((int) $row['koefisien'], $rightNumericStyle),
                Cell::fromValue($pctDisplay, $centerStyle),
            ]);
            $dataRow->setHeight(24);
            $writer->addRow($dataRow);
        }

        // Baris Summary / Rata-rata
        if ($rowCount > 0) {
            $summaryRow = new Row([
                Cell::fromValue('SLA RATA-RATA VENDOR', $summaryLeftStyle),
                Cell::fromValue('', $summaryLeftStyle),
                Cell::fromValue('', $summaryLeftStyle),
                Cell::fromValue('', $summaryLeftStyle),
                Cell::fromValue((int) $data['total_downtime'], $summaryRightNumericStyle),
                Cell::fromValue((int) $data['total_uptime_menit'], $summaryRightNumericStyle),
                Cell::fromValue('', $summaryCenterStyle),
                Cell::fromValue($data['average_sla'].'%', $summaryCenterStyle),
            ]);
            $summaryRow->setHeight(28);
            $writer->addRow($summaryRow);
        }

        $writer->close();

        return response()->download(
            $tempFilePath,
            $fileName,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        )->deleteFileAfterSend(true);
    }

    public function exportCsv(): BinaryFileResponse
    {
        return $this->exportExcel();
    }
}
