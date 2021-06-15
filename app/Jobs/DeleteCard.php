<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteCard implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $card_id;
    protected $provider;
    protected $account;
    protected $advertiser;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, $card_id, $provider, $account, $advertiser)
    {
        $this->user = $user;
        $this->card_id = $card_id;
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

        (new $ad_vendor_class)->deleteCard($this->user, $this->card_id, $this->provider, $this->account, $this->advertiser);
    }
}
