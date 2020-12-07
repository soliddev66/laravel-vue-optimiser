<?php

namespace App\Vngodev;

use DB;

use App\Models\Provider;
use App\Jobs\PullTwitterReport;

class Twitter
{
    public function __construct()
    {

    }

    public static function getReport()
    {
        $provider = Provider::where('slug', 'twitter')->firstOrFail();

        DB::table('campaigns')->where('provider_id', $provider->id)->chunkById(10, function ($campaigns) {
            foreach ($campaigns as $campaign) {
                PullTwitterReport::dispatch($campaign);
            }
        });
    }
}
