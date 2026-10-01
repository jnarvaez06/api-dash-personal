<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\MovementType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'account_id',
    'category_id',
    'description',
    'amount',
    'date',
    'type',
    'transfer_id',
    'is_transfer',
])]
class Movements extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    protected function casts(): array
    {
        return [
            'type' => MovementType::class,
            'is_transfer' => 'boolean',
        ];
    }
}
