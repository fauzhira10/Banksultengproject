<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;

class EditProfile extends BaseEditProfile
{
    protected static ?string $title = 'Profil Pengguna';

    public static function getLabel(): string
    {
        return 'Profil';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getNameFormComponent()
                    ->label('Nama Lengkap')
                    ->placeholder('Masukkan nama lengkap'),
                $this->getUsernameFormComponent(),
                $this->getEmailFormComponent()
                    ->label('Alamat Email')
                    ->placeholder('nama@banksulteng.co.id'),
                $this->getCurrentPasswordFormComponent()
                    ->label('Kata Sandi Saat Ini'),
                $this->getPasswordFormComponent()
                    ->label('Kata Sandi Baru'),
                $this->getPasswordConfirmationFormComponent()
                    ->label('Konfirmasi Kata Sandi Baru'),
            ]);
    }

    protected function getUsernameFormComponent(): Component
    {
        return TextInput::make('username')
            ->label('Username')
            ->disabled()
            ->dehydrated(false)
            ->helperText('Username akun Bank Sulteng.');
    }
}
