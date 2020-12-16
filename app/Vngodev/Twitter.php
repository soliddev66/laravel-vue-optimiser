<?php

namespace App\Vngodev;

use DB;

use App\Models\User;
use App\Models\Provider;
use App\Jobs\PullTwitterReport;

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
            DB::table('user_providers')->where('provider_id', 3)->chunkById(5, function ($user_providers) {
                foreach ($user_providers as $user_provider) {
                    $campaign_accounts = DB::table('campaigns')->select('open_id', 'provider_id', 'advertiser_id')->groupBy('open_id', 'provider_id', 'advertiser_id')->where([
                        'provider_id' => $user_provider->provider_id,
                        'open_id' => $user_provider->open_id
                    ])->get();

                    foreach ($campaign_accounts as $campaign_account) {
                        DB::table('campaigns')->where([
                            'advertiser_id' => $campaign_account->advertiser_id
                        ])->chunkById(20, function ($campaigns) use ($campaign_account) {
                            PullTwitterReport::dispatch($campaigns, $campaign_account);
                            sleep(10);
                        });
                    }
                }
            });
            $lock->release();
        } else {
            echo('Nope, 1 process is running!' . PHP_EOL);
        }
    }
}
