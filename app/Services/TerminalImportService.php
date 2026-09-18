<?php

namespace App\Services;

use App\Models\Cabang;
use App\Models\Terminal;
use App\Models\Vendor;
use Illuminate\Support\Collection;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\ODS\Reader as OdsReader;
use OpenSpout\Reader\ReaderInterface;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;

class TerminalImportService
{
    /**
     * Jumlah maksimal baris data yang diproses dalam satu kali import.
     */
    public const MAX_ROWS = 5000;

    /**
     * Map header aliases to target database fields.
     *
     * @var array<string, array<int, string>>
     */
    protected array $columnAliases = [
        'profil' => ['profil', 'kode profil', 'kode_profil', 'id terminal', 'atm profil', 'terminal profil', 'atm_id', 'profile'],
        'nama_lokasi' => ['nama lokasi', 'nama_lokasi', 'lokasi', 'lokasi fisik', 'nama tempat', 'location'],
        'cabang' => ['cabang', 'kode cabang', 'kode_cabang', 'nama cabang', 'nama_cabang', 'cabang pengelola', 'branch'],
        'urutan_cabang' => ['urutan', 'urutan cabang', 'urutan_cabang', 'no urut', 'unit ke', 'nomor urut'],
        'ip_address' => ['ip address', 'ip_address', 'ip add', 'ip', 'alamat ip', 'ip atm'],
        'luno' => ['luno', 'id/luno', 'id / luno', 'id luno', 'atm luno', 'luno id', 'id'],
        'port' => ['port', 'port switch', 'switch port'],
        'vendor' => ['vendor', 'vendor maintenance', 'nama vendor', 'nama_vendor', 'vendor_id', 'vendor atm'],
        'serial_number' => ['serial number', 'serial_number', 'sn', 's/n', 'no seri', 'nomor seri'],
        'tipe_mesin' => ['tipe', 'tipe mesin', 'tipe_mesin', 'merek', 'merk', 'model', 'type'],
        'kategori' => ['kategori', 'kategori mesin', 'jenis', 'jenis mesin', 'category'],
        'denom' => ['denom', 'denominasi', 'pecahan', 'pecahan uang'],
        'is_hibah' => ['hibah', 'is hibah', 'is_hibah', 'mesin hibah', 'status hibah'],
        'keterangan' => ['keterangan', 'ket', 'catatan', 'notes', 'remark', 'remarks'],
    ];

    /**
     * Cache Cabang records in memory.
     *
     * @var Collection<int, Cabang>
     */
    protected Collection $cabangs;

    /**
     * Cache Vendor records in memory.
     *
     * @var Collection<int, Vendor>
     */
    protected Collection $vendors;

    public function __construct()
    {
        $this->refreshCaches();
    }

    /**
     * Refresh in-memory caches of Cabang and Vendor.
     */
    public function refreshCaches(): void
    {
        $this->cabangs = Cabang::all();
        $this->vendors = Vendor::all();
    }

