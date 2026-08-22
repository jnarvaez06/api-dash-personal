<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[Fillable([
    'name',
    'is_active',
])]

class Category extends Model
{
    use HasFactory;

    public const DEFAULT_NAMES = [
        'Comida',
        'Servicios',
        'Transporte',
        'Mercado',
        'Salud',
        'Salario',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
