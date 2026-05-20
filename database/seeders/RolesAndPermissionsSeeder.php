<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $projectManagerRole = Role::firstOrCreate(['name' => 'project_manager']);
        $testerRole = Role::firstOrCreate(['name' => 'tester']);
        $developerRole = Role::firstOrCreate(['name' => 'developer']);

        // permissions
        $permissionsForProjectManager = [
            'manage team',
            'assign bug',
        ];
        $permissionsForTester = [
            'create bug',
        ];
        $permissionsForDeveloper = [
            'solve bug',
        ];

        $allPermissions=array_merge($permissionsForProjectManager,$permissionsForTester,$permissionsForDeveloper);
         foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $projectManagerRole->syncPermissions($permissionsForProjectManager);
        $testerRole->syncPermissions($permissionsForTester);
        $developerRole->syncPermissions($permissionsForDeveloper);
    }
}
