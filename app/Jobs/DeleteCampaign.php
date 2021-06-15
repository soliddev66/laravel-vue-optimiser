<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $campaign_id;
    protected $provider;
    protected $account;
    protected $advertiser;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, $campaign_id, $provider, $account, $advertiser)
    {
        $this->user = $user;
        $this->campaign_id = $campaign_id;
        $this->provider = $provider;
        $this->account = $account;
        $this->advertiser = $advertiser;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $ad_vendor_class = 'App\\Utils\\AdVendors\\' . ucfirst($this->provider);

        (new $ad_vendor_class)->deleteCampaign($this->user, $this->campaign_id, $this->provider, $this->account, $this->advertiser);
    }
}
