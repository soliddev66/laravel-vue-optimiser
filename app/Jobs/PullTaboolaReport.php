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
use App\Models\TaboolaReport;

use App\Endpoints\TaboolaAPI;

class PullTaboolaReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $campaigns;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($campaigns)
    {
        $this->campaigns = $campaigns;
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

        foreach ($this->campaigns as $campaign) {
            $api = new TaboolaAPI(UserProvider::where([
                'provider_id' => $campaign->provider_id,
                'open_id' => $campaign->open_id
            ])->first());

            if ($api) {
                $reports = $api->getReport($campaign->advertiser_id, $campaign->campaign_id, $current_date->format('Y-m-d'), $next_date->format('Y-m-d'))['results'];

                if (count($reports) > 0) {
                    foreach ($reports as $item) {
                        $report = TaboolaReport::firstOrNew([
                            'campaign_id' => $campaign->id,
                            'start_date' => $current_date->format('Y-m-d'),
                            'end_date' => $next_date->format('Y-m-d')
                        ]);
                    }
                }
            }
        }
    }
}
