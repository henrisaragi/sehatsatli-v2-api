<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserPasswordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $existing_users = DB::table('users')->update(['password' => Hash::make(md5('sehatsatli'))]);

        // foreach ($existing_users as $user) {
        //     $user->password = Hash::make(sha1('sehatsatli'));
        //     $user->save();
        // }
    }
}
