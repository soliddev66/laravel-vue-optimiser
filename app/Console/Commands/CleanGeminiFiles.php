<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanGeminiFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gemini:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean Gemini files.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $folder_path = public_path('/reports');
        $files = glob($folder_path . '/*');

        foreach ($files as $file) {
            if (is_file($file)) {
                $file_last_modified = filemtime($file);
                if ((time() - $file_last_modified) > 7 * 24 * 3600) {
                    unlink($file);
                }
            }
        }
    }
}
