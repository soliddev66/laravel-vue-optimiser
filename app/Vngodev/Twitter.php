<?php

namespace App\Vngodev;

use App\Jobs\PullTwitterReport;

class Twitter
{
    public function __construct() {}

    public static function getReport($date = null)
    {
        PullTwitterReport::dispatch($date)->onQueue('low');
    }
}
