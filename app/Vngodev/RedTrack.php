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

    public static function crawl()
    {
        PullRedTrackReport::dispatch()->onQueue('low-queues');
    }
}
