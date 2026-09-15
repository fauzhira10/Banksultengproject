<?php

namespace Tests\Feature;

use App\Filament\Resources\Terminals\Pages\ListTerminals;
use App\Filament\Resources\Tikets\Pages\ListTikets;
use App\Http\Responses\LoginResponse;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NavigationHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_tiket_tabs_selection_persists_in_session_and_resets_on_login(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 1. Awalnya default tab adalah 'all'
        Livewire::test(ListTikets::class)
            ->assertSuccessful()
            ->assertSet('activeTab', 'all');

        // 2. User mengklik tab 'open'
        Livewire::test(ListTikets::class)
            ->set('activeTab', 'open')
            ->assertSet('activeTab', 'open');

        $this->assertSame('open', session('tikets_active_tab'));

        // 3. User navigasi ke halaman lain dan kembali ke tiket: tab tetap 'open'
        Livewire::test(ListTikets::class)
            ->assertSet('activeTab', 'open');

        // 4. User login baru: riwayat tab tiket direset ke default
        event(new Login('web', $user, false));
        $this->assertNull(session('tikets_active_tab'));

        Livewire::test(ListTikets::class)
            ->assertSet('activeTab', 'all');
    }

    public function test_terminal_tabs_selection_persists_in_session_and_resets_on_login(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 1. Awalnya default tab adalah 'all'
        Livewire::test(ListTerminals::class)
            ->assertSuccessful()
            ->assertSet('activeTab', 'all');

        // 2. User mengklik tab 'crm'
        Livewire::test(ListTerminals::class)
            ->set('activeTab', 'crm')
            ->assertSet('activeTab', 'crm');

        $this->assertSame('crm', session('terminals_active_tab'));

        // 3. User navigasi ke halaman lain dan kembali ke terminal: tab tetap 'crm'
        Livewire::test(ListTerminals::class)
            ->assertSet('activeTab', 'crm');

        // 4. User login baru: riwayat tab terminal direset ke default
        event(new Login('web', $user, false));
        $this->assertNull(session('terminals_active_tab'));

        Livewire::test(ListTerminals::class)
            ->assertSet('activeTab', 'all');
    }

    public function test_login_resets_all_navigation_and_table_sessions(): void
    {
        session([
            'sla_selected_vendor_id' => 99,
            'tikets_active_tab' => 'open',
            'terminals_active_tab' => 'crm',
            'tables' => ['tikets_table_filters' => ['status' => 'Open']],
            'tables.custom_search' => 'search query',
        ]);

        LoginResponse::resetSessionHistory();

        $this->assertNull(session('sla_selected_vendor_id'));
        $this->assertNull(session('tikets_active_tab'));
        $this->assertNull(session('terminals_active_tab'));
        $this->assertNull(session('tables'));
        $this->assertNull(session('tables.custom_search'));
    }
}
