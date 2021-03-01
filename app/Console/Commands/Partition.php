<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Brokenice\LaravelMysqlPartition\Schema\Schema;

class Partition extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'partition:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Partition large tables.';

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
        // Redtrack_reports
        Schema::partitionByYearsAndMonths('redtrack_reports', 'date', 2021);
    }
}
