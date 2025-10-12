<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use App\Models\Endereco;
use App\Models\PreferenciaUsuario;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Cria apenas um usuário admin
        $admin = Usuario::create([
            'nome' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('12345678'),
            'role' => 'admin', // enum: 'user' | 'admin'
            'cpf' => '00000000000',
            'data_nascimento' => '2000-01-01',
            'telefone' => '11999999999',
        ]);
/*          php artisan db:seed executar as seeders
        // Opcional: endereço do admin (pode remover este bloco se não quiser criar endereço)
        Endereco::create([
            'id_usuario' => $admin->id,   // FK existente na sua tabela
            'lar_temporario_id' => null,  // nullable
            'cep' => '01310100',
            'logradouro' => 'Avenida Paulista',
            'numero' => '1000',
            'complemento' => 'Sala 100',  // nullable
            'bairro' => 'Bela Vista',
            'cidade' => 'São Paulo',
            'uf' => 'SP',
        ]);

        // Opcional: preferências do admin (remova se não precisar)
        PreferenciaUsuario::create([
            'usuario_id' => $admin->id,   // FK existente na sua tabela
            'tamanho_pet' => 'medio',
            'tempo_disponivel' => 'tempo_moderado',
            'estilo_vida' => 'ritmo_equilibrado',
            'espaco_casa' => 'area_media',
        ]);
        */
    }
}