<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\User;
use App\Vngodev\Token;
use GuzzleHttp\Client;
use Hborras\TwitterAdsSDK\TwitterAds;
use Hborras\TwitterAdsSDK\TwitterAds\Account;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class PullCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->user->providers as $user_provider) {
            $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($user_provider->provider->slug);

            (new $adVendorClass)->pullCampaign($user_provider);
        }
    }
}
