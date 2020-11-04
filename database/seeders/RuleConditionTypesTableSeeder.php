<?php

namespace Database\Seeders;

use DB;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RuleConditionTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('rule_condition_types')->insert([
            [
                'name' => 'Tracker Click',
                'created_at' => Carbon::now()
            ],
            [
                'name' => 'Tracker Conversions',
                'created_at' => Carbon::now()
            ],
            [
                'name' => 'Tracker Revenue',
                'created_at' => Carbon::now()
            ]
        ]);
    }
}
