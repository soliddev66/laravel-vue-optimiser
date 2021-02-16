<?php

namespace App\Console\Commands;

use App\Vngodev\RedTrack;
use Illuminate\Console\Command;

class PullRedtrackData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redtrack:pull';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull Redtrack data for all platforms.';

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
        RedTrack::crawl();
    }
}
