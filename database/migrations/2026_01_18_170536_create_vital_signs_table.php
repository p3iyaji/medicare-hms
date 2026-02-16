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
        Schema::create('vital_signs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('appointment_id')->constrained('appointments')->onDelete('cascade');
            $table->foreignUuid('patient_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('recorded_by')->constrained('users')->onDelete('cascade');
            // Vital signs
            $table->decimal('temperature', 5, 2)->nullable();
            $table->integer('heart_rate')->nullable();
            $table->integer('blood_pressure_systolic')->nullable();
            $table->integer('blood_pressure_diastolic')->nullable();
            $table->integer('respiratory_rate')->nullable();
            $table->decimal('oxygen_saturation', 5, 2)->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->decimal('bmi', 5, 2)->nullable();

            // Additional info
            $table->text('notes')->nullable();
            $table->timestamp('recorded_at')->nullable();

            // Device info
            $table->string('data_source')->default('manual'); // 'manual' or 'device'
            $table->string('device_type')->nullable(); // e.g., 'blood_pressure', 'thermometer'
            $table->integer('device_id')->nullable(); // Reference to device if any
            $table->integer('pain_scale')->nullable(); // 0-10

            $table->timestamps();

            // Indexes
            $table->index(['patient_id', 'recorded_at']);
            $table->index('data_source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vital_signs');
    }
};
