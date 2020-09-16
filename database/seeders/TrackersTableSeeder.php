<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;

class TrackersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('trackers')->truncate();
        DB::table('trackers')->insert([
            'label' => 'RedTrack',
            'slug' => 'redtrack'
        ]);
    }
}
