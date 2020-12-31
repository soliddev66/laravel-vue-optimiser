<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteCompletedImportFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $file_name;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($file_name)
    {
        $this->file_name = $file_name;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (file_exists(public_path('reports/' . $this->file_name))) {
            unlink(public_path('reports/' . $this->file_name));
        }
    }
}
