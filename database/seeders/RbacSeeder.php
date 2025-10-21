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
            Permission::create(['name' => $permission]);
        }

        // Crear roles
        $adminRole = Role::create(['name' => 'Administrador']);
        $teacherRole = Role::create(['name' => 'Docente']);
        $coordinatorRole = Role::create(['name' => 'Coordinador de Proyección Social']);
        $directorRole = Role::create(['name' => 'Director de Proyección Social']);
        $deanRole = Role::create(['name' => 'Decano/a']);

        // Asignar permisos a roles
        $adminRole->givePermissionTo($permissions);
        $teacherRole->givePermissionTo(['upload.documents']);
        $coordinatorRole->givePermissionTo(['manage.projects', 'review.stage1', 'upload.documents']);
        $directorRole->givePermissionTo(['review.stage2', 'view.reports']);
        $deanRole->givePermissionTo(['view.readonly', 'view.reports']);
    }
}
