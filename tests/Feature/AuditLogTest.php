<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\Login;
use App\Models\AuditLog;
use App\Models\Terminal;
use App\Models\Tiket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_changing_ticket_downtime_is_recorded_with_old_and_new_values(): void
    {
        $user = User::factory()->create();
        $terminal = Terminal::create([
            'profil' => 'WCR.KCU1',
            'nama_lokasi' => 'RS Undata Palu',
            'kategori' => 'ATM',
        ]);
        $tiket = Tiket::create([
            'terminal_id' => $terminal->id,
            'nomor_tiket' => 'TKT-AUDIT-001',
            'kategori_problem' => 'Mesin ATM',
            'permasalahan' => 'DISPENSER ERROR',
            'mulai' => '2026-09-01 08:00:00',
            'status' => 'Open',
        ]);

        $this->actingAs($user);
        $tiket->update(['mulai' => '2026-09-01 10:00:00']);

        $log = AuditLog::query()
            ->where('event', 'updated')
            ->where('auditable_type', $tiket->getMorphClass())
            ->where('auditable_id', $tiket->id)
            ->sole();

        $this->assertSame($user->id, $log->user_id);
        $this->assertStringStartsWith('2026-09-01 08:00:00', $log->old_values['mulai']);
        $this->assertStringStartsWith('2026-09-01 10:00:00', $log->new_values['mulai']);
    }

    public function test_password_change_is_recorded_without_storing_the_password(): void
    {
        $user = User::factory()->create();

        $user->update(['password' => 'Rahasia-Baru-123!']);

        $log = AuditLog::query()->where('event', 'updated')->where('auditable_id', $user->id)->sole();

        $this->assertSame('[disamarkan]', $log->new_values['password']);
        $this->assertSame('[disamarkan]', $log->old_values['password']);
    }

    public function test_failed_login_attempt_is_recorded(): void
    {
        $captcha = $this->fakeLoginCaptcha();

        Livewire::test(Login::class)
            ->fillForm([
                'username' => 'tidak-ada',
                'password' => 'salah',
                'captcha' => $captcha,
            ])
            ->call('authenticate');

        $log = AuditLog::query()->where('event', 'login_failed')->sole();

        $this->assertSame('tidak-ada', $log->new_values['username']);
    }

    public function test_audit_log_cannot_be_modified_or_deleted(): void
    {
        $log = AuditLog::record('login', newValues: ['username' => 'admin']);

        $log->update(['event' => 'diubah']);
        $log->delete();

        $this->assertDatabaseHas('audit_logs', ['id' => $log->id, 'event' => 'login']);
    }
}
