<?php

namespace Database\Seeders;

use DB;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RuleGroupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('rule_groups')->insert([
            'user_id' => 1,
            'name' => 'Group 1',
            'created_at' => Carbon::now()
        ]);
    }
}
