<?php

namespace App\Jobs;

use App\Endpoints\OutbrainAPI;
use App\Models\Campaign;
use App\Models\UserProvider;
use Dorantor\FileLock;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PullOutbrainReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $campaign;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Campaign $campaign)
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
        $lock = new FileLock(storage_path('logs/pull_redtrack_report.lock'));
        if ($lock->acquire()) {
            $api = new OutbrainAPI(UserProvider::where('provider_id', $this->campaign->provider_id)->where('open_id', $this->campaign->open_id)->first());
            $promoted_links = $api->getPromotedLinks($this->campaign->campaign_id);
            if ($promoted_links && isset($promoted_links['promotedLinks'])) {
                foreach ($promoted_links['promotedLinks'] as $key => $promoted_link) {
                    $report = $api->getRealtimeReport($this->campaign, $promoted_link);
                    dd($report);
                }
            }
            $lock->release();
        } else {
            echo('Nope, 1 process is running!' . PHP_EOL);
        }
    }
}
