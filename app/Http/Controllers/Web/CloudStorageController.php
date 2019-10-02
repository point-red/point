<?php

namespace App\Http\Controllers\Web;

use App\Model\CloudStorage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\FileNotFoundException;

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

        if (!$cloudStorage) {
            return view('web.file-not-found');
        }

        $fileName = $cloudStorage->file_name.'.'.$cloudStorage->file_ext;

        try {
            $file = Storage::disk($cloudStorage->disk)->download($cloudStorage->path, $fileName);

            if (!$file) {
                return view('web.file-not-found');
            }

            return $file;
        } catch (FileNotFoundException $exception) {
            return view('web.file-not-found');
        }
    }
}