    /**
     * Import terminal records from an Excel or CSV file.
     *
     * @return array{total: int, created: int, updated: int, skipped: int, errors: array<int, string>}
     */
    public function import(string $filePath, bool $updateExisting = true, bool $autoCreateRelations = true): array
    {
        $reader = $this->createReader($filePath);
        $reader->open($filePath);

        $stats = [
            'total' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        $headerMap = null;
        $rowNumber = 0;

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rowNumber++;

                if ($row->isEmpty()) {
                    continue;
                }

                $cells = $row->toArray();

                // Detect header row by checking for key identifiers
                if ($headerMap === null) {
                    $potentialMap = $this->detectHeaderMap($cells);
                    if ($potentialMap !== null) {
                        $headerMap = $potentialMap;

                        continue;
                    }

                    // Skip rows before header
                    continue;
                }

                $rowData = $this->extractRowData($cells, $headerMap);

                // Skip if entire extracted row is empty
                if ($this->isRowDataEmpty($rowData)) {
                    continue;
                }

                if ($stats['total'] >= self::MAX_ROWS) {
                    $stats['errors'][$rowNumber] = 'Batas maksimal '.number_format(self::MAX_ROWS, 0, ',', '.')." baris tercapai; baris {$rowNumber} dan seterusnya tidak diproses.";

                    break;
                }

                $stats['total']++;

                // Validate mandatory fields
                $profil = trim((string) ($rowData['profil'] ?? ''));
                $namaLokasi = trim((string) ($rowData['nama_lokasi'] ?? ''));

                if ($profil === '') {
                    $stats['errors'][$rowNumber] = "Baris {$rowNumber}: Kode Profil ATM wajib diisi.";

                    continue;
                }

                if ($namaLokasi === '') {
                    $stats['errors'][$rowNumber] = "Baris {$rowNumber}: Nama Lokasi Fisik wajib diisi untuk profil '{$profil}'.";

                    continue;
                }

                try {
                    $recordData = $this->transformRowToAttributes($rowData, $autoCreateRelations);

                    $existing = Terminal::withTrashed()->where('profil', $profil)->first();

                    if ($existing) {
                        if ($updateExisting) {
                            if ($existing->trashed()) {
                                $existing->restore();
                            }
                            $existing->update($recordData);
                            $stats['updated']++;
                        } else {
                            $stats['skipped']++;
                        }
                    } else {
                        Terminal::create($recordData);
                        $stats['created']++;
                    }
                } catch (\Throwable $e) {
                    report($e);

                    $stats['errors'][$rowNumber] = "Baris {$rowNumber} (Profil {$profil}): data tidak dapat disimpan, periksa kembali isian baris ini.";
                }
            }

            // Only process the first sheet
            break;
        }

        $reader->close();

