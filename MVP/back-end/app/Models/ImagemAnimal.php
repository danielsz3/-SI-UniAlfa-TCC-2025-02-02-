<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImagemAnimal extends Model
{
    use SoftDeletes;

    protected $table = 'imagens_animais';

    protected $fillable = ['animal_id', 'caminho', 'width', 'height'];

    public function animal()
    {
        return $this->belongsTo(Animal::class, 'animal_id');
    }
}
