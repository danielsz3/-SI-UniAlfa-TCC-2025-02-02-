<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class AnimalSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        $sexoOptions = ['macho', 'femea'];
        $situacaoOptions = ['disponivel', 'adotado', 'em_adocao', 'em_aprovacao'];
        $tipoAnimalOptions = ['cao', 'gato', 'outro'];
        $nivelEnergiaOptions = ['baixa', 'moderada', 'alta'];
        $tamanhoOptions = ['pequeno', 'medio', 'grande'];
        $tempoNecessarioOptions = ['pouco_tempo', 'tempo_moderado', 'muito_tempo'];
        $ambienteIdealOptions = ['area_pequena', 'area_media', 'area_externa'];

        for ($i = 0; $i < 50; $i++) {
            DB::table('animais')->insert([
                'nome' => $faker->firstName(),
                'sexo' => $faker->randomElement($sexoOptions),
                'data_nascimento' => $faker->date('Y-m-d', '2018-12-31'),
                'castrado' => $faker->boolean(70) ? 1 : 0,
                'vale_castracao' => $faker->boolean(50) ? 1 : 0,
                'descricao' => $faker->paragraph(),
                'situacao' => $faker->randomElement($situacaoOptions),
                'tipo_animal' => $faker->randomElement($tipoAnimalOptions),
                'nivel_energia' => $faker->randomElement($nivelEnergiaOptions),
                'tamanho' => $faker->randomElement($tamanhoOptions),
                'tempo_necessario' => $faker->randomElement($tempoNecessarioOptions),
                'ambiente_ideal' => $faker->randomElement($ambienteIdealOptions),
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
