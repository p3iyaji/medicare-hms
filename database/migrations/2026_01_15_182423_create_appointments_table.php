<?php

use App\Models\User;
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
        Schema::create('appointments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('appointment_no');
            $table->foreignUuid('patient_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('doctor_id')->constrained('users')->onDelete('cascade');
            $table->enum('appointment_type', ['consultation', 'follow up', 'emergency', 'vaccination', 'procedure', 'routine check', 'lab test', 'therapy']);
            $table->enum('status', ['scheduled', 'confirmed', 'in-progress', 'completed', 'no show', 'rescheduled']);
            $table->date('scheduled_date');
            $table->time('scheduled_time');
            $table->timestamp('consultation_started_at')->nullable();
            $table->timestamp('consultation_ended_at')->nullable();
            $table->text('doctor_notes')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('prescription')->nullable();
            $table->text('lab_orders')->nullable();
            $table->text('follow_up_instructions')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->string('estimated_duration');
            $table->time('actual_start_time')->nullable();
            $table->time('actual_end_time')->nullable();
            $table->longText('reason')->nullable();
            $table->longText('symptoms')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'emergency']);
            $table->foreignUuid('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
