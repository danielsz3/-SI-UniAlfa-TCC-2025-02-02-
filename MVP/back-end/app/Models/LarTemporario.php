<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LarTemporario extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lares_temporarios';
    protected $primaryKey = 'id';

    protected $fillable = [
        'data_nascimento',
        'telefone',
        'situacao',
        'experiencia',
    ];

    protected $casts = [
        'data_nascimento' => 'date',
    ];

    // Relacionamento direto com endereços
    public function enderecos()
    {
        return $this->hasMany(Endereco::class, 'lar_temporario_id');
    }

    // Accessor para calcular idade
    public function getIdadeAttribute()
    {
        return $this->data_nascimento ? $this->data_nascimento->age : null;
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