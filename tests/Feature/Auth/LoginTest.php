<?php

namespace Tests\Feature\Auth;

use App\Filament\Pages\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get('/admin/login');

        $response->assertSuccessful();
        $response->assertSee('Username');
        $response->assertSee('Sistem Monitoring SLA ATM');
        $response->assertSee('PT Bank Pembangunan Daerah Sulawesi Tengah');
        $response->assertDontSee('Akses Terbatas');
        $response->assertDontSee('remember');
        $response->assertDontSee('data.email');
    }

    public function test_user_can_authenticate_with_username(): void
    {
        $user = User::factory()->create([
            'username' => 'admin',
            'password' => Hash::make('admin123'),
        ]);

        Livewire::test(Login::class)
            ->fillForm([
                'username' => 'admin',
                'password' => 'admin123',
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors()
            ->assertRedirect('/admin/tikets');

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_authenticate_with_wrong_password(): void
    {
        User::factory()->create([
            'username' => 'admin',
            'password' => Hash::make('admin123'),
        ]);

        Livewire::test(Login::class)
            ->fillForm([
                'username' => 'admin',
                'password' => 'wrongpassword',
            ])
            ->call('authenticate')
            ->assertHasFormErrors(['username']);

        $this->assertGuest();
    }

    public function test_user_cannot_authenticate_with_nonexistent_username(): void
    {
        Livewire::test(Login::class)
            ->fillForm([
                'username' => 'nonexistent',
                'password' => 'admin123',
            ])
            ->call('authenticate')
            ->assertHasFormErrors(['username']);

        $this->assertGuest();
    }

    public function test_authenticated_user_visiting_admin_is_redirected_to_tikets(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin');

        $response->assertRedirect('/admin/tikets');
    }

    public function test_dashboard_is_not_in_navigation_menu(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/tikets');

        $response->assertSuccessful();
        $response->assertSee('Open / Closed Tiket ATM');
        $response->assertDontSee('filament-panels::pages/dashboard.title');
        $response->assertDontSee('Dashboard</span>', false);
    }
}
