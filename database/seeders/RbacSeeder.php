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
            'review.stage1',
            'review.stage2',
            'upload.documents',
            'view.readonly',
            'view.reports'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Crear roles
        $adminRole = Role::create(['name' => 'Administrador']);
        $teacherRole = Role::create(['name' => 'Docente']);
        $academicReviewerRole = Role::create(['name' => 'Revisor Académico']);
        $socialReviewerRole = Role::create(['name' => 'Revisor Proyección Social']);
        $observerRole = Role::create(['name' => 'Observador']);

        // Asignar permisos a roles
        $adminRole->givePermissionTo($permissions);
        $teacherRole->givePermissionTo(['upload.documents']);
        $academicReviewerRole->givePermissionTo(['review.stage1']);
        $socialReviewerRole->givePermissionTo(['review.stage2']);
        $observerRole->givePermissionTo(['view.readonly', 'view.reports']);
    }
}
