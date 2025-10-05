<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Animal extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'animais';

    protected $primaryKey = 'id';


    protected $fillable = [
        'id_ong',
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
        return $this->belongsTo(Ong::class, 'id_ong');
    }

    public function imagens()
    {
        return $this->hasMany(ImagemAnimal::class, 'animal_id');
    }
}
