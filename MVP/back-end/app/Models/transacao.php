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
        'tipo',             
        'valor',
        'data',
        'categoria',
        'descricao',
        'forma_pagamento',
        'situacao',
        'observacao',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data'  => 'datetime',
    ];
}