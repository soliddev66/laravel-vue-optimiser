<?php

namespace App\Providers;

use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

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
    public function boot()
    {
        Queue::after(function (JobProcessed $event) {
            $payload = $event->job->payload();
            $data = $payload['data'];
            if ($payload['displayName'] === 'App\Jobs\PullGeminiReport') {
                $command = unserialize($data['command']);
                $gemini_job = $command->getGeminiJob();
                if ($gemini_job->status === 'completed') {
                    $gemini_job->delete();
                }
            }
        });
    }
}
