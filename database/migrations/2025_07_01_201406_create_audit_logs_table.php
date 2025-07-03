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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action'); // e.g. 'created', 'updated', 'deleted'
            $table->string('auditable_type'); // e.g. App\Models\Assessment
            $table->unsignedBigInteger('auditable_id'); // ID of the model affected
            $table->json('old_values')->nullable(); // what data was before
            $table->json('new_values')->nullable(); // what data was after
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            $table->index(['auditable_type', 'auditable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