        return $stats;
    }

    /**
     * Create appropriate OpenSpout reader by file extension.
     */
    protected function createReader(string $filePath): ReaderInterface
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        return match ($extension) {
            'csv' => new CsvReader,
            'ods' => new OdsReader,
            default => new XlsxReader,
        };
    }

    /**
     * Detect header row and map column index to target field.
     *
     * @param  array<int, mixed>  $cellValues
     * @return array<int, string>|null
     */
    protected function detectHeaderMap(array $cellValues): ?array
    {
        $map = [];
        $matchedCount = 0;

        foreach ($cellValues as $index => $value) {
            if ($value === null) {
                continue;
            }

            $normalized = $this->normalizeString((string) $value);

            foreach ($this->columnAliases as $field => $aliases) {
                if (in_array($normalized, $aliases, true)) {
                    $map[$index] = $field;
                    $matchedCount++;
                    break;
                }
            }
        }

        // Must at least match 'profil' or 'nama_lokasi' to be considered a header row
        if ($matchedCount >= 2 || in_array('profil', $map, true)) {
            return $map;
        }

        return null;
    }

    /**
     * Extract key-value data using the header map.
     *
     * @param  array<int, mixed>  $cellValues
     * @param  array<int, string>  $headerMap
     * @return array<string, mixed>
     */
    protected function extractRowData(array $cellValues, array $headerMap): array
    {
        $rowData = [];

        foreach ($headerMap as $index => $field) {
            $rowData[$field] = $cellValues[$index] ?? null;
        }

        return $rowData;
    }

    /**
     * Check if extracted row has no meaningful content.
     *
     * @param  array<string, mixed>  $rowData
     */
    protected function isRowDataEmpty(array $rowData): bool
    {
        foreach ($rowData as $val) {
            if ($val !== null && trim((string) $val) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * Transform raw Excel row values into Terminal model attributes.
     *
     * @param  array<string, mixed>  $rowData
     * @return array<string, mixed>
     */
    protected function transformRowToAttributes(array $rowData, bool $autoCreateRelations): array
    {
        $profil = trim((string) ($rowData['profil'] ?? ''));
        $tipeMesin = trim((string) ($rowData['tipe_mesin'] ?? ''));
        $rawVendor = trim((string) ($rowData['vendor'] ?? ''));
        $rawCabang = trim((string) ($rowData['cabang'] ?? ''));

        // 1. Resolve Kategori (ATM vs CRM)
        $kategori = strtoupper(trim((string) ($rowData['kategori'] ?? '')));
        if (! in_array($kategori, ['ATM', 'CRM'], true)) {
            if (str_contains(strtoupper($profil), 'CRM') || str_contains(strtoupper($tipeMesin), 'CRM')) {
                $kategori = 'CRM';
            } else {
                $kategori = 'ATM';
            }
        }

        // 2. Resolve Denom
        $denom = trim((string) ($rowData['denom'] ?? ''));
        if ($denom === '') {
            $denom = ($kategori === 'CRM') ? '50/100' : '100';
        } else {
            $denomClean = str_replace(['.', ',', ' ', 'Rp', 'rp'], '', $denom);
            if ($denomClean === '100000' || $denomClean === '100') {
                $denom = '100';
            } elseif ($denomClean === '50000' || $denomClean === '50') {
                $denom = '50';
            } elseif (str_contains($denom, '50') && str_contains($denom, '100')) {
                $denom = '50/100';
            }
        }

        // 3. Resolve Is Hibah
        $isHibah = false;
        if (isset($rowData['is_hibah']) && $rowData['is_hibah'] !== null && $rowData['is_hibah'] !== '') {
            $rawHibah = strtolower(trim((string) $rowData['is_hibah']));
            $isHibah = in_array($rawHibah, ['1', 'true', 'ya', 'yes', 'hibah'], true);
        } else {
            if (str_contains(strtoupper($rawVendor), 'HIBAH') || str_contains(strtoupper($tipeMesin), '522')) {
                $isHibah = true;
            }
        }

        // 4. Resolve Cabang & Vendor
        [$cabangId, $cabangText] = $this->resolveCabang($rawCabang, $autoCreateRelations);
        [$vendorId, $vendorText] = $isHibah
            ? $this->resolveVendor('KOPERASI BANK SULTENG', $autoCreateRelations)
            : $this->resolveVendor($rawVendor, $autoCreateRelations);

        // 6. Urutan Cabang
        $urutanCabang = null;
        if (isset($rowData['urutan_cabang']) && is_numeric($rowData['urutan_cabang'])) {
            $urutanCabang = (int) $rowData['urutan_cabang'];
        }

        // 7. LUNO & Port formatting
        $luno = null;
        if (isset($rowData['luno']) && trim((string) $rowData['luno']) !== '') {
            $luno = trim((string) $rowData['luno']);
            // If numeric and less than 4 digits, keep original or pad if needed (standard 4 digits)
            if (is_numeric($luno) && strlen($luno) < 4) {
                $luno = str_pad($luno, 4, '0', STR_PAD_LEFT);
            }
        }

        $port = null;
        if (isset($rowData['port']) && trim((string) $rowData['port']) !== '') {
            $port = trim((string) $rowData['port']);
        }

        // 8. IP Address formatting
        $ipAddress = null;
        if (isset($rowData['ip_address']) && trim((string) $rowData['ip_address']) !== '') {
            $ipAddress = preg_replace('/\s+/', '', (string) $rowData['ip_address']);
        }

        return [
            'profil' => $profil,
            'nama_lokasi' => trim((string) ($rowData['nama_lokasi'] ?? '')),
            'cabang_id' => $cabangId,
            'cabang_text' => $cabangText,
            'urutan_cabang' => $urutanCabang,
            'ip_address' => $ipAddress,
            'luno' => $luno,
            'port' => $port,
            'vendor_id' => $vendorId,
            'vendor_text' => $vendorText,
            'serial_number' => trim((string) ($rowData['serial_number'] ?? '')) ?: null,
            'denom' => $denom,
            'tipe_mesin' => $tipeMesin ?: null,
            'kategori' => $kategori,
            'is_hibah' => $isHibah,
            'keterangan' => trim((string) ($rowData['keterangan'] ?? '')) ?: null,
        ];
    }

    /**
     * Resolve Cabang ID and text representation.
     *
     * @return array{0: int|null, 1: string|null}
     */
    protected function resolveCabang(string $rawCabang, bool $autoCreate): array
    {
        if ($rawCabang === '') {
            return [null, null];
        }

        $clean = trim($rawCabang);

        // 1. Try matching by kode_cabang (e.g., "001", "101")
        if (preg_match('/^(\d{3})\b/', $clean, $matches)) {
            $kode = $matches[1];
            $cabang = $this->cabangs->firstWhere('kode_cabang', $kode);
            if ($cabang) {
                return [$cabang->id, $cabang->label_cabang ?? $clean];
            }
        }

        // 2. Try exact or case-insensitive matching by label_cabang
        $cabang = $this->cabangs->first(function (Cabang $c) use ($clean) {
            return strcasecmp($c->label_cabang ?? '', $clean) === 0
                || strcasecmp($c->kode_cabang, $clean) === 0
                || strcasecmp($c->nama_cabang, $clean) === 0;
        });

        if ($cabang) {
            return [$cabang->id, $cabang->label_cabang ?? $clean];
        }

        // 3. Try partial name matching (e.g. "Utama Palu" in "001-Utama Palu")
        $cabang = $this->cabangs->first(function (Cabang $c) use ($clean) {
            return str_contains(strtolower($clean), strtolower($c->nama_cabang))
                || str_contains(strtolower($c->nama_cabang), strtolower($clean));
        });

        if ($cabang) {
            return [$cabang->id, $cabang->label_cabang ?? $clean];
        }

        // 4. Auto-create if enabled
        if ($autoCreate) {
            $kode = preg_match('/^(\d+)/', $clean, $m) ? $m[1] : substr(strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $clean)), 0, 5);
            if (strlen($kode) < 3) {
                $kode = str_pad($kode, 3, '0', STR_PAD_LEFT);
            }

            // Ensure unique code
            $baseKode = $kode;
            $counter = 1;
            while ($this->cabangs->contains('kode_cabang', $kode)) {
                $kode = $baseKode.'-'.$counter;
                $counter++;
            }

            $nama = trim(preg_replace('/^\d+[\s\-_]*/', '', $clean));
            if ($nama === '') {
                $nama = $clean;
            }

            $newCabang = Cabang::create([
                'kode_cabang' => $kode,
                'nama_cabang' => $nama,
                'label_cabang' => "{$kode}-{$nama}",
                'urutan' => $this->cabangs->count() + 1,
            ]);

            $this->cabangs->push($newCabang);

            return [$newCabang->id, $newCabang->label_cabang];
        }

        return [null, $clean];
    }

    /**
     * Resolve Vendor ID and text representation.
     *
     * @return array{0: int|null, 1: string|null}
     */
    protected function resolveVendor(string $rawVendor, bool $autoCreate): array
    {
        if ($rawVendor === '') {
            return [null, null];
        }

        $clean = trim($rawVendor);

        if (str_contains(strtoupper($clean), 'HIBAH')) {
            $clean = 'KOPERASI BANK SULTENG';
        }

        $vendor = $this->vendors->first(function (Vendor $v) use ($clean) {
            return strcasecmp($v->nama_vendor, $clean) === 0;
        });

        if ($vendor) {
            return [$vendor->id, $vendor->nama_vendor];
        }

        if ($autoCreate) {
            $newVendor = Vendor::create([
                'nama_vendor' => strtoupper($clean),
            ]);

            $this->vendors->push($newVendor);

            return [$newVendor->id, $newVendor->nama_vendor];
        }

        return [null, $clean];
    }

    /**
     * Normalize string for header comparison.
     */
    protected function normalizeString(string $value): string
    {
        $value = strtolower(trim($value));
        $value = str_replace(['_', '-', '/'], ' ', $value);

        return preg_replace('/\s+/', ' ', $value) ?? '';
    }
}
