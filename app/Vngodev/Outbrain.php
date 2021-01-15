<?php

namespace App\Vngodev;

use App\Jobs\PullOutbrainReport;

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
        PullOutbrainReport::dispatch();
    }
}
