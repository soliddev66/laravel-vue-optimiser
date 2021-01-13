<?php

namespace App\Vngodev;

use App\Jobs\PullTaboolaReport;
use App\Jobs\SyncAd;
use DB;
use App\Models\Campaign;
use App\Models\Ad;

class Taboola
{
    public function __construct() {}

    public static function getReport()
    {
        Campaign::where('provider_id', 4)->chunk(10, function ($campaigns) {
            PullTaboolaReport::dispatch($campaigns);
        });
    }

    public static function syncAds()
    {
        Ad::where(['provider_id' => 4, 'synced' => 0])->chunk(10, function ($ads) {
            foreach ($ads as $ad) {
                SyncAd::dispatch($ad);
            }
        });
    }
}
