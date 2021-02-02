<?php

namespace App\Listeners;

use Alexusmai\LaravelFileManager\Events\Deleted;
use App\Models\Image;

class FileManagerDeleted {
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
    public function handle(Deleted $event)
    {
        foreach ($event->items() as $item) {
            if ($item['type'] === 'file') {
                Image::where([
                    'user_id' => auth()->id(),
                    'disk' => $event->disk(),
                    'path' => $item['dir'],
                    'name' => $item['name']
                ])->delete();
            } else {
                Image::where([
                    ['user_id', '=', auth()->id()],
                    ['disk', '=', $event->disk()],
                    ['path', 'like', $item['path'] . '%']
                ])->delete();
            }
        }
    }
}