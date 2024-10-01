<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SellOrder extends Model
{
    use HasFactory, SoftDeletes, Prunable;

    protected $table = 'sell_orders';

    protected $fillable = ['sell_order_type_id', 'source_partner_id', 'destination_partner_id', 'status_id', 'document_number', 'status_kode', 'total_price', 'description', 'created_at', 'delivered_at', 'due_at', 'parent_id', 'path', 'buktiPembayaran', 'pic_cancel', 'alasan_cancel', 'path_cancel', 'id_request', 'returned_at', 'pic_retur'];

    public function sellOrderType(): BelongsTo
    {
        return $this->belongsTo(SellOrderType::class);
    }

    public function sourcePartner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'source_partner_id');
    }

    public function destinationPartner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'destination_partner_id');
    }

    public function status(): BelongsTo
    {   
        return $this->belongsTo(Status::class);
    }

    public function partnerItems(): BelongsToMany
    {
        return $this->belongsToMany(PartnerItem::class, 'sell_order_details', 'sell_order_id', 'item_id')->withPivot(['quantity', 'quantity_left', 'total', 'deleted_at'])->withTimestamps()->wherePivot('deleted_at', null);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(SellOrder::class, 'parent_id', 'id');
    }

    public function childs(): HasMany
    {
        return $this->hasMany(SellOrder::class, 'parent_id', 'id');
    }

    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        return static::where('deleted_at', '<=', now()->subMonths(6));
    }
}
