<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::query()->updateOrCreate(['name' => 'admin'], [
            'name' => 'admin',
            'display_name' => 'Admin',
            'guard_name' => 'web',
            'description' => 'Admin Role',
        ]);

        $storeKeeperRole = Role::query()->updateOrCreate(['name' => 'store_keeper'], [
            'name' => 'store_keeper',
            'display_name' => 'Store Keeper',
            'guard_name' => 'web',
            'description' => 'Store Keeper Role',
        ]);

        $siteManagerRole = Role::query()->updateOrCreate(['name' => 'site_manager'], [
            'name' => 'site_manager',
            'display_name' => 'Site Manager',
            'guard_name' => 'web',
            'description' => 'Site Manager Role',
        ]);

        $employeeRole = Role::query()->updateOrCreate(['name' => 'worker'], [
            'name' => 'worker',
            'display_name' => 'Employee',
            'guard_name' => 'web',
            'description' => 'Employee Role',
        ]);

        $adminPermissions = Permission::all();

        $siteManagerPermissionsArray = ['work-site-list', 'work-site-show', 'order-create',
            'order-update',
            'order-delete',
            'order-list',
            'order-show'];
        $storeKeeperPermissionArray = ['order-update'];

        $siteManagerPermissions = Permission::query()->whereIn('name', $siteManagerPermissionsArray)->get();
        $storeKeeperPermissions = Permission::query()->whereIn('name', $storeKeeperPermissionArray)->get();

        //        $userPermissionsArray = ['hr-employees-info-attendance-show', 'hr-employees-info-attendance-add', 'profile-info-attendance', 'tm-readers-show', 'notifications-show', 'employee-info', 'employee-requests-show', 'profile-show', 'profile-info-general-show', 'profile-info-general-edit', 'profile-info-general-show-work-info-show', 'profile-info-general-show-work-info-edit', 'profile-info-general-show-work-info-print', 'profile-info-general-show-work-info-export', 'profile-info-general-show-travel-profile-show', 'profile-info-general-show-travel-profile-edit', 'profile-info-general-emergency-contact-show', 'profile-info-general-emergency-contact-add', 'profile-info-general-emergency-contact-edit', 'profile-info-general-emergency-contact-delete', 'profile-info-qualifications-show', 'profile-info-qualifications-education-show', 'profile-info-qualifications-education-add', 'profile-info-qualifications-education-edit', 'profile-info-qualifications-education-delete', 'profile-info-qualifications-certification-show', 'profile-info-qualifications-certification-add', 'profile-info-qualifications-certification-edit', 'profile-info-qualifications-certification-delete', 'profile-info-qualifications-language-show', 'profile-info-qualifications-language-add', 'profile-info-qualifications-language-edit', 'profile-info-qualifications-language-delete', 'profile-info-work-positions-show', 'profile-info-work-experience-show', 'profile-info-work-experience-add', 'profile-info-work-experience-edit', 'profile-info-work-experience-delete', 'profile-info-dependents-show', 'profile-info-dependents-add', 'profile-info-dependents-edit', 'profile-info-dependents-delete', 'profile-info-benefits-show', 'profile-info-assets-show', 'profile-info-assets-add', 'profile-info-assets-edit', 'profile-info-assets-delete', 'profile-info-attendance-show', 'profile-info-documents-show', 'profile-info-documents-add', 'profile-info-documents-edit', 'profile-info-documents-delete', 'profile-info-requests-show', 'profile-info-request-letter-show', 'profile-info-request-letter-add', 'profile-info-request-letter-edit', 'profile-info-request-letter-delete', 'profile-info-request-leave-show', 'profile-info-request-leave-add', 'profile-info-request-leave-edit', 'profile-info-request-leave-delete', 'profile-info-request-ticket-show', 'profile-info-request-ticket-add', 'profile-info-request-ticket-edit', 'profile-info-request-ticket-delete', 'settings-appearance-show', 'system-support-show', 'settings-about-show'];
        //        $userPermissions = Permission::query()->whereIn('name', $userPermissionsArray)->get();

        // Sync all permissions to the admin role
        $adminRole->syncPermissions($adminPermissions);

        // Sync permissions to the Site Manager role
        $siteManagerRole->syncPermissions($siteManagerPermissions);

        $storeKeeperRole->syncPermissions($storeKeeperPermissions);
    }
}
