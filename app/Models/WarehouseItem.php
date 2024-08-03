<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\WareHouseItemFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $warehouse_id
 * @property int $item_id
 * @property int|null $supplier_id
 * @property float $price
 * @property float $quantity
 * @property int $status
 * @property Carbon $date
 *
 * @method static \Database\Factories\WarehouseItemFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|WarehouseItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WarehouseItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WarehouseItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|WarehouseItem onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|WarehouseItem withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|WarehouseItem withoutTrashed()
 *
 * @mixin Eloquent
 */
class WarehouseItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'warehouse_items';

    protected $guarded = [];

    public static function newFactory(): WarehouseItemFactory
    {
        return WarehouseItemFactory::new();
    }

    /**
     * @return BelongsTo<Warehouse,WarehouseItem>
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * @return BelongsTo<Item,WarehouseItem>
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
