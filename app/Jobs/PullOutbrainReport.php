<?php

namespace App\Jobs;

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

    protected $campaigns;
    protected $campaign_account;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($campaigns, $campaign_account)
    {
        $this->campaigns = $campaigns;
        $this->campaign_account = $campaign_account;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Need to be updated
    }
}
