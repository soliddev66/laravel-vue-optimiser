<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\LazyCollection;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PullCampaigns implements ShouldQueue
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
        $batch = Bus::batch([])
            ->allowFailures()
            ->then(function (Batch $batch) {
                //
            })
            ->finally(function (Batch $batch) {
                //
            })
            ->dispatch();

        User::cursor()
            ->map(function(User $user) {
                return new PullCampaign($user);
            })
            ->filter()
            ->chunk(100)
            ->each(function(LazyCollection $jobs) use ($batch) {
                $batch->add($jobs);
            });
    }
}
