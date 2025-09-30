<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImagemLarTemporario extends Model
{
    protected $table = 'imagens_lar_temporario'; // tabela no plural
    protected $primaryKey = 'id_imagem_lar_temp';

    protected $fillable = [
        'id_lar_temporario',
        'url_imagem'
    ];

    public function larTemporario()
    {
        return $this->belongsTo(LarTemporario::class, 'id_lar_temporario');
    }
}