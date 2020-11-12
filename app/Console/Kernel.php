<?php

namespace App\Console;
use Carbon\Carbon;
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
        \DB::table('rule_actions')->insert([[
            'name' => 'Block Widgets / Pushlisher',
            'provider' => '',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Un-Block Widgets / Pushlisher',
            'provider' => '',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Block Apps',
            'provider' => '',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Un-Block Apps',
            'provider' => '',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Pause Campaign',
            'provider' => '',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Activate Campaign',
            'provider' => '',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Pause Contents',
            'provider' => '',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Activate Contents',
            'provider' => '',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Change Campaign Bid',
            'provider' => '',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Change Campaign Bid (CAD Only)',
            'provider' => '',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Change Campaign Budget',
            'provider' => '',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Pause Target',
            'provider' => '',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Activate Target',
            'provider' => '',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Pause Site',
            'provider' => '',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Activate Site',
            'provider' => '',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Pause Exchange',
            'provider' => '',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Activate Exchange',
            'provider' => '',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Pause Sections',
            'provider' => '',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Activate Sections',
            'provider' => '',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Pause AdGroup',
            'provider' => '',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Activate AdGroup',
            'provider' => '',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Pause Domain',
            'provider' => '',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Activate Domain',
            'provider' => '',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Pause Spot',
            'provider' => '',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Activate Spot',
            'provider' => '',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Change Target Bid',
            'provider' => '',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Change Content Bid',
            'provider' => '',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Change Section Bid',
            'provider' => '',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Change Widget Coefficient',
            'provider' => '',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Change Site Bid',
            'provider' => '',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Change Widget Bid',
            'provider' => '',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Change Widget Bid (CAD only)',
            'provider' => '',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Change Site Bid',
            'provider' => '',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Change Exchange Bid',
            'provider' => '',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Change Site Bid Modifier',
            'provider' => '',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Change AdGroup Bid',
            'provider' => '',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Day Parting',
            'provider' => '',
            'created_at' => Carbon::now()
        ]]);
        exit;
        // $schedule->command('inspire')->hourly();
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
