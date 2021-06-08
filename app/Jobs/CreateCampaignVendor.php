<?php

namespace App\Jobs;

use App\Models\User;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateCampaignVendor implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $vendor;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($vendor)
    {
        $this->vendor = $vendor;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $ad_vendor_class = 'App\\Utils\\AdVendors\\' . ucfirst($this->vendor['slug']);

        return (new $ad_vendor_class())->storeCampaignVendors($this->vendor);
    }
}
