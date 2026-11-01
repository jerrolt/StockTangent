<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $fillable = ['inventory_id', 'user_id', 'type', 'reference_number', 'notes'];

    protected function casts(): array
    {
        return [

        ];
    }
    public function inventory(): BelongsTo { return $this->belongsTo(Inventory::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function items(): HasMany { return $this->hasMany(InventoryTransactionItem::class, 'transaction_id'); }
}
