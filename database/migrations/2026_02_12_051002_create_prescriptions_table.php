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
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('consultation_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('doctor_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('patient_id')->constrained('users')->onDelete('cascade');
            $table->string('medication_name');
            $table->string('dosage');
            $table->string('frequency');
            $table->string('duration');
            $table->text('instructions')->nullable();
            $table->string('route')->default('oral');
            $table->string('form')->nullable();
            $table->integer('quantity')->nullable();
            $table->integer('refills')->nullable();

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('dispensed')->default(false);
            $table->timestamp('dispensed_at')->nullable();

            $table->string('status')->default('active');
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['patient_id', 'status']);
            $table->index(['doctor_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
