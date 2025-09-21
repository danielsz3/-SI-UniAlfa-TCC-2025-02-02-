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
        'valor',
        'descricao',
        'categoria',
        'tipo_transacao',
        'data_transacao',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data_transacao' => 'datetime',
    ];
}
