<?php

namespace App\Vngodev;

use App\Jobs\PullOutbrainRedTrack;
use App\Jobs\PullRedTrack;
use App\Models\RedtrackReport;
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
        foreach (User::all() as $key => $user) {
            foreach ($user->campaigns()->where('status', 'ACTIVE')->get() as $index => $campaign) {
                PullRedTrack::dispatch($campaign);
            }
            foreach ($user->outbrainCampaigns()->where('status', 'ACTIVE')->get() as $index => $campaign) {
                PullOutbrainRedTrack::dispatch($campaign);
            }
        }
    }
}
