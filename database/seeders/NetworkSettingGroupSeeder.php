<?php

namespace Database\Seeders;

use DB;
use Carbon\Carbon;

use Illuminate\Database\Seeder;

class NetworkSettingGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('network_setting_groups')->insert([[
            'name' => 'Dictionary',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Homepage',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Answers',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Apple News',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Finance',
            'created_at' => Carbon::now()
        ]]);
    }
}
