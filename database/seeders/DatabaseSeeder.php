<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->seedAdministrator();

        $this->call([
            TerminalSeeder::class,
        ]);
    }

    /**
     * Membuat akun administrator awal bila belum ada. Kata sandi akun yang sudah ada
     * tidak pernah ditimpa. Kata sandi diambil dari `ADMIN_INITIAL_PASSWORD`, atau dibuat
     * acak dan ditampilkan satu kali bila variabel tersebut kosong.
     */
    protected function seedAdministrator(): void
    {
        $email = 'admin@banksulteng.co.id';

        if (User::query()->where('username', 'admin')->orWhere('email', $email)->exists()) {
            return;
        }

        $password = config('auth.initial_admin_password');
        $isGeneratedPassword = blank($password);

        if ($isGeneratedPassword) {
            $password = Str::password(16);
        }

        (new User([
            'name' => 'Administrator Bank Sulteng',
            'username' => 'admin',
            'email' => $email,
            'password' => $password,
        ]))->forceFill(['role' => UserRole::Admin])->save();

        if ($isGeneratedPassword) {
            $this->command?->warn("Akun admin dibuat dengan kata sandi acak: {$password}");
            $this->command?->warn('Simpan kata sandi ini sekarang; kata sandi tidak akan ditampilkan lagi.');
        }
    }
}
