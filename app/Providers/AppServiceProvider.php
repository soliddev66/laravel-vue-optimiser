<?php

namespace App\Providers;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Dispatcher $events)
    {
        Schema::defaultStringLength(191);
        $events->listen(BuildingMenu::class, function (BuildingMenu $event) {
            $event->menu->remove('campaigns');
            $event->menu->addAfter('home', [
                'text' => 'Campaigns',
                'url' => 'campaigns?start=' . session('start') . '&end=' . session('end') . '&shortcut=' . session('shortcut') . '&tracker=' . session('tracker') . '&provider=' . session('provider') . '&account=' . session('account') . '&advertiser=' . session('advertiser') . '&page=' . session('page') . '&search=' . session('search') . '&length=' . session('length') . '&column=' . session('column') . '&dir=' . session('dir'),
                'key' => 'campaigns',
                'active' => ['campaigns*'],
                'icon' => 'fas fa-chart-line'
            ]);
        });
        Queue::after(function (JobProcessed $event) {
            $payload = $event->job->payload();
            $data = $payload['data'];
            if ($payload['displayName'] === 'App\Jobs\PullGeminiReport') {
                $command = unserialize($data['command']);
                $gemini_job = $command->getGeminiJob();
                if ($gemini_job->status === 'completed') {
                    // $gemini_job->delete();
                }
            }
            if ($payload['displayName'] === 'App\Jobs\DeleteDuplicates') {
                //
            }
        });
    }
}
