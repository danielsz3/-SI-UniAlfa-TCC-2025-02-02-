<?php

namespace Database\Seeders;

use App\Models\Usuario;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        Usuario::create(
            [
                'nome' => 'Admin',
                'email' => 'admin@admin.com',
                'password' => bcrypt('12345678'),
                'role' => 'admin',
                'cpf' => '00000000000',
                'telefone' => '00000000000',
                'data_nascimento' => '2000-01-01',
            ]
        );
    }
}
