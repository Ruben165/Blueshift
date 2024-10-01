<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partner extends Model
{
    use HasFactory, SoftDeletes, Prunable;

    protected $table = 'partners';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'clinic_id',
        'name',
        'email',
        'phone',
        'logo',
        'address',
        'sales_name',
        'is_headquarter',
        'allow_consign'
    ];

    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        return static::where('deleted_at', '<=', now()->subMonths(6));
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'partner_group')->withPivot(['deleted_at'])->withTimestamps()->wherePivot('deleted_at', null);
    }

    public function zones()
    {
        return $this->belongsToMany(Zone::class, 'partner_zone')->withPivot(['deleted_at'])->withTimestamps()->wherePivot('deleted_at', null);
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'partner_item')->withPivot('id', 'shelf_id', 'barcode_id', 'batch', 'exp_date', 'stock_qty', 'is_consigned', 'deleted_at')->withTimestamps()->wherePivot('deleted_at', null);
    }

    public static function boot() {
        parent::boot();
        self::deleting(function($partner) {
            $partner->groups()->detach();
            $partner->zones()->detach();
        });
    }
}
