<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pessoa extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'cpf',
        'tipo',
        'telefone',
        'email',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => 'string',
        ];
    }
}