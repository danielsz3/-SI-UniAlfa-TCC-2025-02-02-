<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transacao extends Model
{
    use HasFactory, SoftDeletes;

    // Nome da tabela no banco
    protected $table = 'transacoes';

    // Chave primária
    protected $primaryKey = 'id';

    // Campos que podem ser preenchidos em massa (mass assignment)
    protected $fillable = [
        'tipo',             // entrada ou saída
        'valor',            // valor financeiro
        'data',             // data da transação
        'categoria',        // categoria
        'descricao',        // descrição
        'forma_pagamento',  // forma de pagamento
        'situacao',         // pendente, pago, cancelado
        'observacao',       // observações adicionais
    ];

    // Cast de tipos
    protected $casts = [
        'valor' => 'decimal:2',
        'data' => 'datetime',
    ];
}