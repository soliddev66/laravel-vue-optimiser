<?php

namespace App\Http\Middleware;

use Closure;
use Storage;

class FileManager
{
    public function handle($request, Closure $next)
    {
        $path = $request->input('path') ?? '';

        $path = ltrim($path, md5(auth()->id()));

        $request->merge(['path' => md5(auth()->id()) . $path]);

        if (!Storage::disk($request->input('disk'))->exists(md5(auth()->id()))) {
            Storage::disk($request->input('disk'))->makeDirectory(md5(auth()->id()));
        }

        return $next($request);
    }
}
