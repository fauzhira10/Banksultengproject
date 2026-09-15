<?php

namespace App\Services;

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

class TerminalTemplateService
{
    /**
     * Generate an Excel template for terminal imports.
     */
    public function generateTemplate(): BinaryFileResponse
    {
        $fileName = 'Template_Import_ATM_Bank_Sulteng.xlsx';
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

        $headerCells = array_map(fn (string $h) => Cell::fromValue($h, $headerStyle), $headers);
        $headerRow = new Row($headerCells);
        $headerRow->setHeight(30);
        $writer->addRow($headerRow);

        // Sample Data Rows
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
                'vendor' => 'ASSINDO',
                'sn' => '1522FDC20645',
                'tipe' => 'ATM Diebold OPTEVA 522',
                'kategori' => 'ATM',
                'denom' => '100',
                'keterangan' => 'Mesin Hibah',
            ],
        ];

        foreach ($samples as $sample) {
            $rowCells = [
                Cell::fromValue($sample['profil'], $centerStyle),
                Cell::fromValue($sample['cabang'], $leftStyle),
                Cell::fromValue($sample['urutan'], $centerStyle),
                Cell::fromValue($sample['lokasi'], $leftStyle),
                Cell::fromValue($sample['ip'], $centerStyle),
                Cell::fromValue($sample['luno'], $centerStyle),
                Cell::fromValue($sample['port'], $centerStyle),
                Cell::fromValue($sample['vendor'], $leftStyle),
                Cell::fromValue($sample['sn'], $centerStyle),
                Cell::fromValue($sample['tipe'], $leftStyle),
                Cell::fromValue($sample['kategori'], $centerStyle),
                Cell::fromValue($sample['denom'], $centerStyle),
                Cell::fromValue($sample['keterangan'], $leftStyle),
            ];
            $dataRow = new Row($rowCells);
            $dataRow->setHeight(22);
            $writer->addRow($dataRow);
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
}
