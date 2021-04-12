<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\UserProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PullRedTrackReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $date;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($date)
    {
        $this->date = $date;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $date = $this->date;
        UserProvider::chunk(10, function($user_providers) use ($date) {
            foreach ($user_providers as $user_provider) {
                $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($user_provider->provider->slug);

                (new $adVendorClass())->pullRedTrack($user_provider, $date);
            }
        });

    }
}
