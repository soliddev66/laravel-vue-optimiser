<?php

namespace App\Console\Commands;

use App\Jobs\DeleteDuplicates;
use Illuminate\Console\Command;

class RemoveDuplicates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'duplicates:remove';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove duplicates.';

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
        DeleteDuplicates::dispatch()->onQueue('highest');
    }
}
