<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use DB;

class UpdateCurrencyCommand_gemini_ad_extension_details extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency:gemini_ad_extension_details';

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
            DB::update('UPDATE gemini_ad_extension_details SET spend = spend * 0.13, max_bid = max_bid * 0.13, average_cpc = average_cpc * 0.13, average_cost_per_install = average_cost_per_install * 0.13, average_cpm = average_cpm * 0.13');

            file_put_contents('public/gemini_ad_extension_details', 'SUCCESS');

            $this->info('The command was successful!');
        });

        return 1;
    }
}
