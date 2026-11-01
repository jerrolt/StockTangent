<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class InventoryUser extends Model
{
    use HasFactory;

    protected $table = 'inventory_user';

    protected $fillable = ['inventory_id', 'user_id', 'permission'];

    protected function casts(): array
    {
        return [

        ];
    }
    public function inventory(): BelongsTo { return $this->belongsTo(Inventory::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
