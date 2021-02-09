<?php

namespace App\Jobs;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PullRedTrackReport implements ShouldQueue
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
        $date = $this->date;
        Campaign::chunk(10, function($campaigns) use ($date) {
            foreach ($campaigns as $campaign) {
                $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);

                (new $adVendorClass())->pullRedTrack($campaign, $date);
            }
        });

    }
}
