<?php

namespace Database\Seeders;

use App\Models\PermissionGroup;
use Illuminate\Database\Seeder;

class PermissionGroupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */
        // WorkSite
        PermissionGroup::query()->updateOrCreate(['name' => 'work-site', 'display_name' => 'WorkSite'], ['name' => 'workSite']);
        /*
        |--------------------------------------------------------------------------
        | WorkSite Category
        |--------------------------------------------------------------------------
        */
        PermissionGroup::query()->updateOrCreate(['name' => 'workSite-category', 'display_name' => 'WorkSite Category'], ['name' => 'ws-category']);

        /*
       |--------------------------------------------------------------------------
       | Customer
       |--------------------------------------------------------------------------
       */
        PermissionGroup::query()->updateOrCreate(['name' => 'customer', 'display_name' => 'Customer'], ['name' => 'customer']);

        /*
      |--------------------------------------------------------------------------
      | Payment
      |--------------------------------------------------------------------------
      */
        PermissionGroup::query()->updateOrCreate(['name' => 'payment', 'display_name' => 'Payment'], ['name' => 'payment']);

    }
}
