<?php

namespace App\Console\Commands;

use App\Jobs\PullAdGroups;
use App\Jobs\PullAds;
use App\Jobs\PullCampaigns;
use Illuminate\Console\Command;

class PullContents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contents:pull';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull campaigns / ad groups / ads.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        PullCampaigns::dispatch()->onQueue('high');
        PullAdGroups::dispatch()->onQueue('high');
        PullAds::dispatch()->onQueue('high');
    }
}
