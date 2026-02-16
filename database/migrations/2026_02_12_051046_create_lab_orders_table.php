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
        Schema::create('lab_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('consultation_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('doctor_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('patient_id')->constrained('users')->onDelete('cascade');
            $table->string('test_name');
            $table->text('instructions')->nullable();
            $table->string('priority')->default('routine');
            $table->string('status')->default('pending');
            $table->timestamp('ordered_at')->nullable();
            $table->timestamp('collected_at')->nullable();
            $table->timestamp('resulted_at')->nullable();
            $table->text('results')->nullable();
            $table->string('result_file')->nullable();

            $table->foreignUuid('lab_technician_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['patient_id', 'status']);
            $table->index(['doctor_id', 'created_at']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_orders');
    }
};
