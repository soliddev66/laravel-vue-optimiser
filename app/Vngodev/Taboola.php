<?php

namespace App\Vngodev;

use App\Jobs\PullTaboolaReport;
use App\Jobs\SyncAd;

class Taboola
{
    public function __construct() {}

    public static function getReport()
    {
        PullTaboolaReport::dispatch();
    }

    public static function syncAds()
    {
        SyncAd::dispatch();
    }
}
