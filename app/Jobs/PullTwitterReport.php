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

use App\Models\UserProvider;
use App\Models\TwitterReport;

use App\Endpoints\TwitterAPI;

class PullTwitterReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $campaign;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($campaign)
    {
        $this->campaign = $campaign;
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

        foreach (TwitterReport::METRIC_GROUPS as $metric_group) {
            foreach (TwitterReport::PLACEMENTS as $placement) {
                $api = new TwitterAPI(UserProvider::where('provider_id', $this->campaign->provider_id)->where('open_id', $this->campaign->open_id)->first(), $this->campaign->advertiser_id);

                $report = TwitterReport::firstOrNew([
                    'metric_groups' => $metric_group,
                    'campaign_id' => $this->campaign->id,
                    'granularity' => 'DAY',
                    'placement' => $placement,
                    'start_time' => $current_date->format('Y-m-d'),
                    'end_time' => $next_date->format('Y-m-d')
                ]);

                $report->data = json_encode($api->getCampaignStats($this->campaign->campaign_id, [
                    'metric_groups' => $metric_group,
                    'entity' => 'CAMPAIGN',
                    'entity_ids' => $this->campaign->campaign_id,
                    'granularity' => 'DAY',
                    'placement' => $placement,
                    'start_time' => $current_date->format('Y-m-d'),
                    'end_time' => $next_date->format('Y-m-d')
                ]));

                $report->save();
            }
        }
    }
}
