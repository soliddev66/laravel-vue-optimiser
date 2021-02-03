<?php

namespace App\Http\Controllers;

use Alexusmai\LaravelFileManager\Requests\RequestValidator;
use Alexusmai\LaravelFileManager\Events\FilesUploaded;
use Alexusmai\LaravelFileManager\Events\FilesUploading;
use Alexusmai\LaravelFileManager\Events\Rename;

use App\Utils\FileManager\FileManager;
use App\Models\Tag;

class FileManagerController extends \Alexusmai\LaravelFileManager\Controllers\FileManagerController
{
    /**
     * FileManagerController constructor.
     *
     * @param FileManager $fm
     */
    public function __construct(FileManager $fm)
    {
        parent::__construct($fm);
        $this->fm = $fm;
    }

    /**
     * Rename
     *
     * @param RequestValidator $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function rename(RequestValidator $request)
    {
        event(new Rename($request));

        return response()->json(
            $this->fm->rename(
                $request->input('disk'),
                $request->input('newName'),
                $request->input('oldName'),
                $request->input('path'),
                $request->input('name'),
                $request->input('oldBaseName'),
            )
        );
    }

    /**
     * Rename
     *
     * @param RequestValidator $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function tag(RequestValidator $request)
    {
        return response()->json(
            $this->fm->tag(
                $request->input('tag'),
            )
        );
    }

    /**
     * Rename
     *
     * @param RequestValidator $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function tags()
    {
        return Tag::select('name')->get();
    }
}
