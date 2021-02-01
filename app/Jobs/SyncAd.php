<?php

namespace App\Jobs;

use App\Models\Ad;
use App\Utils\AdVendors\Taboola;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncAd implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $ad;

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
        $tabola = new Taboola;

        Ad::where(['provider_id' => 4, 'synced' => 0])->chunk(10, function ($ads) use ($tabola) {
            foreach ($ads as $ad) {
                $tabola->syncAd($ad);
            }
        });
    }
}
