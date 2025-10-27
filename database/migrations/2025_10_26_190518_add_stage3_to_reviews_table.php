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
        // Modificar el enum para agregar stage3
        DB::statement("ALTER TABLE reviews MODIFY COLUMN stage ENUM('stage1', 'stage2', 'stage3') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir a los valores originales
        DB::statement("ALTER TABLE reviews MODIFY COLUMN stage ENUM('stage1', 'stage2') NOT NULL");
    }
};