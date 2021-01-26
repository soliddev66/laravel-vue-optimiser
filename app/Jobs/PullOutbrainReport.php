<?php

namespace App\Jobs;

use DB;

use App\Endpoints\OutbrainAPI;
use App\Models\Campaign;
use App\Models\OutbrainReport;
use App\Models\UserProvider;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PullOutbrainReport implements ShouldQueue
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
        DB::table('campaigns')->where('provider_id', 2)->chunkById(20, function ($campaigns) {
            foreach ($campaigns as $campaign) {
                $date = Carbon::now()->format('Y-m-d');
                $api = new OutbrainAPI(UserProvider::where('provider_id', $campaign->provider_id)->where('open_id', $campaign->open_id)->first());

                $report = OutbrainReport::firstOrNew([
                    'campaign_id' => $campaign->id,
                    'date' => $date
                ]);
                $report->data = json_encode($api->getPerformanceReport($campaign, $date));

                $report->save();
                sleep(20);
            }
        });
    }
}
