<?php

/*
 * This file is part of the overtrue/laravel-summernote.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Overtrue\LaravelSummernote;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Overtrue\LaravelSummernote\Events\Uploaded;
use Overtrue\LaravelSummernote\Events\Uploading;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class StorageManager.
 */
class StorageManager
{
    use UrlResolverTrait;

    /**
     * @var \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected $disk;

    /**
     * Constructor.
     *
     * @param \Illuminate\Contracts\Filesystem\Filesystem $disk
     */
    public function __construct(Filesystem $disk)
    {
        $this->disk = $disk;
    }

    /**
     * Upload a file.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        $config = config('summernote.upload');

        if (!$request->hasFile($config['form_name'])) {
            return $this->error('UPLOAD_ERR_NO_FILE');
        }

        $file = $request->file($config['form_name']);

        if ($error = $this->fileHasError($file, $config)) {
            return $this->error($error);
        }

        $filename = $this->getFilename($file, $config);

        if ($this->eventSupport()) {
            $modifiedFilename = event(new Uploading($file, $filename, $config), [], true);
            $filename = !is_null($modifiedFilename) ? $modifiedFilename : $filename;
        }

        $this->store($file, $filename);

        $response = [
            'state' => 'SUCCESS',
            'url' => $this->getUrl($filename),
            'title' => $this->getUrl($filename),
            'relative_url' => substr($this->getUrl($filename), strlen(config('app.url'))),
            'original' => $file->getClientOriginalName(),
            'type' => $file->getExtension(),
            'size' => $file->getSize(),
        ];

        if ($this->eventSupport()) {
            event(new Uploaded($file, $response));
        }

        return response()->json($response);
    }

    /**
     * @return bool
     */
    public function eventSupport()
    {
        return class_exists('Illuminate\Foundation\Events\Dispatchable');
    }

    /**
     * List all files of dir.
     *
     * @param string $path
     * @param int    $start
     * @param int    $size
     * @param array  $allowFiles
     *
     * @return Response
     */
    public function listFiles($path, $start, $size = 20, array $allowFiles = [])
    {
        $allFiles = $this->disk->listContents($path, true);
        $files = $this->paginateFiles($allFiles, $start, $size);

        return [
            'state' => empty($files) ? 'EMPTY' : 'SUCCESS',
            'list' => $files,
            'start' => $start,
            'total' => count($allFiles),
        ];
    }

    /**
     * Split results.
     *
     * @param array $files
     * @param int   $start
     * @param int   $size
     *
     * @return array
     */
    protected function paginateFiles(array $files, $start = 0, $size = 50)
    {
        return collect($files)->where('type', 'file')->splice($start)->take($size)->map(function ($file) {
            return [
                'url' => $this->getUrl($file['path']),
                'mtime' => $file['timestamp'],
            ];
        })->all();
    }

    /**
     * Store file.
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param string                                              $filename
     *
     * @return mixed
     */
    protected function store(UploadedFile $file, $filename)
    {
        return $this->disk->put($filename, fopen($file->getRealPath(), 'r+'));
    }

    /**
     * Validate the input file.
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param array                                               $config
     *
     * @return bool|string
     */
    protected function fileHasError(UploadedFile $file, array $config)
    {
        $error = false;

        if (!$file->isValid()) {
            $error = $file->getError();
        } elseif ($file->getSize() > $config['max_size']) {
            $error = 'upload.ERROR_SIZE_EXCEED';
        } elseif (!empty($config['allow_files']) &&
            !in_array('.'.$file->guessExtension(), $config['allow_files'])) {
            $error = 'upload.ERROR_TYPE_NOT_ALLOWED';
        }

        return $error;
    }

    /**
     * Get the new filename of file.
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param array                                               $config
     *
     * @return string
     */
    protected function getFilename(UploadedFile $file, array $config)
    {
        $ext = '.'.$file->getClientOriginalExtension();

        $filename = config('summernote.hash_filename') ? md5($file->getFilename()).$ext : $file->getClientOriginalName();

        return $this->formatPath($config['path_format'], $filename);
    }

    /**
     * Make error response.
     *
     * @param $message
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function error($message)
    {
        return response()->json(['state' => trans("summernote::upload.{$message}")]);
    }

    /**
     * Format the storage path.
     *
     * @param string $path
     * @param string $filename
     *
     * @return mixed
     */
    protected function formatPath($path, $filename)
    {
        $replacement = array_merge(explode('-', date('Y-y-m-d-H-i-s')), [$filename, time()]);
        $placeholders = ['{yyyy}', '{yy}', '{mm}', '{dd}', '{hh}', '{ii}', '{ss}', '{filename}', '{time}'];
        $path = str_replace($placeholders, $replacement, $path);

        //替换随机字符串
        if (preg_match('/\{rand\:([\d]*)\}/i', $path, $matches)) {
            $length = min($matches[1], strlen(PHP_INT_MAX));
            $path = preg_replace('/\{rand\:[\d]*\}/i', str_pad(mt_rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT), $path);
        }

        if (!str_contains($path, $filename)) {
            $path = str_finish($path, '/').$filename;
        }

        return $path;
    }
}
