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
        'id_usuario',
        'nome_ong',
        'cnpj',
        'descricao',
        'url_logo',
        'url_banner',
        'telefone',
        'pix',
        'banco',
        'agencia',
        'conta',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }
}
