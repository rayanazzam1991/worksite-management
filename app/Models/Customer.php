<?php

namespace App\Models;

use Database\Factories\CustomerFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $first_name
 * @property string|null $last_name
 * @property string|null $phone
 * @property string $status
 * @property int|null $address_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read Address|null $address
 * @property-read mixed $full_name
 * @property-read Collection<int, Payment> $payments
 * @property-read int|null $payments_count
 * @property string $fullName
 *
 * @method static CustomerFactory factory($count = null, $state = [])
 * @method static Builder|Customer newModelQuery()
 * @method static Builder|Customer newQuery()
 * @method static Builder|Customer query()
 * @method static Builder|Customer whereAddressId($value)
 * @method static Builder|Customer whereCreatedAt($value)
 * @method static Builder|Customer whereDeletedAt($value)
 * @method static Builder|Customer whereFirstName($value)
 * @method static Builder|Customer whereId($value)
 * @method static Builder|Customer whereLastName($value)
 * @method static Builder|Customer wherePhone($value)
 * @method static Builder|Customer whereStatus($value)
 * @method static Builder|Customer whereUpdatedAt($value)
 *
 * @property-read \App\Models\WorkSite|null $workSite
 *
 * @method static Builder|Customer onlyTrashed()
 * @method static Builder|Customer withTrashed()
 * @method static Builder|Customer withoutTrashed()
 *
 * @mixin Eloquent
 */
class Customer extends Model
{
    use HasFactory,SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = [];

    protected static function newFactory(): CustomerFactory
    {
        return CustomerFactory::new();
    }

    /**
     * @return Attribute<string,string>
     */
    public function fullName(): Attribute
    {

        return Attribute::make(
            get: function (mixed $value, mixed $attributes) {
                if (is_array($attributes)) {
                    return ($attributes['first_name'] ?? '').
                        ' '.($attributes['last_name'] ?? '');
                }

                return '';
            }
        );
    }

    /**
     * @return BelongsTo<Address,Customer>
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    /**
     * @return MorphMany<Payment>
     */
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    /**
     * @return HasOne<WorkSite>
     */
    public function workSite(): HasOne
    {
        return $this->hasOne(WorkSite::class);
    }
}
