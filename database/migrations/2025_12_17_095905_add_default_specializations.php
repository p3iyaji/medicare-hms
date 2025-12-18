<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $specializations = [
            ['name' => 'Cardiology', 'description' => 'Heart and cardiovascular system'],
            ['name' => 'Dermatology', 'description' => 'Skin, hair, and nails'],
            ['name' => 'Endocrinology', 'description' => 'Hormones and endocrine system'],
            ['name' => 'Gastroenterology', 'description' => 'Digestive system'],
            ['name' => 'Neurology', 'description' => 'Brain and nervous system'],
            ['name' => 'Oncology', 'description' => 'Cancer treatment'],
            ['name' => 'Pediatrics', 'description' => 'Child healthcare'],
            ['name' => 'Psychiatry', 'description' => 'Mental health'],
            ['name' => 'Radiology', 'description' => 'Medical imaging'],
            ['name' => 'Surgery', 'description' => 'Surgical procedures'],
            ['name' => 'Gynecology', 'description' => 'Female reproductive system'],
            ['name' => 'Orthopedics', 'description' => 'Musculoskeletal system'],
            ['name' => 'Ophthalmology', 'description' => 'Eye and vision care'],
            ['name' => 'ENT', 'description' => 'Ear, nose, and throat'],
            ['name' => 'Urology', 'description' => 'Urinary system'],
            ['name' => 'Pulmonology', 'description' => 'Respiratory system'],
            ['name' => 'Nephrology', 'description' => 'Kidney diseases'],
            ['name' => 'Hematology', 'description' => 'Blood disorders'],
            ['name' => 'General Practice', 'description' => 'Primary care physician'],
            ['name' => 'Emergency Medicine', 'description' => 'Emergency care'],
        ];
            foreach ($specializations as $specialization) {
                        DB::table('specializations')->insert([
                            'name' => $specialization['name'],
                            'description' => $specialization['description'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
