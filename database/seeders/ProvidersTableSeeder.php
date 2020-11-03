<?php

namespace Database\Seeders;

use DB;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ProvidersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('providers')->truncate();
        DB::table('providers')->insert([
            'label' => 'Yahoo Gemini',
            'slug' => 'yahoo',
            'scopes' => json_encode(['admg-w', 'sdps-r', 'sdpp-r']),
            'created_at' => Carbon::now()
        ]);
        DB::table('providers')->insert([
            'label' => 'Outbrain',
            'slug' => 'outbrain',
            'scopes' => null,
            'created_at' => Carbon::now()
        ]);
        DB::table('providers')->insert([
            'label' => 'Twitter',
            'slug' => 'twitter',
            'scopes' => null,
            'created_at' => Carbon::now()
        ]);
    }
}
