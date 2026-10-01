<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\MovementType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'category_id',
    'description',
    'amount',
    'date',
    'type',
])]
class Movements extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    
    protected function casts(): array
    {
        return [
            'type' => MovementType::class,
        ];
    }
}
