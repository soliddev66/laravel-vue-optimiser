<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use DB;

class UpdateCurrencyCommand_gemini_performance_stats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency:gemini_performance_stats';

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
        echo 'OK';
        // DB::beginTransaction();

        // try {
        //     DB::update('UPDATE gemini_performance_stats SET spend = spend * 0.13, max_bid = max_bid * 0.13, ad_extn_spend = ad_extn_spend * 0.13, average_cpc = average_cpc * 0.13, average_cpm = average_cpm * 0.13, cost_per_video_view = cost_per_video_view * 0.13');

        //     DB::commit();

        //     file_put_contents('public/gemini_performance_stats', 'SUCCESS');

        // } catch (\Throwable $e) {
        //     DB::rollback();

        //     file_put_contents('public/gemini_performance_stats', 'FAILED');
        //     throw $e;
        // }

        return 1;
    }
}
