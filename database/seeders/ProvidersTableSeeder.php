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
            'icon' => '/icons/Gemini.png',
            'created_at' => Carbon::now()
        ]);
        DB::table('providers')->insert([
            'label' => 'Outbrain',
            'slug' => 'outbrain',
            'scopes' => null,
            'icon' => '/icons/Outbrain.png',
            'created_at' => Carbon::now()
        ]);
        DB::table('providers')->insert([
            'label' => 'Twitter',
            'slug' => 'twitter',
            'scopes' => null,
            'icon' => '/icons/Twitter.png',
            'created_at' => Carbon::now()
        ]);
        DB::table('providers')->insert([
            'label' => 'Taboola',
            'slug' => 'taboola',
            'scopes' => null,
            'icon' => '/icons/Taboola.png',
            'created_at' => Carbon::now()
        ]);
        DB::table('providers')->insert([
            'label' => 'Yahoo Japan',
            'slug' => 'yahoojp',
            'scopes' => json_encode(['yahooads']),
            'icon' => '/icons/YahooJP.png',
            'created_at' => Carbon::now()
        ]);
    }
}
