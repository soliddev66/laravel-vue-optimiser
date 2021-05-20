<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use DB;

class UpdateCurrencyCommand_gemini_search_stats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency:gemini_search_stats';

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
            DB::update('UPDATE gemini_search_stats SET spend = spend * 0.13, max_bid = max_bid * 0.13, average_cpc = average_cpc * 0.13');

            file_put_contents('public/gemini_search_stats', 'SUCCESS');

            $this->info('The command was successful!');
        });

        return 1;
    }
}
