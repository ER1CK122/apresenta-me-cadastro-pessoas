<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@apresentame.com'],
            [
                'name' => 'Administrador',
                'password' => 'senha123', // será hasheado automaticamente pelo cast 'hashed'
            ]
        );
    }
}