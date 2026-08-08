<?php

namespace Database\Seeders;

use App\Models\UPT;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UptSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $existing_upt = DB::table('ms_upt')->get('*');
        $upt_sms_data = DB::table('ms_upt_sms')->get('*');
        foreach ($existing_upt as $upt) {

            // Cari ke ms_upt_sms dengan upt id yang sama
            // Tiap record yang ditemukan, jadikan ke array, masukkan dalam 1 variabel array
            $upt_heads = [];

            $sms_datas = $upt_sms_data->where('upt_id', '==', $upt->id);
            foreach ($sms_datas as $sms_data) {

                $array_tambahan = [
                    'name' => $sms_data->name,
                    'upt_id' => $sms_data->upt_id,
                    'occupation' => $sms_data->occupation,
                    'email' => $sms_data->email,
                    'phone_1' => $sms_data->phone_1,
                    'phone_2' => $sms_data->phone_2,
                    'sms_type' => $sms_data->name,
                    //...
                ];

                array_push($upt_heads, $array_tambahan);
            }

            Upt::create([
                'id' => $upt->id,
                'creator' => 1,
                'name' => $upt->name,
                'type' => $upt->type,
                'upt_type' => $upt->upt_type,
                // 'unit_id'=>$upt,
                'province_id' => $upt->province,
                //'location' => $upt->location,
                'upt_heads' => $upt_heads
            ]);
        }
        DB::statement("DROP TABLE ms_upt");
        DB::statement("DROP TABLE ms_upt_sms");

        $csvFile = fopen(base_path("database/ksda.csv"), "r");

        // Enrique 
        // Map dengan data di province

        $firstline = true;
        while (($data = fgetcsv($csvFile, 2000, ",")) !== FALSE) {
            if (!$firstline) {
                if ($data[0] != null) {
                    Upt::create([
                        'creator' => 1,
                        "name" => $data['0'],
                        "upt_type" => $data['1'],
                        // "province_name" => $data['2'],
                        "province_id" => $data['10'],
                        'type' => $data['9'],

                        "address" => $data['3'],
                        "latitude" => $data['7'],
                        "longitude" => $data['8'],

                    ]);
                }
            }
            $firstline = false;
        }

        fclose($csvFile);
    }
}
