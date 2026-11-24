<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Inventory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'organization_id', 'name', 'description'];

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $query) use ($user) {
            $query->where('user_id', $user->id)
                ->orWhereHas('users', fn (Builder $query) => $query->whereKey($user->id))
                ->orWhereHas('organization', fn (Builder $query) => $query->withTrashed()
                    ->whereHas('users', fn (Builder $query) => $query->whereKey($user->id)));
        });
    }

    protected function casts(): array
    {
        return [

        ];
    }
    public function owner(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function users(): BelongsToMany { return $this->belongsToMany(User::class)->withPivot('permission')->withTimestamps(); }
    public function memberships(): HasMany { return $this->hasMany(InventoryUser::class); }
    public function items(): HasMany { return $this->hasMany(InventoryItem::class); }
    public function transactions(): HasMany { return $this->hasMany(InventoryTransaction::class); }
}
