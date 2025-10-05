<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContatoOng extends Model
{
    use SoftDeletes;

    protected $table = 'contatos_ongs';

    protected $fillable = [
        'id_ong',
        'tipo',
        'contato',
        'link',
        'descricao',
    ];

    public function ong()
    {
        return $this->belongsTo(Ong::class, 'id_ong', 'id_ong');
    }
}