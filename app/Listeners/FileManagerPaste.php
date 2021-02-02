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
        $path = $event->path();

        foreach ($clipboard['files'] as $file) {
            $image = Image::where(DB::raw("CONCAT(path, '/', name)"), $file)->first();

            if ($image) {
                if (Storage::disk($disk)->exists($path . '/' . $image->name)) {
                    continue;
                }

                $db_image = Image::firstOrNew([
                    'user_id' => auth()->id(),
                    'disk' => $disk,
                    'path' => $path,
                    'name' => $image->name
                ]);

                $db_image->width = $image->width;
                $db_image->height = $image->height;

                $db_image->save();

                if ($clipboard['type'] == 'cut') {
                    $image->delete();
                }
            }
        }


    }
}