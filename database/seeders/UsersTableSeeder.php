<?php

namespace Database\Seeders;

use DB;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'Administrator',
            'email' => 'admin@optimiser.test',
            'email_verified_at' => Carbon::now(),
            'password' => bcrypt('123123'),
            'created_at' => Carbon::now()
        ]);
    }
}
