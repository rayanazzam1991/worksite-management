<?php

namespace App\Models;

use Database\Factories\WorkSiteCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Database\Factories\WorkSiteCategoryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|WorkSiteCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkSiteCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkSiteCategory onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkSiteCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkSiteCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkSiteCategory whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkSiteCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkSiteCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkSiteCategory whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkSiteCategory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkSiteCategory withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkSiteCategory withoutTrashed()
 *
 * @mixin \Eloquent
 */
class WorkSiteCategory extends Model
{
    use HasFactory,SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = [];

    protected static function newFactory(): WorkSiteCategoryFactory
    {
        return WorkSiteCategoryFactory::new();
    }
}
