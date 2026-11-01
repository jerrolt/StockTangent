<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class InventoryItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['inventory_id', 'product_id', 'quantity', 'reorder_level'];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'reorder_level' => 'decimal:4',
        ];
    }
    public function inventory(): BelongsTo { return $this->belongsTo(Inventory::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function transactionItems(): HasMany { return $this->hasMany(InventoryTransactionItem::class); }
}
