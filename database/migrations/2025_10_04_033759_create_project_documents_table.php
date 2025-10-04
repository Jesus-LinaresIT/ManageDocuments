<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('project_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained();
            $table->foreignId('document_type_id')->constrained();
            $table->enum('status', ['pending', 'sent', 'approved_stage1', 'in_stage2', 'approved', 'denied'])->default('pending');
            $table->text('last_observation')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamps();
            
            $table->unique(['project_id', 'document_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_documents');
    }
};
