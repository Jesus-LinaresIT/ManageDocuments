<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\DocumentType;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuarios demo
        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'admin@fica.edu.sv',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('Administrador');

        $teacher = User::create([
            'name' => 'Dr. Juan Pérez',
            'email' => 'juan.perez@fica.edu.sv',
            'password' => Hash::make('password'),
        ]);
        $teacher->assignRole('Docente');

        $academicReviewer = User::create([
            'name' => 'Dra. María González',
            'email' => 'maria.gonzalez@fica.edu.sv',
            'password' => Hash::make('password'),
        ]);
        $academicReviewer->assignRole('Revisor Académico');

        $socialReviewer = User::create([
            'name' => 'Lic. Carlos Rodríguez',
            'email' => 'carlos.rodriguez@fica.edu.sv',
            'password' => Hash::make('password'),
        ]);
        $socialReviewer->assignRole('Revisor Proyección Social');

        // Crear proyecto demo
        $project = Project::create([
            'name' => 'Proyecto de Desarrollo Comunitario en San Salvador',
            'period' => '2024-1',
            'unit' => 'FICA',
            'target_date' => '2024-12-15',
            'teacher_id' => $teacher->id,
            'rev_academic_id' => $academicReviewer->id,
            'rev_social_id' => $socialReviewer->id,
        ]);

        // Crear los 5 ProjectDocument para el proyecto
        $documentTypes = DocumentType::all();
        foreach ($documentTypes as $documentType) {
            ProjectDocument::create([
                'project_id' => $project->id,
                'document_type_id' => $documentType->id,
                'status' => 'pending',
            ]);
        }

        // Crear log de auditoría
        AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'project.create',
            'meta' => [
                'project_id' => $project->id,
                'project_name' => $project->name,
            ]
        ]);
    }
}
