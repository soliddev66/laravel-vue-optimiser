<?php

namespace App\Vngodev;

use App\Jobs\PullRedTrackReport;

/**
 * RedTrack
 */
class RedTrack
{
    public function __construct()
    {
        //
    }

    public static function crawl($date = null)
    {
        PullRedTrackReport::dispatch($date)->onQueue('low');
    }
}
