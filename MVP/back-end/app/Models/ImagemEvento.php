<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImagemEvento extends Model
{
    use SoftDeletes;

    protected $table = 'imagens_eventos';

    protected $primaryKey = 'id';
    
    protected $fillable = [
        'evento_id',
        'caminho',
        'width',
        'height'
    ];

    public function evento()
    {
        return $this->belongsTo(Evento::class, 'evento_id');
    }
}