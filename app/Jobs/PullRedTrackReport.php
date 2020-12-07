<?php

namespace App\Jobs;

use App\Models\Campaign;
use Dorantor\FileLock;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PullRedTrackReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $campaign;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Campaign $campaign)
    {
        $this->campaign = $campaign;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $lock = new FileLock(storage_path('logs/pull_redtrack_report.lock'));
        if ($lock->acquire()) {
            $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($this->campaign->provider->slug);

            (new $adVendorClass())->pullRedTrack($this->campaign);
            $lock->release();
        } else {
            echo ('Nope, 1 process is running!' . PHP_EOL);
        }
    }
}
