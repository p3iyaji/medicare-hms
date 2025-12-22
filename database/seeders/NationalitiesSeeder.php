<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NationalitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
        public function run(): void
    {
        $nationalities = [
            ['name' => 'Afghan', 'code' => 'AF'],
            ['name' => 'Albanian', 'code' => 'AL'],
            ['name' => 'American', 'code' => 'US'],
            ['name' => 'Australian', 'code' => 'AU'],
            ['name' => 'British', 'code' => 'GB'],
            ['name' => 'Canadian', 'code' => 'CA'],
            ['name' => 'Chinese', 'code' => 'CN'],
            ['name' => 'French', 'code' => 'FR'],
            ['name' => 'German', 'code' => 'DE'],
            ['name' => 'Indian', 'code' => 'IN'],
            ['name' => 'Italian', 'code' => 'IT'],
            ['name' => 'Japanese', 'code' => 'JP'],
            ['name' => 'Mexican', 'code' => 'MX'],
            ['name' => 'Nigerian', 'code' => 'NG'],
            ['name' => 'Pakistani', 'code' => 'PK'],
            ['name' => 'Spanish', 'code' => 'ES'],
            ['name' => 'South African', 'code' => 'ZA'],
        ];

        DB::table('nationalities')->insert($nationalities);
    }
}
