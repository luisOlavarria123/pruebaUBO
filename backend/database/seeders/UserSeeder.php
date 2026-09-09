<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Credenciales de prueba (documentadas en el README):
     * - solicitante@ubo.test / password
     * - revisor@ubo.test / password
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'solicitante@ubo.test'],
            [
                'name' => 'Ana Solicitante',
                'password' => Hash::make('password'),
                'role' => UserRole::Solicitante,
                'email_verified_at' => now(),
            ],
        );

        User::updateOrCreate(
            ['email' => 'revisor@ubo.test'],
            [
                'name' => 'Roberto Revisor',
                'password' => Hash::make('password'),
                'role' => UserRole::Revisor,
                'email_verified_at' => now(),
            ],
        );
    }
}
