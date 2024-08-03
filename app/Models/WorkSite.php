<?php

namespace App\Models;

use Database\Factories\WorkSiteFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string $description
 * @property int|null $customer_id
 * @property int|null $category_id
 * @property int|null $parent_work_site_id
 * @property string $starting_budget
 * @property string $cost
 * @property int|null $address_id
 * @property int $workers_count
 * @property string|null $receipt_date
 * @property string|null $starting_date
 * @property string|null $deliver_date
 * @property int $status_on_receive
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read Address|null $address
 * @property-read WorkSiteCategory|null $category
 * @property-read Customer|null $customer
 * @property-read Payment|null $lastPayment
 * @property-read WorkSite|null $parentWorksite
 * @property-read Collection<int, Payment> $payments
 * @property-read int|null $payments_count
 * @property-read Collection<int, Item> $resources
 * @property-read int|null $resources_count
 * @property-read Collection<int, WorkSite> $subWorkSites
 * @property-read int|null $sub_work_sites_count
 *
 * @method static WorkSiteFactory factory($count = null, $state = [])
 * @method static Builder|WorkSite newModelQuery()
 * @method static Builder|WorkSite newQuery()
 * @method static Builder|WorkSite query()
 * @method static Builder|WorkSite whereAddressId($value)
 * @method static Builder|WorkSite whereCategoryId($value)
 * @method static Builder|WorkSite whereCost($value)
 * @method static Builder|WorkSite whereCreatedAt($value)
 * @method static Builder|WorkSite whereCustomerId($value)
 * @method static Builder|WorkSite whereDeletedAt($value)
 * @method static Builder|WorkSite whereDeliverDate($value)
 * @method static Builder|WorkSite whereDescription($value)
 * @method static Builder|WorkSite whereId($value)
 * @method static Builder|WorkSite whereParentWorksiteId($value)
 * @method static Builder|WorkSite whereReceiptDate($value)
 * @method static Builder|WorkSite whereStartingBudget($value)
 * @method static Builder|WorkSite whereStartingDate($value)
 * @method static Builder|WorkSite whereStatusOnReceive($value)
 * @method static Builder|WorkSite whereTitle($value)
 * @method static Builder|WorkSite whereUpdatedAt($value)
 * @method static Builder|WorkSite whereWorkersCount($value)
 *
 * @property-read Collection<int, \App\Models\Item> $items
 * @property-read int|null $items_count
 *
 * @mixin Eloquent
 */
class WorkSite extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = [];

    protected static function newFactory(): WorkSiteFactory
    {
        return WorkSiteFactory::new();
    }

    /**
     * @return HasMany<WorkSite>
     */
    public function subWorkSites(): HasMany
    {
        return $this->hasMany(WorkSite::class, 'parent_work_site_id');
    }

    /**
     * @return BelongsTo<WorkSite,WorkSite>
     */
    public function parentWorksite(): BelongsTo
    {
        return $this->belongsTo(WorkSite::class, 'parent_work_site_id');
    }

    /**
     * @return BelongsToMany<Item>
     */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'work_site_items')->withPivot(['quantity', 'price']);
    }

    /**
     * @return BelongsTo<WorkSiteCategory,WorkSite>
     */
    public function category(): BelongsTo
    {
        return $this->BelongsTo(WorkSiteCategory::class);
    }

    /**
     * @return BelongsTo<Customer,WorkSite>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return MorphOne<Payment>
     */
    public function lastPayment(): MorphOne
    {
        return $this->morphOne(Payment::class, 'payable')->latest('id');
    }

    /**
     * @return MorphMany<Payment>
     */
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    /**
     * @return BelongsTo<Address,WorkSite>
     */
    public function address(): BelongsTo
    {
        return $this->BelongsTo(Address::class);
    }
}
