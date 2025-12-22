<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions FIRST
        $permissions = [
            'view dashboard',
            'view profile',
            'edit profile',
            'view patients',
            'manage patients',
            'view medical records',
            'manage medical records',
            'view appointments',
            'manage appointments',
            'view lab results',
            'manage lab results',
            'manage users',
            'manage roles',
            'manage settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $roles = [
            'patient' => [
                'view dashboard',
                'view profile',
                'edit profile',
                'view appointments',
            ],
            'doctor' => [
                'view dashboard',
                'view profile',
                'edit profile',
                'view patients',
                'manage patients',
                'view medical records',
                'manage medical records',
                'view appointments',
                'manage appointments',
                'view lab results',
            ],
            'nurse' => [
                'view dashboard',
                'view profile',
                'edit profile',
                'view patients',
                'view medical records',
                'view appointments',
                'manage appointments',
            ],
            'admin' => [
                'view dashboard',
                'view profile',
                'edit profile',
                'manage users',
                'manage roles',
                'manage settings',
            ],
            'lab_technician' => [
                'view dashboard',
                'view profile',
                'edit profile',
                'view lab results',
                'manage lab results',
            ],
            'caregiver' => [
                'view dashboard',
                'view profile',
                'edit profile',
                'view patients',
                'view medical records',
            ],
        ];

        // Create roles and assign permissions
        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->givePermissionTo($rolePermissions);
        }
    }
}