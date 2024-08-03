<?php

namespace App\Models;

use Database\Factories\AddressFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $title
 * @property string|null $street
 * @property string|null $state
 * @property string|null $zipcode
 * @property int $city_id
 * @property string|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read City|null $city
 *
 * @method static AddressFactory factory($count = null, $state = [])
 * @method static Builder|Address newModelQuery()
 * @method static Builder|Address newQuery()
 * @method static Builder|Address query()
 * @method static Builder|Address whereCityId($value)
 * @method static Builder|Address whereCreatedAt($value)
 * @method static Builder|Address whereDeletedAt($value)
 * @method static Builder|Address whereId($value)
 * @method static Builder|Address whereState($value)
 * @method static Builder|Address whereStreet($value)
 * @method static Builder|Address whereUpdatedAt($value)
 * @method static Builder|Address whereZipcode($value)
 *
 * @mixin \Eloquent
 */
class Address extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function newFactory(): AddressFactory
    {
        return AddressFactory::new();
    }

    /**
     * @return BelongsTo<City,Address>
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
