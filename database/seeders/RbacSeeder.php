<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RbacSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear permisos
        $permissions = [
            'manage.users',
            'manage.projects',
            'upload.documents',
            'review.stage1',
            'review.stage2',
            'view.readonly',
            'view.reports'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Crear roles
        $adminRole = Role::firstOrCreate(['name' => 'Administrador']);
        $teacherRole = Role::firstOrCreate(['name' => 'Docente']);
        $coordinatorRole = Role::firstOrCreate(['name' => 'Coordinador de Proyección Social']);
        $directorRole = Role::firstOrCreate(['name' => 'Director de Proyección Social']);
        $deanRole = Role::firstOrCreate(['name' => 'Decano/a']);

        // Asignar permisos a roles
        $adminRole->givePermissionTo($permissions);
        $teacherRole->givePermissionTo(['upload.documents']);
        $coordinatorRole->givePermissionTo(['review.stage1', 'manage.projects', 'view.readonly']);
        $directorRole->givePermissionTo(['review.stage2', 'view.readonly']);
        $deanRole->givePermissionTo(['view.readonly']);
    }
}
