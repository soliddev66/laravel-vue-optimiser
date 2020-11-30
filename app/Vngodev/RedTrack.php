<?php

namespace App\Vngodev;

use Carbon\Carbon;

use App\Models\User;
use App\Models\UserTracker;

use GuzzleHttp\Client;
use App\Jobs\PullRedTrack;

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
        foreach (User::all() as $user) {
            foreach ($user->campaigns as $campaign) {
                PullRedTrack::dispatch($campaign);
            }
        }
    }
}
