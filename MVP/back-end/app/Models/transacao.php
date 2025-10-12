<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transacao extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'transacoes';
    protected $primaryKey = 'id';

    protected $fillable = [
        'tipo',             // 'entrada' | 'saida'
        'valor',            // decimal
        'data',             // datetime
        'categoria',        // varchar
        'descricao',        // varchar
        'forma_pagamento',  // varchar
        'situacao',         // 'pendente' | 'concluido' | 'cancelado'
        'observacao',       // text (nullable)
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data'  => 'datetime',
    ];
}