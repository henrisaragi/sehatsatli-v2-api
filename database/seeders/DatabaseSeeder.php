<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            OptionSeeder::class,
            SpeciesSeeder::class,
            UptSeeder::class,
            UserSeeder::class,
            DiseaseSeeder::class,
            TranferSeeder::class,
        ]);
    }
}
