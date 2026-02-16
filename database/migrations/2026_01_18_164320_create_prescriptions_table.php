<?php

use App\Models\Appointment;
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
            $table->uuid('id');
            $table->foreignUuid('patient_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('doctor_id')->constrained('users')->onDelete('cascade');
            $table->foreignIdFor(Appointment::class);
            $table->enum('status', ['active', 'completed', 'cancelled', 'expired']);
            $table->longText('instructions');
            $table->date('invalid_from');
            $table->date('valid_until');
            $table->integer('refills_allowed')->default(1);
            $table->integer('refills_remaining')->default(0);
            $table->timestamps();
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
