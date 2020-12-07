<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Campaign;

class TwitterCampaignReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitter:campaign:report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get twitter campaigns report';

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

        return 0;
    }
}
