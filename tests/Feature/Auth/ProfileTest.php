<?php

namespace Tests\Feature\Auth;

use App\Filament\Pages\Auth\EditProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_cannot_be_accessed_by_guests(): void
    {
        $response = $this->get('/admin/profile');

        $response->assertRedirect('/admin/login');
    }

    public function test_profile_page_can_be_rendered_for_authenticated_users(): void
    {
        $user = User::factory()->create([
            'name' => 'Staf IT Bank Sulteng',
            'username' => 'stafit',
            'email' => 'stafit@banksulteng.co.id',
        ]);

        $response = $this->actingAs($user)->get('/admin/profile');

        $response->assertSuccessful();
        $response->assertSee('Profil');
        $response->assertSee('stafit');
    }

    public function test_operator_cannot_change_own_email_address(): void
    {
        $user = User::factory()->create([
            'email' => 'operator@banksulteng.co.id',
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($user);

        Livewire::test(EditProfile::class)
            ->assertFormFieldDisabled('email')
            ->fillForm([
                'name' => 'Nama Baru',
                'email' => 'lain@banksulteng.co.id',
                'currentPassword' => 'password123',
            ])
            ->call('save');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nama Baru',
            'email' => 'operator@banksulteng.co.id',
        ]);
    }

    public function test_admin_can_update_profile_name_and_email(): void
    {
        $user = User::factory()->admin()->create([
            'name' => 'Nama Lama',
            'username' => 'petugas01',
            'email' => 'lama@banksulteng.co.id',
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($user);

        Livewire::test(EditProfile::class)
            ->fillForm([
                'name' => 'Nama Baru Bank Sulteng',
                'email' => 'baru@banksulteng.co.id',
                'currentPassword' => 'password123',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nama Baru Bank Sulteng',
            'username' => 'petugas01',
            'email' => 'baru@banksulteng.co.id',
        ]);
    }
}
