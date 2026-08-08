<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $existing_users = DB::table('ms_user')->get('*');
        $existing_officers = collect(DB::table('ms_officer')->get('*'));

        foreach ($existing_users as $index => $existing_user) {

            $existing_officer = $existing_officers->firstWhere('id', $existing_user->officer_id);
            $superadmin = false;
            if (!$existing_officer) {
                $superadmin = true;
                //$this->command->info($existing_user->id);
            }
            //$this->command->info('found');
            if ($existing_officer) {
                if ($existing_officer->gender == 'L') {
                    $gender = $existing_officer->gender = 1;
                } else {
                    $existing_officer->gender == 'P';
                    $gender = $existing_officer->gender = 2;
                }
            }

            $user = new User();
            $user->id = $existing_user->id;
            $user->username = md5($existing_user->email);
            if ($existing_officer && $existing_officer->phone) {
                $user->username1 = md5($existing_officer->phone);
            }
            $user->password = Hash::make(md5('sehatsatli'));
            $user->fill([
                'name' => $existing_user->name,
                'email' => $existing_user->email,
                'phone' => $existing_officer ? $existing_officer->phone : null,
                'phones' => [],
                'gender' => $existing_officer ? $gender : 2, //"P"
                'occupation' => $existing_officer ? $existing_officer->occupation : "Admin Pusat",
                'user_level' => $existing_user->group,
                'upt_id' => $existing_user->upt_id,
                'upt_type' => $existing_officer ? $existing_officer->upt_type : "PUSAT",
                'trained' => $existing_user->trained,
                'is_doctor' => false,
                'show_in_contact' => false,
                'training_mode' => false,
                'web_admin' => $existing_user->group < 4,
                'all_notification' => true,
            ]);

            $user->save();
        }

        DB::statement("DROP TABLE ms_user");
        DB::statement("DROP TABLE ms_officer");
    }
}
