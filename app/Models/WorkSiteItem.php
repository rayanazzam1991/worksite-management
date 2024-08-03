<?php

namespace App\Models;

use Database\Factories\WorkSiteItemFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static WorkSiteItemFactory factory($count = null, $state = [])
 * @method static Builder|WorkSiteItem newModelQuery()
 * @method static Builder|WorkSiteItem newQuery()
 * @method static Builder|WorkSiteItem query()
 *
 * @property mixed $work_site_id
 * @property mixed $item_id
 * @property mixed $price
 * @property mixed $quantity
 *
 * @mixin Eloquent
 */
class WorkSiteItem extends Model
{
    use HasFactory;

    protected $table = 'work_site_items';

    protected $guarded = [];

    protected static function newFactory(): WorkSiteItemFactory
    {
        return WorkSiteItemFactory::new();
    }
}
