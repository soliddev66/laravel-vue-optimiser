<?php

namespace App\Console;

use App\Jobs\PullAd;
use App\Jobs\PullAdGroup;
use App\Jobs\PullCampaign;
use App\Models\Rule;
use App\Models\User;
use App\Vngodev\Gemini;
use App\Vngodev\Outbrain;
use App\Vngodev\RedTrack;
use App\Vngodev\Twitter;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

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
        })->everyThirtyMinutes();
        $schedule->call(function () {
            Gemini::checkJobs();
        })->everyThirtyMinutes();
        $schedule->call(function () {
            Outbrain::getReport();
        })->everyThirtyMinutes();
        $schedule->call(function () {
            Twitter::getReport();
        })->everyThirtyMinutes();

        // Redtrack
        $schedule->call(function () {
            RedTrack::crawl();
        })->everyThirtyMinutes();

        // Campaign
        $schedule->call(function () {
            foreach (User::all() as $key => $user) {
                PullCampaign::dispatch($user);
            }
        })->everyTenMinutes();

        // Ad group and ad
        $schedule->call(function () {
            foreach (User::all() as $key => $user) {
                PullAdGroup::dispatch($user);
                PullAd::dispatch($user);
            }
        })->everyTenMinutes();

        // Rules
        foreach (Rule::all() as $rule) {
            $schedule->command('rule:action', [
                $rule->id
            ])->cron($this->getFrequency($rule))->appendOutputTo(storage_path('logs/commands.log'));
        }
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
