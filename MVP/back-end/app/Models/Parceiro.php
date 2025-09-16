<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Parceiro extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'parceiros';

    protected $primaryKey = 'id_parceiro';

    
    protected $fillable = [
        'nome_parceiro',
        'url_site',
        'url_logo',
        'descricao',
    ];
}
