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
        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 150)->index();
            $table->string('slug')->unique()->index();
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'archived', 'pending'])->default('pending')->index();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('project_users', function (Blueprint $table) {
            $table->primary(['project_id', 'user_id'], 'project_user_primary');
            $table->foreignUuid('project_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->enum('role', ['owner', 'admin', 'editor', 'viewer', 'commenter'])->default('viewer')->index();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_users');
        Schema::dropIfExists('projects');
    }
};
