<?php

namespace Database\Seeders;

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
            'name' => 'Group 1',
            'created_at' => Carbon::now()
        ]);
    }
}
