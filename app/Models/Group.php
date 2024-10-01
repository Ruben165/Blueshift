<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use HasFactory, SoftDeletes, Prunable;

    protected $table = 'groups';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        return static::where('deleted_at', '<=', now()->subMonths(6));
    }

    public function partners()
    {
        return $this->belongsToMany(Partner::class, 'partner_group')->withPivot(['deleted_at'])->withTimestamps()->wherePivot('deleted_at', null);
    }

    public static function boot() {
        parent::boot();
        self::deleting(function($batch_mitra) {
            $batch_mitra->partners()->detach();
        });
    }
}
