<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Documento extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'documentos';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'titulo',
        'categoria',
        'descricao',
        'arquivo',   // caminho no storage
        'tipo',      // mime-type
        'tamanho',   // tamanho em bytes
    ];
}