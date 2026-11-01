<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'description', 'sku', 'barcode', 'qrcode', 'unit_of_measure', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
    public function inventoryItems(): HasMany { return $this->hasMany(InventoryItem::class); }
    public function stores(): BelongsToMany { return $this->belongsToMany(Store::class)->withPivot('price', 'currency', 'observed_at')->withTimestamps(); }
    public function storePrices(): HasMany { return $this->hasMany(ProductStore::class); }
    public function transactionItems(): HasMany { return $this->hasMany(InventoryTransactionItem::class); }
}
