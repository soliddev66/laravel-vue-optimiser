<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use DB;

class UpdateCurrencyCommand_gemini_slot_performance_stats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency:gemini_slot_performance_stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update currency';

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
        DB::transaction(function () {
            DB::update('UPDATE gemini_slot_performance_stats SET average_cpc = average_cpc * 0.13, spend = spend * 0.13');

            file_put_contents('public/gemini_slot_performance_stats', 'SUCCESS');

            $this->info('The command was successful!');
        });

        return 1;
    }
}
