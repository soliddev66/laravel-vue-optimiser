<?php

namespace App\Vngodev;

use App\Jobs\PullTwitterReport;

class Twitter
{
    public function __construct() {}

    public static function getReport()
    {
        PullTwitterReport::dispatch();
    }
}
