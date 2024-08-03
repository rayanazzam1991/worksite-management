<?php

namespace App\Providers;

use App\Models\Customer;
use App\Models\WorkSite;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class MorphRelationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::morphMap([
            'workSite' => Worksite::class,
            'customer' => Customer::class,
        ]);
    }
}
