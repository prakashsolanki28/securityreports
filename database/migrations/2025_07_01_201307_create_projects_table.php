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
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('slug')->unique()->index();
            $table->text('description')->nullable();
            $table->foreignUuid('owner_id')->constrained('users')->onUpdate('cascade')->onDelete('restrict');
            $table->boolean('status')->default(true)->index();
            $table->string('logo')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('team_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->enum('role', ['owner', 'admin', 'member', 'guest'])->default('member')->index(); // Added admin and guest roles, indexed
            $table->timestamp('joined_at')->nullable(); // Track when user joined
            $table->unique(['team_id', 'user_id'], 'team_user_unique'); // Named unique constraint
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->index();
            $table->string('slug')->unique()->index();
            $table->text('description')->nullable();
            $table->foreignId('team_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->enum('status', ['active', 'archived', 'pending'])->default('pending')->index();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('project_user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->enum('role', ['admin', 'editor', 'viewer', 'commenter', 'manager'])->default('viewer')->index(); // Added manager role
            $table->timestamp('assigned_at')->nullable(); // Track role assignment
            $table->unique(['project_id', 'user_id'], 'project_user_unique');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::dropIfExists('project_user_roles');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('team_users');
        Schema::dropIfExists('teams');
    }
};
