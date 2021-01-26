<?php

namespace App\Vngodev;

use App\Jobs\PullYahooJapanReport;

/**
 * Outbrain
 */
class YahooJapan
{
    public function __construct()
    {
        //
    }

    public static function getReport()
    {
        PullYahooJapanReport::dispatch();
    }
}
