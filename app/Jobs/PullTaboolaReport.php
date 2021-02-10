<?php

namespace App\Jobs;

use App\Endpoints\TaboolaAPI;
use App\Models\Campaign;
use App\Models\TaboolaReport;
use App\Models\UserProvider;
use Carbon\Carbon;
use DateInterval;
use DateTime;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PullTaboolaReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $date;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($date)
    {
        $this->date = $date;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $target_date = Carbon::now()->format('Y-m-d');
        if ($this->date) {
            $target_date = $this->date;
        }

        Campaign::where('provider_id', 4)->chunk(10, function ($campaigns) use ($target_date) {
            foreach ($campaigns as $campaign) {
                $api = new TaboolaAPI(UserProvider::where([
                    'provider_id' => $campaign->provider_id,
                    'open_id' => $campaign->open_id
                ])->first());

                if ($api) {
                    $reports = $api->getReport($campaign->advertiser_id, $campaign->campaign_id, $target_date, $target_date)['results'];
                    if (count($reports) > 0) {
                        foreach ($reports as $item) {
                            $report = TaboolaReport::firstOrNew([
                                'campaign_id' => $campaign->id,
                                'date' => $target_date
                            ]);

                            unset($item['date']);

                            foreach (array_keys($item) as $key) {
                                $report->{$key} = $item[$key];
                            }

                            $report->save();
                        }
                    }
                }
            }
        });
    }
}
