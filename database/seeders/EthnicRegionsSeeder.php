<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class EthnicRegionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
       public function run(): void
    {
        $ethnicRegions = [
            'Yoruba',
            'Hausa',
            'Igbo',
            'Fulani',
            'Tiv',
            'Idoma',
            'Igala',
            'Nupe',
            'Gbagyi (Gwari)',
            'Jukun',
            'Berom',
            'Angas',
            'Tarok',
            'Ijaw',
            'Itsekiri',
            'Urhobo',
            'Isoko',
            'Efik',
            'Ibibio',
            'Annang',
            'Ogoni',
            'Edo (Bini)',
            'Ebira',
            'Ekoi',
            'Yala',
            'Kanuri',
            'Babur / Bura',
            'Margi',
            'Higgi',
            'Kilba',
            'Mumuye',
            'Bachama',
            'Tangale',
            'Waja',
            'Shuwa Arab',
            'Tuareg',
        ];

        $data = collect($ethnicRegions)->map(function ($name) {
            return [
                'name' => $name,
                'slug' => Str::slug($name),
            ];
        })->toArray();

        DB::table('ethnic_regions')->insert($data);
    }

}
