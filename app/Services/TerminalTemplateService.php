<?php

namespace App\Services;

use App\Models\Terminal;
use DateInterval;
use DateTimeInterface;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Cell\StringCell;
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

class TerminalTemplateService
{
    /**
     * Generate an Excel export/template for terminal records.
     */
    public function generateTemplate(): BinaryFileResponse
    {
        $fileName = 'Data_Terminal_ATM_CRM_Bank_Sulteng.xlsx';
        $tempFilePath = tempnam(sys_get_temp_dir(), 'atm_tpl_').'.xlsx';

        $options = new Options;
        $writer = new Writer($options);
        $writer->openToFile($tempFilePath);

        $border = new Border(
            new BorderPart(Border::TOP, Color::rgb(200, 200, 200), Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::RIGHT, Color::rgb(200, 200, 200), Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::BOTTOM, Color::rgb(200, 200, 200), Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::LEFT, Color::rgb(200, 200, 200), Border::WIDTH_THIN, Border::STYLE_SOLID),
        );

        $headerStyle = (new Style)
            ->setFontBold()
            ->setFontSize(10)
            ->setFontColor(Color::WHITE)
            ->setBackgroundColor('0F2F57') // Navy blue Bank Sulteng
            ->setCellAlignment(CellAlignment::CENTER)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setBorder($border);

        $leftStyle = (new Style)
            ->setFontSize(10)
            ->setCellAlignment(CellAlignment::LEFT)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setBorder($border);

        $centerStyle = (new Style)
            ->setFontSize(10)
            ->setCellAlignment(CellAlignment::CENTER)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setBorder($border);

        // Header Row
        $headers = [
            'Profil ATM',
            'Cabang',
            'Urutan Cabang',
            'Nama Lokasi Fisik',
            'IP Address',
            'ID / LUNO',
            'Port Switch',
            'Vendor Maintenance',
            'Serial Number',
            'Tipe Mesin',
            'Kategori',
            'Pecahan Denom',
            'Keterangan',
        ];

        $headerCells = array_map(fn (string $h) => $this->makeTextSafeCell($h, $headerStyle), $headers);
        $headerRow = new Row($headerCells);
        $headerRow->setHeight(30);
        $writer->addRow($headerRow);

        $terminals = Terminal::with(['cabang', 'vendor'])
            ->orderBy('cabang_id')
            ->orderBy('urutan_cabang')
            ->orderBy('profil')
            ->get();

        if ($terminals->isNotEmpty()) {
            foreach ($terminals as $terminal) {
                $cabangLabel = $terminal->cabang?->label_cabang
                    ?? $terminal->cabang?->nama_cabang
                    ?? $terminal->cabang_text
                    ?? '';
                $vendorName = $terminal->vendor?->nama_vendor
                    ?? $terminal->vendor_text
                    ?? '';

                $rowCells = [
                    $this->makeTextSafeCell((string) ($terminal->profil ?? ''), $centerStyle),
                    $this->makeTextSafeCell((string) $cabangLabel, $leftStyle),
                    $this->makeTextSafeCell($terminal->urutan_cabang ?? '', $centerStyle),
                    $this->makeTextSafeCell((string) ($terminal->nama_lokasi ?? ''), $leftStyle),
                    $this->makeTextSafeCell((string) ($terminal->ip_address ?? ''), $centerStyle),
                    $this->makeTextSafeCell((string) ($terminal->luno ?? ''), $centerStyle),
                    $this->makeTextSafeCell((string) ($terminal->port ?? ''), $centerStyle),
                    $this->makeTextSafeCell((string) $vendorName, $leftStyle),
                    $this->makeTextSafeCell((string) ($terminal->serial_number ?? ''), $centerStyle),
                    $this->makeTextSafeCell((string) ($terminal->tipe_mesin ?? ''), $leftStyle),
                    $this->makeTextSafeCell((string) ($terminal->kategori ?? 'ATM'), $centerStyle),
                    $this->makeTextSafeCell((string) ($terminal->denom ?? ''), $centerStyle),
                    $this->makeTextSafeCell((string) ($terminal->keterangan ?? ''), $leftStyle),
                ];
                $dataRow = new Row($rowCells);
                $dataRow->setHeight(22);
                $writer->addRow($dataRow);
            }
        } else {
            // Sample Data Rows jika database belum memiliki data
            $samples = [
                [
                    'profil' => 'WCR.KCU1',
                    'cabang' => '001-Utama Palu',
                    'urutan' => 1,
                    'lokasi' => 'RS Undata Palu',
                    'ip' => '175.12.41.2',
                    'luno' => '0200',
                    'port' => '8220',
                    'vendor' => 'SRISHINDU INFORMATIKA',
                    'sn' => '56HG701702',
                    'tipe' => 'ATM WINCOR Pro Cash 480N',
                    'kategori' => 'ATM',
                    'denom' => '100',
                    'keterangan' => 'Unit RS Undata',
                ],
                [
                    'profil' => 'CRM.KCU1',
                    'cabang' => '001-Utama Palu',
                    'urutan' => 2,
                    'lokasi' => 'Bapenda Palu',
                    'ip' => '172.16.50.126',
                    'luno' => '0176',
                    'port' => '8191',
                    'vendor' => 'ASSINDO',
                    'sn' => 'B2010D00482',
                    'tipe' => 'CRM YIHUA',
                    'kategori' => 'CRM',
                    'denom' => '50/100',
                    'keterangan' => 'CRM Setor Tarik Tunai',
                ],
                [
                    'profil' => 'DBL.SAMS',
                    'cabang' => '001-Utama Palu',
                    'urutan' => 3,
                    'lokasi' => 'Kantor Samsat',
                    'ip' => '172.16.27.2',
                    'luno' => '0018',
                    'port' => '8018',
                    'vendor' => 'KOPERASI BANK SULTENG',
                    'sn' => '1522FDC20645',
                    'tipe' => 'ATM Diebold OPTEVA 522',
                    'kategori' => 'ATM',
                    'denom' => '100',
                    'keterangan' => 'Mesin Hibah',
                ],
            ];

            foreach ($samples as $sample) {
                $rowCells = [
                    $this->makeTextSafeCell($sample['profil'], $centerStyle),
                    $this->makeTextSafeCell($sample['cabang'], $leftStyle),
                    $this->makeTextSafeCell($sample['urutan'], $centerStyle),
                    $this->makeTextSafeCell($sample['lokasi'], $leftStyle),
                    $this->makeTextSafeCell($sample['ip'], $centerStyle),
                    $this->makeTextSafeCell($sample['luno'], $centerStyle),
                    $this->makeTextSafeCell($sample['port'], $centerStyle),
                    $this->makeTextSafeCell($sample['vendor'], $leftStyle),
                    $this->makeTextSafeCell($sample['sn'], $centerStyle),
                    $this->makeTextSafeCell($sample['tipe'], $leftStyle),
                    $this->makeTextSafeCell($sample['kategori'], $centerStyle),
                    $this->makeTextSafeCell($sample['denom'], $centerStyle),
                    $this->makeTextSafeCell($sample['keterangan'], $leftStyle),
                ];
                $dataRow = new Row($rowCells);
                $dataRow->setHeight(22);
                $writer->addRow($dataRow);
            }
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

    /**
     * OpenSpout menjadikan string berawalan "=" sebagai formula; paksa string menjadi teks
     * agar data dari input/import tidak dieksekusi sebagai formula di Excel.
     */
    protected function makeTextSafeCell(bool|DateInterval|DateTimeInterface|float|int|string|null $value, ?Style $style = null): Cell
    {
        return is_string($value) ? new StringCell($value, $style) : Cell::fromValue($value, $style);
    }
}
