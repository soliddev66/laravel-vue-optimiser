<?php

namespace App\Console\Commands;

use App\Models\GeminiJob;
use Illuminate\Console\Command;

class CleanGeminiJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gemini-jobs:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean Gemini jobs.';

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
        GeminiJob::where('status', 'completed')->whereRaw('DATE(created_at) < DATE_SUB(CURDATE(), INTERVAL 7 DAY)')->delete();
    }
}
