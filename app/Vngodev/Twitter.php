<?php

namespace App\Vngodev;

use App\Jobs\PullTwitterReport;
use App\Models\Provider;
use DB;
use Dorantor\FileLock;

class Twitter
{
    public function __construct()
    {

    }

    public static function getReport()
    {
        $lock = new FileLock(storage_path('logs/pull_twitter_report.lock'));
        if ($lock->acquire()) {
            DB::table('campaigns')->where('provider_id', 3)->chunkById(10, function ($campaigns) {
                foreach ($campaigns as $campaign) {
                    PullTwitterReport::dispatch($campaign);
                    sleep(10);
                }
            });
            $lock->release();
        } else {
            echo('Nope, 1 process is running!' . PHP_EOL);
        }
    }
}
