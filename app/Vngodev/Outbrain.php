<?php

namespace App\Vngodev;

use App\Jobs\PullOutbrainReport;
use App\Models\Campaign;

/**
 * Outbrain
 */
class Outbrain
{
    public function __construct()
    {
        //
    }

    public static function getReport()
    {
        Campaign::where('provider_id', 2)->chunk(10, function ($campaigns) {
            foreach ($campaigns as $key => $campaign) {
                PullOutbrainReport::dispatch($campaign);
                sleep(10);
            }
        });
    }
}
