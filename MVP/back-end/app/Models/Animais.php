<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Animal extends Model
{
    use SoftDeletes;

    protected $table = 'animais';

    protected $fillable = [
        'ong_id',
        'nome',
        'sexo',
        'idade',
        'castrado',
        'vale_castracao',
        'descricao',
        'tipo_animal',
        'nivel_energia',
        'tamanho',
        'tempo_necessario',
        'ambiente_ideal',
    ];

    // ✅ Relacionamentos
    public function ong()
    {
        return $this->belongsTo(Ong::class, 'ong_id');
    }

    public function imagens()
    {
        return $this->hasMany(ImagemAnimal::class, 'animal_id');
    }
}
