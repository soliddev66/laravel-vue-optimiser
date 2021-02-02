<?php

namespace App\Utils\FileManager;

use Alexusmai\LaravelFileManager\Traits\CheckTrait;
use Alexusmai\LaravelFileManager\Traits\ContentTrait;
use Alexusmai\LaravelFileManager\Traits\PathTrait;
use Alexusmai\LaravelFileManager\Services\ConfigService\ConfigRepository;

use App\Models\Image;
use Storage;

class FileManager extends \Alexusmai\LaravelFileManager\FileManager
{
    /**
     * FileManager constructor.
     *
     * @param  ConfigRepository  $configRepository
     */
    public function __construct(ConfigRepository $configRepository)
    {
        $this->configRepository = $configRepository;
    }

    /**
     * Upload files
     *
     * @param $disk
     * @param $path
     * @param $files
     * @param $overwrite
     *
     * @return array
     */
    public function upload($disk, $path, $files, $overwrite)
    {
        $result = [
            'result' => [
                'status'  => 'success',
                'message' => 'uploaded',
                'data' => []
            ],
        ];

        $fileNotUploaded = false;

        foreach ($files as $file) {
            if (!$overwrite
                && Storage::disk($disk)
                    ->exists($path . '/' . $file->getClientOriginalName())
            ) {
                $result['result']['data'][] = [
                    'file' => $file->getClientOriginalName(),
                    'existing' => true
                ];

                $fileNotUploaded = true;
                continue;
            }

            if ($this->configRepository->getMaxUploadFileSize()
                && $file->getSize() / 1024 > $this->configRepository->getMaxUploadFileSize()
            ) {
                $result['result']['data'][] = [
                    'file' => $file->getClientOriginalName(),
                    'sizeExceeded' => true
                ];

                $fileNotUploaded = true;
                continue;
            }

            // check file type if need
            if ($this->configRepository->getAllowFileTypes()
                && !in_array(
                    $file->getClientOriginalExtension(),
                    $this->configRepository->getAllowFileTypes()
                )
            ) {
                $result['result']['data'][] = [
                    'file' => $file->getClientOriginalName(),
                    'fileTypeNotAllowed' => true
                ];

                $fileNotUploaded = true;
                continue;
            }

            $dimensions = getimagesize($file->getPathName());

            Storage::disk($disk)->putFileAs(
                $path,
                $file,
                $file->getClientOriginalName()
            );

            $image = Image::firstOrNew([
                'user_id' => auth()->id(),
                'disk' => $disk,
                'path' => $path ?? '',
                'name' => $file->getClientOriginalName()
            ]);

            $image->width = $dimensions[0];
            $image->height = $dimensions[1];

            $image->save();
        }

        // If the some file was not uploaded
        if ($fileNotUploaded) {
            $result['result']['status'] = 'warning';
            $result['result']['message'] = 'notAllUploaded';
        }

        return $result;
    }

    /**
     * Get files and directories for the selected path and disk
     *
     * @param $disk
     * @param $path
     *
     * @return array
     */
    public function content($disk, $path)
    {
        // get content for the selected directory
        $content = $this->getContent($disk, $path);

        foreach ($content['files'] as &$file) {
            $image = Image::firstOrNew([
                'user_id' => auth()->id(),
                'disk' => $disk,
                'path' => $path,
                'name' => $file['basename']
            ]);

            if (!$image->width) {
                $dimensions = getimagesize(Storage::disk($disk)->path($file['path']));

                $image->width = $dimensions[0];
                $image->height = $dimensions[1];

                $image->save();
            }

            $file['width'] = $image->width;
            $file['height'] = $image->height;
        }

        return [
            'result' => [
                'status'  => 'success',
                'message' => null,
            ],
            'directories' => $content['directories'],
            'files' => $content['files'],
        ];
    }

    /**
     * Rename file or folder
     *
     * @param $disk
     * @param $newName
     * @param $oldName
     *
     * @return array
     */
    public function rename($disk, $newName, $oldName, $path = null, $name = null, $oldBaseName = null)
    {
        Storage::disk($disk)->move($oldName, $newName);

        $image = Image::where([
            'user_id' => auth()->id(),
            'disk' => $disk,
            'path' => $path,
            'name' => $oldBaseName
        ])->first();

        if ($image) {
            $image->name = $name;
            $image->save();
        }

        return [
            'result' => [
                'status'  => 'success',
                'message' => 'renamed',
            ],
        ];
    }
}
