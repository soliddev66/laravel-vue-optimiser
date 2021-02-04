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
     * Upload files
     *
     * @param RequestValidator $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(RequestValidator $request)
    {
        event(new FilesUploading($request));

        $uploadResponse = $this->fm->upload(
            $request->input('disk'),
            $request->input('path'),
            $request->file('files'),
            $request->input('overwrite'),
            $request->input('tags'),
        );

        event(new FilesUploaded($request));

        return response()->json($uploadResponse);
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
        return Tag::select('name')->where('user_id', auth()->id())->get();
    }
}
