<?php

namespace App\Jobs;

use DB;
use DateTime;
use Exception;
use DateInterval;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Models\Campaign;
use App\Models\UserProvider;
use App\Models\TwitterReport;

use App\Endpoints\TwitterAPI;

class PullTwitterReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $current_date = new DateTime;
        $next_date = (new DateTime)->add(new DateInterval('P1D'));

        DB::table('user_providers')->where('provider_id', 3)->chunkById(5, function ($user_providers) use ($current_date, $next_date) {
            foreach ($user_providers as $user_provider) {
                $campaign_accounts = DB::table('campaigns')->select('open_id', 'provider_id', 'advertiser_id')->groupBy('open_id', 'provider_id', 'advertiser_id')->where([
                    'provider_id' => $user_provider->provider_id,
                    'open_id' => $user_provider->open_id
                ])->get();

                foreach ($campaign_accounts as $campaign_account) {
                    DB::table('campaigns')->where(['advertiser_id' => $campaign_account->advertiser_id])->chunkById(20, function ($campaigns) use ($campaign_account, $current_date, $next_date) {
                        $campaign_ids = [];

                        foreach ($campaigns as $campaign) {
                            $campaign_ids[] = $campaign->campaign_id;
                        }

                        $api = new TwitterAPI(UserProvider::where([
                            'provider_id' => $campaign_account->provider_id,
                            'open_id' => $campaign_account->open_id
                        ])->first(), $campaign_account->advertiser_id);

                        foreach (TwitterReport::PLACEMENTS as $placement) {
                            $report_datas = $api->getCampaignStats([
                                'metric_groups' => implode(',', TwitterReport::METRIC_GROUPS),
                                'entity' => 'CAMPAIGN',
                                'entity_ids' => implode(',', $campaign_ids),
                                'granularity' => 'DAY',
                                'placement' => $placement,
                                'start_time' => $current_date->format('Y-m-d'),
                                'end_time' => $next_date->format('Y-m-d')
                            ]);

                            foreach ($report_datas as $report_data) {
                                $campaign = Campaign::where('campaign_id', $report_data->id)->first();
                                if ($campaign) {
                                    $report = TwitterReport::firstOrNew([
                                        'campaign_id' => $campaign->id,
                                        'granularity' => 'DAY',
                                        'placement' => $placement,
                                        'start_time' => $current_date->format('Y-m-d'),
                                        'end_time' => $next_date->format('Y-m-d')
                                    ]);

                                    $report->data = json_encode($report_data->id_data);

                                    $report->save();
                                }
                            }
                        }

                        sleep(10);
                    });
                }
            }
        });
    }
}
