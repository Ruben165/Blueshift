<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BuyOrder extends Model
{
    use HasFactory, SoftDeletes, Prunable;

    protected $table = 'buy_orders';

    protected $fillable = ['supplier_id', 'status_id', 'type_id', 'faktur', 'SP_no', 'SP_date', 'approve_date', 'send_date', 'receive_date', 'partner_item_id'];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }

    public function status(): BelongsTo
    {   
        return $this->belongsTo(Status::class);
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'buy_order_details', 'buy_order_id', 'item_id')->withPivot(['qty_request', 'id_CRPOBR', 'clinic', 'qty_came', 'faktur', 'batch', 'expired', 'shelf', 'HNA_each', 'discount', 'note', 'buy_price', 'amount', 'deleted_at', 'order', 'partner_item_id'])->withTimestamps()->wherePivot('deleted_at', null);
    }

    // public function partnerItems(): BelongsToMany
    // {
    //     return $this->belongsToMany(PartnerItem::class, 'buy_order_partner_item_details', 'buy_order_id', 'partner_item_id')->withPivot(['quantity', 'total', 'deleted_at'])->withTimestamps()->wherePivot('deleted_at', null);
    // }

    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        return static::where('deleted_at', '<=', now()->subMonths(6));
    }
}
