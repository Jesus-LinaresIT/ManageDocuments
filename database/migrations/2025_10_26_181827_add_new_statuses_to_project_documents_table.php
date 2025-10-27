<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modificar el enum para agregar los nuevos estados
        DB::statement("ALTER TABLE project_documents MODIFY COLUMN status ENUM('pending', 'sent', 'approved_stage1', 'pending_stage2', 'approved_stage2', 'pending_stage3', 'approved', 'denied') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir a los estados originales
        DB::statement("ALTER TABLE project_documents MODIFY COLUMN status ENUM('pending', 'sent', 'approved_stage1', 'in_stage2', 'approved', 'denied') DEFAULT 'pending'");
    }
};