<?php

namespace App\Enums;

enum UserRole: string
{
    /**
     * Akses penuh: master vendor/cabang, import, hapus permanen, dan pemulihan data.
     */
    case Admin = 'admin';

    /**
     * Mengelola data operasional harian: membuat dan memperbarui tiket serta terminal.
     */
    case Operator = 'operator';

    /**
     * Hanya melihat data dan mengekspor laporan SLA.
     */
    case Viewer = 'viewer';

    public function getLabel(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Operator => 'Operator',
            self::Viewer => 'Viewer',
        };
    }
}
