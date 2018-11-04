<?php

namespace App\Http\Controllers\Web;

use App\Model\CloudStorage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class CloudStorageController extends Controller
{
    /**
     * Download the resource.
     *
     * @param Request $request
     * @return mixed
     */
    public function download(Request $request)
    {
        $cloudStorage = CloudStorage::where('key', $request->get('key'))->first();
        $file = $cloudStorage->file_name . '.' . $cloudStorage->file_ext;

        return Storage::disk('s3')->download($cloudStorage->path, $file);
    }
}
