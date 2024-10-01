<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory, SoftDeletes, Prunable;

    protected $table = 'items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sku',
        'name',
        'content',
        'type_id',
        'packaging',
        'unit',
        'manufacturer',
        'supplier_id',
        'price'
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('name', 'asc');
        });
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }

    public function partners(): BelongsToMany
    {
        return $this->belongsToMany(Partner::class, 'partner_item')->withPivot('id', 'shelf_id', 'barcode_id', 'batch', 'exp_date', 'stock_qty', 'is_consigned', 'deleted_at')->withTimestamps()->wherePivot('deleted_at', null);
    }

    public function buy_orders(): BelongsToMany
    {
        return $this->belongsToMany(BuyOrder::class, 'buy_order_details', 'item_id', 'buy_order_id')->withPivot(['quantity', 'arrived_quantity', 'total', 'deleted_at'])->withTimestamps()->wherePivot('deleted_at', null);
    }

    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        return static::where('deleted_at', '<=', now()->subMonths(6));
    }
}
