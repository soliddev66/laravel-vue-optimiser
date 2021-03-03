<?php

namespace App\Console\Commands;

use Brokenice\LaravelMysqlPartition\Models\Partition;
use Brokenice\LaravelMysqlPartition\Schema\Schema;
use Illuminate\Console\Command;

class PartitionTable extends Command
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
        Schema::partitionByRange('redtrack_reports', 'to_days(date)', [
            new Partition('anno0', Partition::RANGE_TYPE, 738156),
            new Partition('anno1', Partition::RANGE_TYPE, 738246),
            new Partition('anno2', Partition::RANGE_TYPE, 738368),
            new Partition('anno3', Partition::RANGE_TYPE, 738521),
            new Partition('anno4', Partition::RANGE_TYPE, 738611),
            new Partition('anno5', Partition::RANGE_TYPE, 738733),
            new Partition('anno6', Partition::RANGE_TYPE, 738886)
        ], true);
    }
}
