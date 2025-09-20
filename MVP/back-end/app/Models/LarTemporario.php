<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LarTemporario extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lares_temporarios';

    protected $fillable = [
        'data_nascimento',
        'telefone',
        'situacao',
        'experiencia',
    ];

    protected $casts = [
        'data_nascimento' => 'date',
    ];

    // Relacionamento polimórfico com endereços (se você implementar)
    public function enderecos()
    {
        return $this->morphMany(Endereco::class, 'enderecoable');
    }

    // Accessor para calcular idade
    public function getIdadeAttribute()
    {
        return $this->data_nascimento->age;
    }

    // Scope para filtrar apenas ativos
    public function scopeAtivos($query)
    {
        return $query->where('situacao', 'ativo');
    }

    // Scope para filtrar apenas inativos
    public function scopeInativos($query)
    {
        return $query->where('situacao', 'inativo');
    }
}