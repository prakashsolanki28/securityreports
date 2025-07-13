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
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('project_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('low');
            $table->string('cve')->nullable();
            $table->text('remediation')->nullable();
            $table->timestamps();
        });

        Schema::create('assessment_evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['screenshot', 'log', 'video', 'other'])->default('screenshot');
            $table->string('path');
            $table->text('description')->nullable();
            $table->timestamps();
        });

         Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('assessment_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->onDelete('cascade');
            $table->foreignId('tag_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['assessment_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_tags');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('assessment_evidence');
        Schema::dropIfExists('assessments');
    }
};
