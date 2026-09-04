# Monitoring SLA ATM — Bank Sulteng

Aplikasi internal untuk memantau **SLA (Service Level Agreement) gangguan mesin ATM**: pendataan terminal ATM, vendor, cabang, kategori/jenis masalah, serta tiket gangguan beserta durasi penanganannya.

Dibangun dengan **Laravel 13** + **Filament 5** sebagai panel admin.

> **Status: tahap awal pengembangan.** Kerangka aplikasi (skeleton) sudah jalan, tetapi domain bisnisnya belum lengkap. Lihat bagian [Status & Keterbatasan Saat Ini](#status--keterbatasan-saat-ini) sebelum mulai — ada beberapa hal yang **wajib** diselesaikan agar aplikasi bisa dipakai penuh.

---

## Daftar Isi

- [Tech Stack](#tech-stack)
- [Prasyarat](#prasyarat)
- [Instalasi Pertama Kali](#instalasi-pertama-kali)
- [Konfigurasi Environment](#konfigurasi-environment)
- [Menjalankan Aplikasi](#menjalankan-aplikasi)
- [Membuat User Admin](#membuat-user-admin)
- [Struktur Proyek](#struktur-proyek)
- [Skema Database](#skema-database)
- [Testing](#testing)
- [Code Style](#code-style)
- [Status & Keterbatasan Saat Ini](#status--keterbatasan-saat-ini)
- [Troubleshooting](#troubleshooting)

---

## Tech Stack

| Komponen | Versi | Keterangan |
| --- | --- | --- |
| PHP | ^8.3 | Diuji dengan PHP 8.3.16 |
| Laravel Framework | ^13.17 | Terpasang: 13.30.1 |
| Filament | ^5.0 | Panel admin, terpasang: 5.7.8 |
| MySQL / MariaDB | 8.0+ / 10.6+ | Koneksi default proyek ini |
| Node.js | 20+ | Diuji dengan Node 22.17.0 |
| Vite | ^8.0 | Build tool frontend |
| Tailwind CSS | ^4.0 | Via `@tailwindcss/vite` |
| PHPUnit | ^12.5 | Test runner |
| Laravel Pint | ^1.27 | Code formatter (dev) |

---

## Prasyarat

Pastikan hal-hal berikut sudah terpasang di mesin Anda **sebelum** clone:

### 1. PHP 8.3 atau lebih baru

Cek versi:

```bash
php -v
```

**Ekstensi PHP yang wajib aktif:**

```
pdo_mysql   mbstring   openssl   fileinfo
curl        zip        bcmath    intl        gd
```

Cek ekstensi yang sudah aktif:

```bash
php -m
```

> Pengguna **Laragon / XAMPP** di Windows: sebagian besar ekstensi ini sudah aktif secara default. Jika ada yang kurang, hilangkan tanda `;` pada baris `extension=...` yang bersangkutan di `php.ini`, lalu restart service.

### 2. Composer 2.x

```bash
composer -V
```

Unduh di [getcomposer.org](https://getcomposer.org/download/) bila belum ada.

### 3. Node.js 20+ dan npm

```bash
node -v
npm -v
```

Unduh di [nodejs.org](https://nodejs.org/).

### 4. MySQL 8.0+ atau MariaDB 10.6+

Server database harus sudah berjalan dan Anda punya akses untuk membuat database & user.

### 5. Git

```bash
git --version
```

---

## Instalasi Pertama Kali

### Langkah 1 — Clone repository

```bash
git clone <URL-REPOSITORY-INI>
cd Banksultengproject
```

### Langkah 2 — Siapkan database MySQL

Masuk ke MySQL sebagai root, lalu jalankan:

```sql
CREATE DATABASE monitoring_atm
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE USER 'atm_app'@'127.0.0.1' IDENTIFIED BY 'PASSWORD_ANDA';
GRANT ALL PRIVILEGES ON monitoring_atm.* TO 'atm_app'@'127.0.0.1';
FLUSH PRIVILEGES;
```

Ganti `PASSWORD_ANDA` dengan password pilihan Anda sendiri — nilai ini nanti dipakai di `.env`.

### Langkah 3 — Install dependency PHP

```bash
composer install
```

### Langkah 4 — Siapkan file `.env`

```bash
cp .env.example .env
```

Di Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Lalu **edit `.env`** sesuai bagian [Konfigurasi Environment](#konfigurasi-environment) di bawah. Ini langkah paling penting — `.env.example` masih berisi nilai bawaan Laravel (SQLite), sedangkan proyek ini memakai MySQL.

### Langkah 5 — Generate application key

```bash
php artisan key:generate
```

### Langkah 6 — Jalankan migrasi database

```bash
php artisan migrate
```

> ⚠️ **Saat ini perintah ini akan gagal** pada migrasi `create_tikets_table`. Baca [Status & Keterbatasan Saat Ini](#status--keterbatasan-saat-ini) untuk penjelasan dan solusinya.

### Langkah 7 — Install dependency & build frontend

```bash
npm install
npm run build
```

> `.npmrc` proyek ini menyetel `ignore-scripts=true` sebagai pengamanan, jadi post-install script paket tidak dijalankan otomatis. Ini normal.

### Langkah 8 — Buat user admin

Lihat bagian [Membuat User Admin](#membuat-user-admin).

---

### Jalan pintas (opsional)

Setelah `.env` dikonfigurasi dan database dibuat, langkah 3–7 bisa diringkas menjadi:

```bash
composer run setup
```

Script ini menjalankan: `composer install` → salin `.env` → `key:generate` → `migrate --force` → `npm install` → `npm run build`.

---

## Konfigurasi Environment

Buka file `.env` dan sesuaikan nilai-nilai berikut:

```dotenv
APP_NAME="Monitoring SLA ATM"
APP_ENV=local
APP_KEY=                      # diisi otomatis oleh `php artisan key:generate`
APP_DEBUG=true                # WAJIB false di production
APP_URL=http://localhost:8000
APP_TIMEZONE=Asia/Makassar    # WITA — sesuai zona waktu Sulawesi Tengah
APP_LOCALE=id
APP_FALLBACK_LOCALE=en

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=monitoring_atm
DB_USERNAME=atm_app
DB_PASSWORD=PASSWORD_ANDA     # samakan dengan yang dibuat di Langkah 2

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

**Catatan penting:**

- `SESSION_DRIVER`, `QUEUE_CONNECTION`, dan `CACHE_STORE` semuanya memakai `database`. Tabel pendukungnya (`sessions`, `jobs`, `cache`) dibuat oleh migrasi bawaan Laravel, jadi **`php artisan migrate` harus berhasil** sebelum aplikasi bisa diakses.
- `APP_TIMEZONE=Asia/Makassar` penting untuk perhitungan durasi SLA agar sesuai waktu setempat (WITA).
- File `.env` **tidak** ikut ter-commit (sudah masuk `.gitignore`). Jangan pernah commit kredensial ke repository.

---

## Menjalankan Aplikasi

### Mode pengembangan (direkomendasikan)

```bash
composer run dev
```

Perintah ini menjalankan **tiga proses sekaligus** dalam satu terminal:

| Proses | Perintah | Fungsi |
| --- | --- | --- |
| `server` | `php artisan serve` | Web server di `http://localhost:8000` |
| `queue` | `php artisan queue:listen --tries=1 --timeout=0` | Worker antrian |
| `vite` | `npm run dev` | Dev server frontend + hot reload |

Hentikan semuanya dengan `Ctrl+C`.

### Mode manual (terminal terpisah)

```bash
# Terminal 1
php artisan serve

# Terminal 2
npm run dev
```

### Akses aplikasi

| URL | Keterangan |
| --- | --- |
| `http://localhost:8000` | Halaman depan (masih halaman bawaan Laravel) |
| `http://localhost:8000/admin` | **Panel admin Filament** — pintu masuk utama aplikasi |
| `http://localhost:8000/admin/login` | Halaman login |
| `http://localhost:8000/up` | Health check |

### Melihat log secara realtime

```bash
php artisan pail
```

---

## Membuat User Admin

Panel `/admin` butuh akun untuk login. Pilih salah satu cara:

### Cara 1 — Perintah Filament (interaktif, direkomendasikan)

```bash
php artisan make:filament-user
```

Anda akan diminta memasukkan nama, email, dan password.

### Cara 2 — Seeder

```bash
php artisan db:seed
```

Membuat satu akun uji:

- **Email:** `test@example.com`
- **Password:** `password`

> Akun seeder ini hanya untuk pengembangan lokal. **Jangan** dipakai di lingkungan production.

---

## Struktur Proyek

```
app/
├── Filament/
│   └── Resources/
│       ├── Terminals/          # CRUD terminal ATM
│       │   ├── Pages/          # List, Create, Edit, View
│       │   ├── Schemas/        # Definisi form & infolist
│       │   ├── Tables/         # Definisi kolom & aksi tabel
│       │   └── TerminalResource.php
│       └── Vendors/            # CRUD vendor (struktur sama)
├── Http/Controllers/
├── Models/
│   └── User.php
└── Providers/
    ├── AppServiceProvider.php
    └── Filament/
        └── AdminPanelProvider.php   # Konfigurasi panel: path /admin, warna, middleware

database/
├── factories/
├── migrations/
└── seeders/

resources/
├── css/app.css                 # Entry Tailwind CSS v4
├── js/app.js
└── views/welcome.blade.php

routes/
├── web.php                     # Hanya route "/" — sisanya diurus Filament
└── console.php
```

### Cara kerja panel admin

`AdminPanelProvider` melakukan **auto-discovery**: semua class di `app/Filament/Resources`, `app/Filament/Pages`, dan `app/Filament/Widgets` otomatis terdaftar tanpa perlu registrasi manual. Jadi untuk menambah menu baru, cukup jalankan:

```bash
php artisan make:filament-resource NamaModel --generate
```

---

## Skema Database

### Tabel domain

| Tabel | Fungsi | Status kolom |
| --- | --- | --- |
| `terminals` | Data mesin ATM | `profil` (unik), `serial_number` (unik, nullable), `ip_adress` (unik, nullable) |
| `vendors` | Vendor penyedia/pemelihara ATM | ⚠️ baru `id` + `timestamps` |
| `cabangs` | Cabang bank pemilik ATM | ⚠️ baru `id` + `timestamps` |
| `kategori_masalahs` | Kategori gangguan | ⚠️ baru `id` + `timestamps` |
| `jenis_masalahs` | Jenis gangguan (turunan kategori) | ⚠️ baru `id` + `timestamps` |
| `tikets` | Tiket gangguan + perhitungan SLA | ⚠️ punya `softDeletes` & index, tapi kolom datanya belum ada |

### Tabel bawaan Laravel

`users`, `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`

### Relasi yang direncanakan

Berdasarkan penamaan dan index yang sudah ditulis, alur datanya:

```
cabangs ──< terminals >── vendors
                │
                └──< tikets >── jenis_masalahs >── kategori_masalahs
```

Index pada `tikets` (`terminal_id`, `mulai`, `selesai`, dan `status`) menunjukkan bahwa durasi SLA dihitung dari selisih waktu `mulai` → `selesai` per terminal.

---

## Testing

```bash
# Seluruh test suite
php artisan test

# Satu file
php artisan test tests/Feature/ExampleTest.php

# Satu test berdasarkan nama
php artisan test --filter=nama_test

# Output ringkas
php artisan test --compact

# Lewat PHPUnit langsung
vendor/bin/phpunit
```

Test berjalan di atas **SQLite in-memory** (diatur di `phpunit.xml`), jadi tidak menyentuh database MySQL Anda dan tidak butuh konfigurasi tambahan.

---

## Code Style

Proyek ini memakai **Laravel Pint**. Jalankan sebelum commit:

```bash
# Format hanya file yang berubah
vendor/bin/pint --dirty

# Format seluruh proyek
vendor/bin/pint
```

---

## Status & Keterbatasan Saat Ini

Bagian ini penting bagi siapa pun yang baru clone. Tiga hal berikut membuat aplikasi **belum berjalan penuh**:

### 1. 🔴 Migrasi `tikets` gagal dijalankan

`database/migrations/2026_09_03_061641_create_tikets_table.php` membuat index pada kolom yang belum didefinisikan:

```php
$table->index(['terminal_id', 'mulai', 'selesai']);
$table->index('status');
```

Ketiga kolom `terminal_id`, `mulai`, `selesai`, dan `status` belum pernah dibuat, sehingga MySQL menolak dengan error semacam:

```
SQLSTATE[42000]: Key column 'terminal_id' doesn't exist in table
```

**Akibatnya `php artisan migrate` berhenti di tengah jalan** — lima migrasi sebelumnya sudah masuk, `tikets` gagal.

**Solusi:** tambahkan definisi kolomnya lebih dulu di migrasi tersebut, misalnya:

```php
$table->foreignId('terminal_id')->constrained();
$table->timestamp('mulai');
$table->timestamp('selesai')->nullable();
$table->string('status', 30);
```

Lalu jalankan ulang `php artisan migrate`.

**Workaround sementara** agar bisa lanjut mengembangkan bagian lain — komentari dua baris `index()` tersebut, atau jalankan migrasi sampai sebelum `tikets` saja.

### 2. 🔴 Model `Terminal` dan `Vendor` belum dibuat

`app/Filament/Resources/Terminals/TerminalResource.php` dan `VendorResource.php` mereferensikan `App\Models\Terminal` dan `App\Models\Vendor`, tetapi `app/Models/` baru berisi `User.php`.

Route `/admin/terminals` dan `/admin/vendors` **memang terdaftar**, namun akan melempar `Class "App\Models\Terminal" not found` begitu dibuka.

**Solusi:**

```bash
php artisan make:model Terminal
php artisan make:model Vendor
```

Kedua resource-nya juga memakai `SoftDeletingScope` dan `TrashedFilter`, jadi model perlu `use SoftDeletes` **dan** migrasi tabelnya perlu `$table->softDeletes()` — saat ini `terminals` dan `vendors` belum punya kolom `deleted_at`.

### 3. 🟡 Form, tabel, dan infolist masih kosong

Semua class di `Schemas/` dan `Tables/` masih berisi array kosong (`//`), sehingga halaman CRUD tampil tanpa field maupun kolom apa pun. Perlu diisi setelah model dan skema tabelnya final.

### Ringkasan yang sudah berjalan

- ✅ Kerangka Laravel 13 + Filament 5, panel `/admin` beserta login
- ✅ Koneksi MySQL, session/queue/cache berbasis database
- ✅ Build frontend Vite + Tailwind v4
- ✅ Test suite (SQLite in-memory)
- ✅ Migrasi `terminals` dengan kolom awal
- ⬜ Model & relasi domain
- ⬜ Skema kolom untuk `vendors`, `cabangs`, `kategori_masalahs`, `jenis_masalahs`, `tikets`
- ⬜ Form/tabel Filament
- ⬜ Logika perhitungan SLA
- ⬜ Manajemen role & permission

---

## Troubleshooting

| Masalah | Penyebab & Solusi |
| --- | --- |
| `Unable to locate file in Vite manifest` | Aset frontend belum di-build. Jalankan `npm run build`, atau `npm run dev` bila sedang mengembangkan. |
| `SQLSTATE[HY000] [1045] Access denied for user` | Kredensial `DB_USERNAME` / `DB_PASSWORD` di `.env` tidak cocok. Cek ulang user MySQL yang dibuat di Langkah 2. |
| `SQLSTATE[HY000] [2002] Connection refused` | Service MySQL belum jalan, atau `DB_HOST` / `DB_PORT` salah. |
| `SQLSTATE[42000] Key column '...' doesn't exist` | Bug migrasi `tikets` — lihat [poin 1](#1--migrasi-tikets-gagal-dijalankan) di atas. |
| `Class "App\Models\Terminal" not found` | Model belum dibuat — lihat [poin 2](#2--model-terminal-dan-vendor-belum-dibuat) di atas. |
| `No application encryption key has been specified` | Jalankan `php artisan key:generate`. |
| `Table 'monitoring_atm.sessions' doesn't exist` | Migrasi bawaan belum jalan. Jalankan `php artisan migrate`. |
| Perubahan `.env` tidak terbaca | Bersihkan cache konfigurasi: `php artisan config:clear`. |
| Perubahan Filament tidak muncul | `php artisan filament:optimize-clear` lalu `php artisan optimize:clear`. |
| Halaman blank / error 500 tak jelas | Cek `storage/logs/laravel.log`, atau pantau realtime dengan `php artisan pail`. |

### Reset penuh (hati-hati — semua data hilang)

```bash
php artisan migrate:fresh --seed
php artisan optimize:clear
```

---

## Perintah Cepat

```bash
composer run setup      # Instalasi sekali jalan
composer run dev        # Jalankan server + queue + vite
composer run test       # Bersihkan config lalu jalankan test

php artisan serve       # Web server saja
php artisan migrate     # Jalankan migrasi
php artisan pail        # Tail log realtime
php artisan route:list  # Daftar seluruh route
php artisan optimize:clear  # Bersihkan semua cache

npm run dev             # Vite dev server
npm run build           # Build aset produksi

vendor/bin/pint --dirty # Format kode yang berubah
```
