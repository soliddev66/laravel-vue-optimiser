<?php

namespace App\Listeners;

use Alexusmai\LaravelFileManager\Events\Paste;
use App\Models\Image;
use Storage;
use DB;

class FileManagerPaste {
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  customerOrder  $event
     * @return void
     */
    public function handle(Paste $event)
    {
        $clipboard = $event->clipboard();
        $disk = $event->disk();

        if ($clipboard['type'] == 'cut') {
            foreach ($clipboard['files'] as $item) {
                Image::where([
                    [DB::raw("CONCAT(path, '/', name)"), '=', $item],
                    ['user_id', '=', auth()->id()],
                    ['disk', '=', $disk]
                ])->delete();
            }

            foreach ($clipboard['directories'] as $item) {
                Image::where([
                    ['user_id', '=', auth()->id()],
                    ['disk', '=', $disk],
                    ['path', 'like', $item . '%']
                ])->delete();
            }
        }
    }
}
