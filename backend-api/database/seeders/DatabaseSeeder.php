<?php

namespace Database\Seeders;

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    { 
        // Daftar permissions
        $permissions = [
            'show user', 'create user', 'edit user', 'delete user',
            'show permission', 'create permission', 'edit permission', 'delete permission',
            'show role', 'create role', 'edit role', 'delete role',
            'update issue', 'assign issue', 
            'assign project'
        ];

        // Buat semua permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'api']);
            Log::info("Permission '{$permission}' created or already exists.");
        }

        // Daftar roles
        $roles = [
            'Super Admin',
            'Admin',
            'Developer',
            'User'
        ];

        // Buat roles
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'api']);
            Log::info("Role '{$roleName}' created.");
        }

        // Buat Super Admin user dan assign role serta permissions langsung
        $superAdminUser = User::firstOrCreate([
            'email' => 'superadmin@gmail.com'
        ], [
            'name' => 'Super Administrator',
            'password' => Hash::make('SuperAdminSentryOP'),
            'is_protected' => true,
        ]);
        $superAdminUser->assignRole('Super Admin');
        $superAdminUser->syncPermissions($permissions); // Assign all permissions langsung ke user
        Log::info("Super Admin user created.");

        // Buat Admin user dan assign role serta permissions langsung
        $adminPermissions = [
            'show user', 'edit user', 'create user', 'delete user',
            'update issue', 'assign issue', 'assign project'
        ];
        $adminUser = User::firstOrCreate([
            'email' => 'admin@gmail.com'
        ], [
            'name' => 'Administrator',
            'password' => Hash::make('admin123'),
        ]);
        $adminUser->assignRole('Admin');
        $adminUser->syncPermissions($adminPermissions); // Assign selected permissions langsung ke user
        Log::info("Admin user created.");

        // Buat Developer user dan assign role serta permissions langsung
        $developerPermissions = ['update issue', 'assign issue'];
        $developerUser = User::firstOrCreate([
            'email' => 'developer@gmail.com'
        ], [
            'name' => 'Developer User',
            'password' => Hash::make('developer123'),
        ]);
        $developerUser->assignRole('Developer');
        $developerUser->syncPermissions($developerPermissions); // Assign selected permissions langsung ke user
        Log::info("Developer user created.");

        // Buat Regular User dan assign role tanpa permissions
        $regularUser = User::firstOrCreate([
            'email' => 'user@gmail.com'
        ], [
            'name' => 'Regular User',
            'password' => Hash::make('user123'),
        ]);
        $regularUser->assignRole('User'); // Role diberikan, tapi tidak ada permissions langsung
        Log::info("Regular User created.");
    }
}
