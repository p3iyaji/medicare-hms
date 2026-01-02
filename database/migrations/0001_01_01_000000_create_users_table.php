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
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->uuid('uuid')->unique();
        $table->string('email')->unique();
        $table->string('phone', 20)->nullable();
        $table->timestamp('email_verified_at')->nullable();
        $table->timestamp('phone_verified_at')->nullable();
        $table->string('password');

        $table->enum('user_type', [
            'patient','doctor','nurse','admin','lab technician','caregiver'
        ])->default('patient');
        $table->string('patient_no')->unique()->nullable();

        $table->enum('title', ['Mr', 'Mrs', 'Miss', 'Ms', 'Dr', 'Prof', 'Engr', 'Barr', 'Arc', 'Chief', 'Alhaji', 'Alhaja', 'Pastor', 'Mallam', 'Oba', 'Elder', 'Sir', 'Lady', 'HRH', 'HRM'])->nullable()->default(null);        
        $table->string('first_name', 100)->nullable();
        $table->string('middle_name', 100)->nullable();

        $table->string('last_name', 100)->nullable();
        $table->date('date_of_birth')->nullable();
        $table->enum('gender', ['male','female','other','prefer not to say'])->nullable();
        $table->string('profile_image')->nullable();

        $table->foreignId('nationality_id')->nullable()->constrained('nationalities')->nullOnDelete();
        $table->foreignId('state_id')->nullable()->constrained('states')->nullOnDelete();
        $table->string('occupation', 255)->nullable();
        $table->longText('work_address')->nullable();
        $table->string('industry')->nullable();
        $table->foreignId('ethinic_region_id')->nullable()->constrained('ethnic_regions')->nullOnDelete();
        $table->text('spoken_languages')->nullable();
        $table->enum('religion', ['Christianity', 'Islam', 'None', 'Prefer not to say'])->nullable();
        $table->string('region', 100)->nullable();
        $table->string('county', 100)->nullable();
        $table->string('district', 100)->nullable();
        $table->longText('residential_address')->nullable();


        $table->boolean('is_verified')->default(false);
        $table->boolean('is_active')->default(true);
        $table->timestamp('last_login_at')->nullable();
        $table->boolean('mfa_enabled')->default(false);

        $table->rememberToken();
        $table->timestamps();
        $table->softDeletes();

        $table->index('user_type', 'idx_user_type');
        $table->index(['email','phone'], 'idx_email_phone');
    });

    Schema::create('password_reset_tokens', function (Blueprint $table) {
        $table->string('email')->index();
        $table->string('token');
        $table->timestamp('created_at')->nullable();
    });

    Schema::create('sessions', function (Blueprint $table) {
        $table->string('id')->primary();
        $table->foreignId('user_id')->nullable()
              ->constrained()
              ->nullOnDelete();
        $table->string('ip_address', 45)->nullable();
        $table->text('user_agent')->nullable();
        $table->longText('payload');
        $table->integer('last_activity')->index();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
