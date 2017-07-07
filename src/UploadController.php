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

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Class UploadController.
 */
class UploadController extends Controller
{
    public function serve(Request $request)
    {
        $upload = config('summernote.upload');
        $storage = app('summernote.storage');

        return $storage->upload($request);
    }
}
