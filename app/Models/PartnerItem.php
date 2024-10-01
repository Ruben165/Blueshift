<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PartnerItem extends Model
{
    use HasFactory, SoftDeletes, Prunable;

    protected $table = 'partner_item';

    protected $fillable = ['item_id', 'partner_id', 'shelf_id', 'barcode_id', 'batch', 'exp_date', 'stock_qty', 'discount_price', 'is_consigned', 'created_at', 'deleted_at'];

    protected static function booted(): void
    {
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->select('partner_item.*')->join('items', 'items.id', '=', 'partner_item.item_id')->orderBy('items.name');
        });
    }

    public function shelf(): BelongsTo
    {
        return $this->belongsTo(Shelf::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function sell_orders(): BelongsToMany
    {
        return $this->belongsToMany(SellOrder::class, 'sell_order_details', 'item_id', 'sell_order_id')->withPivot(['quantity', 'total', 'deleted_at'])->withTimestamps()->wherePivot('deleted_at', null);
    }

    protected function discountPrice(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ?? $this->item->price,
        );
    }

    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        return static::where('deleted_at', '<=', now()->subMonths(6));
    }
}
