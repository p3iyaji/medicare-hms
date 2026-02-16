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
        Schema::create('user_specializations', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('specialization_id')->constrained('specializations')->onDelete('cascade');
            $table->integer('years_of_experience')->default(0);
            $table->string('certification_number', 100)->nullable();
            $table->timestamps();

            //using unique constraint to prevent duplicate user-specs pairs
            $table->unique(['user_id', 'specialization_id'], 'unique_user_specialization');

            //indexes
            $table->index('user_id', 'idx_user_id');
            $table->index('specialization_id', 'idx_specialization_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_specializations');
    }
};
