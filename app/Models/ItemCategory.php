<?php

namespace App\Models;

use Database\Factories\ItemCategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property int $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static ItemCategoryFactory factory($count = null, $state = [])
 * @method static Builder|ItemCategory newModelQuery()
 * @method static Builder|ItemCategory newQuery()
 * @method static Builder|ItemCategory onlyTrashed()
 * @method static Builder|ItemCategory query()
 * @method static Builder|ItemCategory whereCreatedAt($value)
 * @method static Builder|ItemCategory whereDeletedAt($value)
 * @method static Builder|ItemCategory whereId($value)
 * @method static Builder|ItemCategory whereName($value)
 * @method static Builder|ItemCategory whereStatus($value)
 * @method static Builder|ItemCategory whereUpdatedAt($value)
 * @method static Builder|ItemCategory withTrashed()
 * @method static Builder|ItemCategory withoutTrashed()
 *
 * @mixin \Eloquent
 */
class ItemCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected static function newFactory(): ItemCategoryFactory
    {
        return ItemCategoryFactory::new();
    }
}
