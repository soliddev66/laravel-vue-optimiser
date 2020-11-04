<?php

namespace Database\Seeders;

use DB;
use Database\Seeders\ProvidersTableSeeder;
use Database\Seeders\TrackersTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $this->call(ProvidersTableSeeder::class);
        $this->call(TrackersTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(RuleConditionTypesTableSeeder::class);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
