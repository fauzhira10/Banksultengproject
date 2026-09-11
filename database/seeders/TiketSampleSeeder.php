<?php

namespace Database\Seeders;

use App\Models\Cabang;
use App\Models\Terminal;
use App\Models\Tiket;
use Illuminate\Database\Seeder;

class TiketSampleSeeder extends Seeder
{
    public function run(): void
    {
        $samples = [
            [
                'nomor_tiket' => 'BST2412020246',
                'cabang_kode' => '006',
                'cabang_text' => '006-Salakan',
                'permasalahan' => 'DISPENSER EROR',
                'kategori_problem' => 'Mesin ATM',
                'mulai' => '2024-12-31 17:00:00',
                'selesai' => '2025-01-11 15:00:00',
                'status' => 'Closed',
                'deskripsi' => 'Dispenser macet pada saat nasabah melakukan penarikan.',
                'tindakan' => 'Penggantian modul pick up dispenser dan pengetesan transaksi normal.',
                'contact_person' => 'Dahlan',
                'phone_number' => '085241001234',
            ],
            [
                'nomor_tiket' => 'BST2412020245',
                'cabang_kode' => '401',
                'cabang_text' => '401-Kolonodale',
                'permasalahan' => 'DISPENSER EROR',
                'kategori_problem' => 'Mesin ATM',
                'mulai' => '2025-01-01 16:00:00',
                'selesai' => '2025-01-22 12:00:00',
                'status' => 'Closed',
                'deskripsi' => 'Error dispenser code 20000.',
                'tindakan' => 'Perbaikan sensor presentation dan kalibrasi kaset uang.',
                'contact_person' => 'Michael / Stevi Kokalinso',
                'phone_number' => '082196336263',
            ],
            [
                'nomor_tiket' => 'BST2412030247',
                'cabang_kode' => '101',
                'cabang_text' => '101-Donggala',
                'permasalahan' => 'DISPENSER EROR',
                'kategori_problem' => 'Mesin ATM',
                'mulai' => '2025-01-03 09:00:00',
                'selesai' => '2025-01-03 16:00:00',
                'status' => 'Closed',
                'deskripsi' => 'Dispenser jam.',
                'tindakan' => 'Clearing cash jam dan restart service ATM.',
                'contact_person' => 'Fahri',
                'phone_number' => '081341239876',
            ],
            [
                'nomor_tiket' => 'BST2412040249',
                'cabang_kode' => '102',
                'cabang_text' => '102-Parigi',
                'permasalahan' => 'DISPENSER EROR',
                'kategori_problem' => 'Mesin ATM',
                'mulai' => '2025-01-03 17:00:00',
                'selesai' => '2025-01-04 17:00:00',
                'status' => 'Closed',
                'deskripsi' => 'Dispenser reject bin penuh dan error transport.',
                'tindakan' => 'Reset dispenser dan maintenance berkala.',
                'contact_person' => 'Andi',
                'phone_number' => '082298761234',
            ],
            [
                'nomor_tiket' => '126800577',
                'cabang_kode' => '105',
                'cabang_text' => '105-Tolai',
                'permasalahan' => 'DISPENSER ERROR',
                'kategori_problem' => 'Mesin ATM',
                'mulai' => '2025-01-03 17:00:00',
                'selesai' => '2025-01-06 17:00:00',
                'status' => 'Closed',
                'deskripsi' => 'Dispenser hardware fault.',
                'tindakan' => 'Kunjungan teknisi vendor dan penggantian gear transport.',
                'contact_person' => 'Rahmat',
                'phone_number' => '085399887766',
            ],
            [
                'nomor_tiket' => 'BST2412060251',
                'cabang_kode' => '001',
                'cabang_text' => '001-Utama Palu',
                'permasalahan' => 'CARD READER ERROR',
                'kategori_problem' => 'Mesin ATM',
                'mulai' => '2025-01-09 08:00:00',
                'selesai' => '2025-01-09 12:00:00',
                'status' => 'Closed',
                'deskripsi' => 'Kartu tertelan dan card reader tidak merespon.',
                'tindakan' => 'Cleaning card reader head dan testing kartu normal.',
                'contact_person' => 'DIAZ',
                'phone_number' => '081245678901',
            ],
            [
                'nomor_tiket' => 'BST2412060253',
                'cabang_kode' => '003',
                'cabang_text' => '003-Poso',
                'permasalahan' => 'SOFTWARE CORRUPT',
                'kategori_problem' => 'System',
                'mulai' => '2025-01-05 18:00:00',
                'selesai' => '2025-01-09 10:00:00',
                'status' => 'Closed',
                'deskripsi' => 'Aplikasi ATM blue screen saat startup.',
                'tindakan' => 'Re-install aplikasi terminal dan patch update.',
                'contact_person' => 'Hendra',
                'phone_number' => '082190807060',
            ],
            [
                'nomor_tiket' => 'BST2501100001',
                'cabang_kode' => '001',
                'cabang_text' => '001-Utama Palu',
                'permasalahan' => 'DISPENSER ERROR',
                'kategori_problem' => 'Mesin ATM',
                'mulai' => now()->subHours(5)->toDateTimeString(),
                'selesai' => null,
                'status' => 'Open',
                'deskripsi' => 'Mohon untuk di openkan ticket perbaikan problem dispenser error pada mesin ATM Samsat Palu.',
                'tindakan' => 'Tiket telah diteruskan ke vendor PT. Srisindhu Informatika. Menunggu kedatangan teknisi di lokasi.',
                'contact_person' => 'DIAZ',
                'phone_number' => '081245678901',
            ],
            [
                'nomor_tiket' => 'BST2501100002',
                'cabang_kode' => '502',
                'cabang_text' => '502-Bahodopi',
                'permasalahan' => 'USB LAN RUSAK',
                'kategori_problem' => 'Jaringan',
                'mulai' => now()->subHours(2)->toDateTimeString(),
                'selesai' => null,
                'status' => 'Open',
                'deskripsi' => 'Koneksi jaringan ATM terputus, port USB LAN pada PC core rusak.',
                'tindakan' => 'Koordinasi dengan tim IT Support dan teknisi vendor untuk pergantian converter USB LAN.',
                'contact_person' => 'Irfan',
                'phone_number' => '082196336263',
            ],
        ];

        foreach ($samples as $item) {
            $cabang = Cabang::where('kode_cabang', $item['cabang_kode'])->first();
            $terminal = null;
            if ($cabang) {
                $terminal = Terminal::where('cabang_id', $cabang->id)->first();
            }

            if (! $terminal) {
                $terminal = Terminal::first();
            }

            if (! $terminal) {
                continue;
            }

            Tiket::updateOrCreate(
                ['nomor_tiket' => $item['nomor_tiket']],
                [
                    'terminal_id' => $terminal->id,
                    'cabang_id' => $cabang?->id ?? $terminal->cabang_id,
                    'cabang_text' => $item['cabang_text'],
                    'permasalahan' => $item['permasalahan'],
                    'kategori_problem' => $item['kategori_problem'],
                    'mulai' => $item['mulai'],
                    'selesai' => $item['selesai'],
                    'status' => $item['status'],
                    'deskripsi' => $item['deskripsi'],
                    'tindakan' => $item['tindakan'],
                    'contact_person' => $item['contact_person'],
                    'phone_number' => $item['phone_number'],
                ]
            );
        }
    }
}
