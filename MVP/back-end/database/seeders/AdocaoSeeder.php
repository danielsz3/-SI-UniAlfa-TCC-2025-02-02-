<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class AdocaoSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Arrays com os valores possíveis dos enums, baseado na sua tabela
        $statusOptions = ['em_aprovacao', 'aprovado'];
        $qtdPessoasCasaOptions = ['sozinho', 'uma_pessoa', 'duas_pessoas', 'tres_pessoas', 'quatro_ou_mais'];
        $acessoRuaJanelasOptions = [
            'janelas_telas_sem_acesso_rua',
            'janelas_sem_telas',
            'janelas_sem_telas_instalarei'
        ];
        $acessoRuaPortoesMurosOptions = [
            'impedem_escape',
            'permitem_acesso_rua',
            'serao_adaptados'
        ];
        $rendaFamiliarOptions = ['acima_2_sm', 'abaixo_2_sm', 'outro'];

        for ($i = 0; $i < 50; $i++) {  // Gera 50 registros
            DB::table('adocoes')->insert([
                'usuario_id' => 1,
                'animal_id' => $faker->numberBetween(1, 50),
                'status' => $faker->randomElement($statusOptions),
                'qtd_pessoas_casa' => $faker->randomElement($qtdPessoasCasaOptions),
                'possui_filhos' => $faker->boolean(40) ? 1 : 0, // 40% chance de ter filhos
                'sobre_rotina' => json_encode([
                    'atividade' => $faker->randomElement(['trabalho', 'estudo', 'lar']),
                    'horario' => $faker->randomElement(['manhã', 'tarde', 'noite'])
                ]),
                'acesso_rua_janelas' => $faker->randomElement($acessoRuaJanelasOptions),
                'acesso_rua_portoes_muros' => $faker->randomElement($acessoRuaPortoesMurosOptions),
                'renda_familiar' => $faker->randomElement($rendaFamiliarOptions),
                'aceita_termos' => 1,
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
