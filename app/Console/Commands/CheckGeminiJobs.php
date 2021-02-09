<?php

namespace App\Console\Commands;

use App\Vngodev\Gemini;
use Illuminate\Console\Command;

class CheckGeminiJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gemini:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checking Gemini jobs.';

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
        Gemini::checkJobs();
    }
}
