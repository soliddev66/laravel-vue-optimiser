<?php

namespace App\Console\Commands;

use DB;
use DateTime;
use Exception;
use DateInterval;

use Illuminate\Console\Command;

use App\Models\Provider;
use App\Models\UserProvider;
use App\Models\TwitterReport;

use App\Endpoints\TwitterAPI;

use Hborras\TwitterAdsSDK\TwitterAds\Campaign\Campaign;

class TwitterCampaignReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitter:campaign:report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get twitter campaigns report';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $currentDate = new DateTime;
        $nextDate = (new DateTime)->add(new DateInterval('P1D'));

        $provider = Provider::where('slug', 'twitter')->firstOrFail();

        DB::table('campaigns')->where('provider_id', $provider->id)->chunkById(10, function ($campaigns) use ($currentDate, $nextDate) {
            foreach ($campaigns as $item) {
                try {
                    foreach (TwitterReport::METRIC_GROUPS as $metric_group) {
                        foreach (TwitterReport::PLACEMENTS as $placement) {
                            $api = new TwitterAPI(UserProvider::where('provider_id', $item->provider_id)->where('open_id', $item->open_id)->first(), $item->advertiser_id);

                            $report = TwitterReport::firstOrNew([
                                'metric_groups' => $metric_group,
                                'campaign_id' => $item->id,
                                'granularity' => 'DAY',
                                'placement' => $placement,
                                'start_time' => $currentDate->format('Y-m-d'),
                                'end_time' => $nextDate->format('Y-m-d')
                            ]);

                            $report->data = json_encode($api->getCampaignStats($item->campaign_id, [
                                'metric_groups' => $metric_group,
                                'entity' => 'CAMPAIGN',
                                'entity_ids' => $item->campaign_id,
                                'granularity' => 'DAY',
                                'placement' => $placement,
                                'start_time' => $currentDate->format('Y-m-d'),
                                'end_time' => $nextDate->format('Y-m-d')
                            ]));

                            $report->save();
                        }
                    }
                } catch (Exception $e) {
                    echo "Failed\n";
                }
            }
        });

        return 0;
    }
}
