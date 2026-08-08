<?php

namespace Database\Seeders;

use App\Models\Species;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SpeciesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $species_animals = DB::table('ms_animal_name')->get('*');
        foreach ($species_animals as $species_animal) {
            Species::create([
                'id' => $species_animal->id,
                'creator' => 1,
                'category' => $species_animal->animal_type == "TUMBUHAN" ? 2 : 1,
                'code' => $species_animal->code,
                'name' => ucwords($species_animal->name),
                'latin_name' => ucwords($species_animal->latin_name),
                'type' => $species_animal->animal_type,
                'priority' => $species_animal->mark == "PRIORITAS",
                'protected' => true
            ]);
        }
        DB::statement("DROP TABLE ms_animal_name");
        DB::statement("DROP TABLE ms_animal_name_v1");
    }
}
