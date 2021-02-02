<?php

namespace App\Http\Controllers;

use Alexusmai\LaravelFileManager\Requests\RequestValidator;
use Alexusmai\LaravelFileManager\Events\FilesUploaded;
use Alexusmai\LaravelFileManager\Events\FilesUploading;

use App\Utils\FileManager\FileManager;

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
}
