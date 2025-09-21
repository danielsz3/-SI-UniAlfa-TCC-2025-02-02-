<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Endereco extends Model
{
    use SoftDeletes;

    protected $table = 'enderecos';

    protected $fillable = [
        'id_usuario',
        'lar_temporario_id',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'uf'
    ];

    // Relacionamentos
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function larTemporario()
    {
        return $this->belongsTo(LarTemporario::class, 'lar_temporario_id');
    }
}