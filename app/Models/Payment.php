<?php

namespace App\Models;

use Database\Factories\PaymentFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $payable_type
 * @property int $payable_id
 * @property string $amount
 * @property string $payment_date
 * @property int $payment_type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read Model|Eloquent $payable
 *
 * @method static PaymentFactory factory($count = null, $state = [])
 * @method static Builder|Payment newModelQuery()
 * @method static Builder|Payment newQuery()
 * @method static Builder|Payment query()
 * @method static Builder|Payment whereAmount($value)
 * @method static Builder|Payment whereCreatedAt($value)
 * @method static Builder|Payment whereDeletedAt($value)
 * @method static Builder|Payment whereId($value)
 * @method static Builder|Payment wherePayableId($value)
 * @method static Builder|Payment wherePayableType($value)
 * @method static Builder|Payment wherePaymentDate($value)
 * @method static Builder|Payment wherePaymentType($value)
 * @method static Builder|Payment whereUpdatedAt($value)
 *
 * @mixin Eloquent
 */
class Payment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = [];

    protected static function newFactory(): PaymentFactory
    {
        return PaymentFactory::new();
    }

    /**
     * @return MorphTo<Model,Payment>
     */
    public function payable(): MorphTo
    {
        return $this->morphTo();
    }
}
