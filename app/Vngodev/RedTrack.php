<?php

namespace App\Vngodev;

use App\Jobs\PullRedTrackReport;
use App\Models\Campaign;
use App\Models\User;
use App\Models\UserTracker;
use Carbon\Carbon;
use GuzzleHttp\Client;

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
        Campaign::chunk(10, function($campaigns) {
            foreach ($campaigns as $key => $campaign) {
                PullRedTrackReport::dispatch($campaign);
            }
        });
    }
}
