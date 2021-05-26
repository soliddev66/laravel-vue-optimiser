<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use DB;

class UpdateCurrencyCommand_yahoo_japan_reports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency:yahoo_japan_reports';

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
            DB::update('UPDATE yahoo_japan_reports SET cost = cost * 0.0091, avg_cpc = avg_cpc * 0.0091, cpa = cpa * 0.0091, conversion_value = conversion_value * 0.0091, all_conversion_value = all_conversion_value * 0.0091, value_per_all_conversions = value_per_all_conversions * 0.0091, value_per_conversions_via_ad_click = value_per_conversions_via_ad_click * 0.0091, cpa_via_ad_click = cpa_via_ad_click * 0.0091, all_cpa = all_cpa * 0.0091, average_cpv = average_cpv * 0.0091');

            file_put_contents('public/yahoo_japan_reports', 'SUCCESS');

            $this->info('The command was successful!');
        });

        return 1;
    }
}
