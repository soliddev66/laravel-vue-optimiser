<?php

namespace App\Vngodev;

use App\Jobs\PullOutbrainReport;
use App\Models\Campaign;
use Dorantor\FileLock;

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
        $lock = new FileLock(storage_path('logs/pull_redtrack_report.lock'));
        if ($lock->acquire()) {
            Campaign::where('provider_id', 2)->chunk(10, function ($campaigns) {
                foreach ($campaigns as $key => $campaign) {
                    PullOutbrainReport::dispatch($campaign);
                    sleep(10);
                }
            });
            $lock->release();
        } else {
            echo('Nope, 1 process is running!' . PHP_EOL);
        }
    }
}
