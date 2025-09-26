<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Integracao extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'integracoes';

    protected $fillable = [
        'service',
        'access_token',
        'external_user_id',
        'expires_at',
    ];
}
