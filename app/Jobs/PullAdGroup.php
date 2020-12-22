<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\User;
use Dorantor\FileLock;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PullAdGroup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $lock = new FileLock(storage_path('logs/pull_ad_group.lock'));
        if ($lock->acquire()) {
            foreach ($this->user->providers as $user_provider) {
                $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($user_provider->provider->slug);

                (new $adVendorClass)->pullAdGroup($user_provider);
            }
            $lock->release();
        } else {
            echo('Nope, 1 process is running!' . PHP_EOL);
        }
    }
}
