<?php

namespace App\Console\Commands;

use App\Vngodev\Gemini;
use Illuminate\Console\Command;

class CrawlGeminiReportData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gemini:crawl';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawl Gemini report data.';

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
        Gemini::crawl();
    }
}
