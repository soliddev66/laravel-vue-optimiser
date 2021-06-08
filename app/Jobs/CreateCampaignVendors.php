<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\LazyCollection;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Events\CampaignVendorCreated;

class CreateCampaignVendors implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $vendors;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($vendors)
    {
        $this->vendors = $vendors;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $jobs = [];

        foreach ($this->vendors as $vendor) {
            if ($vendor['selected']) {
                $jobs[] = new CreateCampaignVendor($vendor);
            }
        }

        Bus::batch($jobs)
            ->allowFailures()
            ->then(function (Batch $batch) {

            })
            ->finally(function (Batch $batch) {

            })
            ->dispatch();
    }
}
