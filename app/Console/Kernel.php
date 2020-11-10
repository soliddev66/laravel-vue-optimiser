<?php

namespace App\Console;

use App\Jobs\PullCampaign;
use App\Models\User;
use App\Models\Rule;
use App\Vngodev\Gemini;
use App\Vngodev\RedTrack;
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
        $schedule->command('inspire')->hourly();
        $schedule->call(function () {
            Gemini::crawl();
        })->everyMinute();
        $schedule->call(function () {
            Gemini::checkJobs();
        })->everyMinute();
        $schedule->call(function () {
            RedTrack::crawl();
        })->everyMinute();
        $schedule->call(function () {
            foreach (User::all() as $key => $user) {
                PullCampaign::dispatch($user);
            }
        })->everyMinute();

        foreach (Rule::all() as $rule) {
            var_dump($rule->from);
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
}
