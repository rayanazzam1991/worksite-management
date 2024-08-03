<?php

namespace App\Models;

use Database\Factories\JobTitleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @method static JobTitleFactory factory($count = null, $state = [])
 * @method static Builder|JobTitle newModelQuery()
 * @method static Builder|JobTitle newQuery()
 * @method static Builder|JobTitle query()
 * @method static Builder|JobTitle where1($value)
 * @method static Builder|JobTitle whereCreatedAt($value)
 * @method static Builder|JobTitle whereDeletedAt($value)
 * @method static Builder|JobTitle whereId($value)
 * @method static Builder|JobTitle whereName($value)
 * @method static Builder|JobTitle whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class JobTitle extends Model
{
    use HasFactory;
}
