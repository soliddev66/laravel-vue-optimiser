<?php

namespace App\Vngodev;

use App\Jobs\PullTaboolaReport;
use DB;
use App\Models\Campaign;

class Taboola
{
    public function __construct() {}

    public static function getReport()
    {
        Campaign::where('provider_id', 4)->chunk(10, function ($campaigns) {
            PullTaboolaReport::dispatch($campaigns);
        });
    }
}
