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
        Schema::create('note_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('doctor_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->text('content');
            $table->string('category')->default('consultation');
            $table->json('placeholders')->nullable();
            $table->boolean('is_shared')->default(false);
            $table->integer('usage_count')->default(0);
            $table->timestamps();
            $table->index(['doctor_id', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('note_templates');
    }
};
