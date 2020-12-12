<?php

namespace App\Jobs;

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

    protected $campaigns;
    protected $campaign_account;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($campaigns, $campaign_account)
    {
        $this->campaigns = $campaigns;
        $this->campaign_account = $campaign_account;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $current_date = new DateTime;
            $next_date = (new DateTime)->add(new DateInterval('P1D'));

            $campaign_ids = [];

            foreach ($this->campaigns as $campaign) {
                $campaign_ids[] = $campaign->campaign_id;
            }

            $api = new TwitterAPI(UserProvider::where([
                'provider_id' => $this->campaign_account->provider_id,
                'open_id' => $this->campaign_account->open_id
            ])->first(), $this->campaign_account->advertiser_id);

            foreach (TwitterReport::METRIC_GROUPS as $metric_group) {
                foreach (TwitterReport::PLACEMENTS as $placement) {
                    $report_datas = $api->getCampaignStats([
                        'metric_groups' => $metric_group,
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
                                'metric_groups' => $metric_group,
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
            }
        } catch (\Exception $e) {
            var_dump($e->getMessage());
        }
    }
}
