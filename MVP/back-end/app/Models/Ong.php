<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ong extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ongs';
    protected $primaryKey = 'id_ong';

    protected $fillable = [
        'nome',
        'razao_social',
        'descricao',
        'imagem',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'pais',
        'banco',
        'agencia',
        'numero_conta',
        'tipo_conta',
        'chave_pix',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

}