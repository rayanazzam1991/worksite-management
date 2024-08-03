<?php

namespace App\Models;

use Database\Factories\ContractorFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read Address|null $address
 *
 * @method static ContractorFactory factory($count = null, $state = [])
 * @method static Builder|Contractor newModelQuery()
 * @method static Builder|Contractor newQuery()
 * @method static Builder|Contractor query()
 *
 * @mixin \Eloquent
 */
class Contractor extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function newFactory(): ContractorFactory
    {
        return ContractorFactory::new();
    }

    /**
     * @return BelongsTo<Address,Contractor>
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }
}
