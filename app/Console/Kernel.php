<?php

namespace App\Console;

use App\Jobs\PullAds;
use App\Jobs\PullAdGroups;
use App\Jobs\PullCampaigns;
use App\Models\GeminiJob;
use App\Models\Rule;
use App\Models\User;
use App\Vngodev\Gemini;
use App\Vngodev\Outbrain;
use App\Vngodev\RedTrack;
use App\Vngodev\Taboola;
use App\Vngodev\Twitter;
use App\Vngodev\YahooJapan;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        // Report data
        $schedule->call(function () {
            Gemini::crawl();
        })->daily();
        $schedule->call(function () {
            Gemini::checkJobs();
        })->everyTenMinutes();
        $schedule->call(function () {
            Outbrain::getReport();
        })->hourly();
        $schedule->call(function () {
            Twitter::getReport();
        })->hourly();
        $schedule->call(function () {
            Taboola::getReport();
        })->hourly();
        $schedule->call(function () {
            YahooJapan::getReport();
        })->hourly();
        $schedule->call(function () {
            Taboola::syncAds();
        })->everyMinute();

        // Redtrack
        $schedule->call(function () {
            RedTrack::crawl();
        })->everyTenMinutes();

        // Campaign
        $schedule->call(function () {
            PullCampaigns::dispatch();
            PullAdGroups::dispatch();
            PullAds::dispatch();
        })->everyTenMinutes();

        // Rules
        foreach (Rule::all() as $rule) {
            $schedule->command('rule:action', [
                $rule->id
            ])->cron($this->getFrequency($rule))->appendOutputTo(storage_path('logs/commands.log'));
        }

        // Jobs retry
        $schedule->command('queue:retry all')->everyFifteenMinutes();

        // Delete unuse gemini jobs
        $schedule->call(function () {
            GeminiJob::where('status', 'completed')->delete();
        })->daily();

        // Failed jobs clear
        // $schedule->command('queue:flush')->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    private function getFrequency($rule)
    {
        switch(Rule::FREQUENCIES[$rule->interval_unit]) {
            default:
                return '*/' . $rule->interval_amount . ' * * * *';

            case 'HOURS':
                return '0 */' . $rule->interval_amount . ' * * *';

            case 'DAYS':
                return '0 */' . $rule->interval_amount * 24 . ' * * *';

            case 'WEEKS':
                return '0 */' . $rule->interval_amount * 24 * 7 . ' * * *';

            case 'MONTHS':
                return '0 0 * */' . $rule->interval_amount . ' *';

            case 'YEARS':
                return '0 0 * */' . $rule->interval_amount * 12 . ' *';
        }
    }
}
