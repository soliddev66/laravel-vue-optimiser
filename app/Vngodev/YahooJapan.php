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

    public static function getReport($date = null)
    {
        PullYahooJapanReport::dispatch($date)->onQueue('low');
    }
}
