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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('blood_type', [
                'A+','A-','B+','B-','AB+','AB-','O+','O-'
            ])->nullable();

            $table->enum('genotype', ['AA', 'AS', 'AC', 'SS', 'SC', 'CC'])->nullable();

            $table->decimal('height', 5, 2)->nullable()->comment('Height in cm');
            $table->decimal('weight', 5, 2)->nullable()->comment('Weight in kg');

            $table->string('emergency_contact_name', 200)->nullable();
            $table->string('emergency_contact_number', 20)->nullable();
            $table->string('emergency_contact_relationship', 100)->nullable();
            $table->longText('emergency_contact_address')->nullable();
            $table->boolean('same_as_users_address')->nullable();

            $table->foreignId('primary_physician_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('insurance_provider', 200)->nullable();
            $table->string('insurance_policy_number', 100)->nullable();

            $table->text('allergies')->nullable();
            $table->text('chronic_conditions')->nullable();

            $table->timestamps();

            $table->index('primary_physician_id', 'idx_primary_physician');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
