<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Store extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'address_line1', 'address_line2', 'city', 'state', 'postal_code', 'country', 'phone', 'website'];

    protected function casts(): array
    {
        return [

        ];
    }
    public function products(): BelongsToMany { return $this->belongsToMany(Product::class)->withPivot('price', 'currency', 'observed_at')->withTimestamps(); }
    public function productPrices(): HasMany { return $this->hasMany(ProductStore::class); }
    public function transactionItems(): HasMany { return $this->hasMany(InventoryTransactionItem::class); }
}
