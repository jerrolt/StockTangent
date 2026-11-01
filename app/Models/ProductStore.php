<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductStore extends Model
{
    use HasFactory;

    protected $table = 'product_store';

    protected $fillable = ['product_id', 'store_id', 'price', 'currency', 'observed_at'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:4',
            'observed_at' => 'datetime',
        ];
    }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function store(): BelongsTo { return $this->belongsTo(Store::class); }
}
