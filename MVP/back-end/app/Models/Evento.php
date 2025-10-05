<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Evento extends Model
{
    use SoftDeletes;

    protected $table = 'eventos';

    protected $fillable = [
        'titulo',
        'data_inicio',
        'data_fim',
        'local',
        'descricao',
        'imagem_capa' // caminho da imagem de capa
    ];

    public function imagens()
    {
        return $this->hasMany(ImagemEvento::class, 'evento_id');
    }
}