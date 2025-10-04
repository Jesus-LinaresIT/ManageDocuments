<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DocumentTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $documentTypes = [
            [
                'name' => 'Propuesta de Proyecto',
                'sequence' => 1,
                'allowed_mime' => ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
                'max_mb' => 20
            ],
            [
                'name' => 'Plan de Trabajo',
                'sequence' => 2,
                'allowed_mime' => ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
                'max_mb' => 20
            ],
            [
                'name' => 'Informe de Avance',
                'sequence' => 3,
                'allowed_mime' => ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
                'max_mb' => 20
            ],
            [
                'name' => 'Informe Final',
                'sequence' => 4,
                'allowed_mime' => ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
                'max_mb' => 20
            ],
            [
                'name' => 'Evidencias',
                'sequence' => 5,
                'allowed_mime' => ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
                'max_mb' => 20
            ]
        ];

        foreach ($documentTypes as $type) {
            DocumentType::create($type);
        }
    }
}
