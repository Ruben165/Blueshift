<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shelf extends Model
{
    use HasFactory, SoftDeletes, Prunable;

    protected $table = 'shelfs';

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
}
