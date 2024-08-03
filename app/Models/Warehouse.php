<?php

namespace App\Models;

use Database\Factories\WareHouseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property-read \App\Models\Address|null $address
 *
 * @method static \Database\Factories\WareHouseFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Warehouse newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Warehouse newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Warehouse onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Warehouse query()
 * @method static \Illuminate\Database\Eloquent\Builder|Warehouse withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Warehouse withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Warehouse extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'warehouses';

    protected $guarded = [];

    /**
     * @return BelongsTo<Address,Warehouse>
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public static function newFactory(): WareHouseFactory
    {
        return WareHouseFactory::new();
    }
}
