<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PullAdGroups implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach (User::all() as $key => $user) {
            foreach ($user->providers as $user_provider) {
                $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($user_provider->provider->slug);

                (new $adVendorClass())->pullAdGroup($user_provider);
            }
        }
    }
}
