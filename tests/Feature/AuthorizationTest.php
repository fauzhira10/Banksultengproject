<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\Login;
use App\Filament\Resources\Terminals\Pages\ListTerminals;
use App\Filament\Resources\Tikets\Pages\ListTikets;
use App\Models\Terminal;
use App\Models\Tiket;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_user_cannot_access_panel(): void
    {
        $user = User::factory()->inactive()->create();

        $this->actingAs($user)->get('/admin/tikets')->assertForbidden();
    }

    public function test_user_without_active_flag_is_denied_instead_of_erroring(): void
    {
        $user = User::factory()->make();
        $user->setRawAttributes(['username' => 'tanpa-flag']);

        $this->assertFalse($user->canAccessPanel(Filament::getPanel('admin')));
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        User::factory()->inactive()->create([
            'username' => 'nonaktif',
            'password' => bcrypt('password123'),
        ]);

        $captcha = $this->fakeLoginCaptcha();

        Livewire::test(Login::class)
            ->fillForm([
                'username' => 'nonaktif',
                'password' => 'password123',
                'captcha' => $captcha,
            ])
            ->call('authenticate')
            ->assertHasFormErrors(['username']);

        $this->assertGuest();
    }

    public function test_viewer_can_view_data_but_cannot_create_records(): void
    {
        $viewer = User::factory()->viewer()->create();

        $this->actingAs($viewer)->get('/admin/tikets')->assertSuccessful();
        $this->actingAs($viewer)->get('/admin/laporan-sla-bulanan')->assertSuccessful();
        $this->actingAs($viewer)->get('/admin/tikets/create')->assertForbidden();
        $this->actingAs($viewer)->get('/admin/terminals/create')->assertForbidden();
    }

    public function test_operator_can_manage_tickets_but_not_master_data(): void
    {
        $operator = User::factory()->create();

        $this->actingAs($operator)->get('/admin/tikets/create')->assertSuccessful();
        $this->actingAs($operator)->get('/admin/terminals/create')->assertSuccessful();
        $this->actingAs($operator)->get('/admin/vendors/create')->assertForbidden();
        $this->actingAs($operator)->get('/admin/cabangs/create')->assertForbidden();
    }

    public function test_only_admin_can_see_terminal_import_action(): void
    {
        $this->actingAs(User::factory()->create());
        Livewire::test(ListTerminals::class)->assertActionHidden('importExcel');

        $this->actingAs(User::factory()->admin()->create());
        Livewire::test(ListTerminals::class)->assertActionVisible('importExcel');
    }

    public function test_viewer_cannot_close_ticket(): void
    {
        $tiket = $this->createOpenTiket();

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(ListTikets::class)->assertTableActionHidden('closeTicket', $tiket);
    }

    public function test_terminal_with_ticket_history_cannot_be_force_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $tiket = $this->createOpenTiket();
        $terminal = $tiket->terminal;

        $this->assertFalse($admin->can('forceDelete', $terminal));

        $this->expectException(QueryException::class);

        $terminal->forceDelete();
    }

    public function test_admin_can_force_delete_terminal_without_ticket_history(): void
    {
        $admin = User::factory()->admin()->create();
        $terminal = Terminal::create([
            'profil' => 'WCR.KOSONG',
            'nama_lokasi' => 'Lokasi Tanpa Tiket',
            'kategori' => 'ATM',
        ]);

        $this->assertTrue($admin->can('forceDelete', $terminal));
        $this->assertFalse(User::factory()->create()->can('forceDelete', $terminal));
    }

    protected function createOpenTiket(): Tiket
    {
        $terminal = Terminal::create([
            'profil' => 'WCR.KCU1',
            'nama_lokasi' => 'RS Undata Palu',
            'kategori' => 'ATM',
        ]);

        return Tiket::create([
            'terminal_id' => $terminal->id,
            'nomor_tiket' => 'TKT-AUTH-001',
            'kategori_problem' => 'Mesin ATM',
            'permasalahan' => 'DISPENSER ERROR',
            'mulai' => now()->subHour(),
            'status' => 'Open',
        ]);
    }
}
