<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use App\Models\Concerns\Auditable;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Kolom `role` dan `is_active` sengaja tidak mass-assignable agar tidak dapat
 * diubah lewat form profil; gunakan `forceFill()` dari kode tepercaya.
 */
#[Fillable(['name', 'username', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use Auditable, HasFactory, Notifiable;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'role' => UserRole::Viewer->value,
        'is_active' => true,
    ];

    /**
     * Fail-closed: akun hanya boleh masuk bila `is_active` bernilai true secara eksplisit.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active === true;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    /**
     * Admin dan Operator boleh membuat serta memperbarui tiket dan terminal.
     */
    public function canManageOperationalData(): bool
    {
        return in_array($this->role, [UserRole::Admin, UserRole::Operator], true);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }
}
