<?php

namespace Database\Seeders;

use DB;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RulesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('rules')->insert([
            'name' => 'Rule 1',
            'user_id' => 1,
            'rule_group_id' => 1,
            'from' => 1,
            'interval_amount' => 1,
            'status' => 'ACTIVE',
            'interval_unit' => 1,
            'created_at' => Carbon::now()
        ]);
    }
}
