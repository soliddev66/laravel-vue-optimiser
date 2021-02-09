<?php

namespace App\Console\Commands;

use App\Vngodev\Gemini;
use App\Vngodev\Outbrain;
use App\Vngodev\RedTrack;
use App\Vngodev\Taboola;
use App\Vngodev\Twitter;
use App\Vngodev\YahooJapan;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CrawlInitData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:init {start_date?} {end_date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawl initial data for reports';

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
        if ($this->argument('start_date') && $this->argument('end_date')) {
            $start_date = Carbon::parse($this->argument('start_date'));
            $end_date = Carbon::parse($this->argument('end_date'));
        } else {
            $start_date = Carbon::now()->subDays(15);
            $end_date = Carbon::now();
        }

        Gemini::crawlRange($start_date->format('Y-m-d'), $end_date->format('Y-m-d'));

        for ($date = $start_date; $date->lte($end_date); $date->addDay()) {
            RedTrack::crawl($date->format('Y-m-d'));
            Outbrain::getReport($date->format('Y-m-d'));
            Twitter::getReport($date->format('Y-m-d'));
            Taboola::getReport($date->format('Y-m-d'));
            YahooJapan::getReport($date->format('Y-m-d'));
        }
    }
}
