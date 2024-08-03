<?php

namespace App\Models;

use Database\Factories\ItemFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property mixed|null $name
 * @property int $id
 * @property string|null $description
 * @property int $resource_category_id
 * @property int $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read ItemCategory|null $category
 *
 * @method static ItemFactory factory($count = null, $state = [])
 * @method static Builder|Item newModelQuery()
 * @method static Builder|Item newQuery()
 * @method static Builder|Item onlyTrashed()
 * @method static Builder|Item query()
 * @method static Builder|Item whereCreatedAt($value)
 * @method static Builder|Item whereDeletedAt($value)
 * @method static Builder|Item whereDescription($value)
 * @method static Builder|Item whereId($value)
 * @method static Builder|Item whereName($value)
 * @method static Builder|Item whereResourceCategoryId($value)
 * @method static Builder|Item whereStatus($value)
 * @method static Builder|Item whereUpdatedAt($value)
 * @method static Builder|Item withTrashed()
 * @method static Builder|Item withoutTrashed()
 *
 * @mixin Eloquent
 */
class Item extends Model
{
    use HasFactory,SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = [];

    protected static function newFactory(): ItemFactory
    {
        return ItemFactory::new();
    }

    /**
     * @return BelongsTo<ItemCategory,Item>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class, 'item_category_id');
    }
}
