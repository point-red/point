<?php

namespace App\Http\Controllers\Web;

use App\Model\CloudStorage;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\FileNotFoundException;

class CloudStorageController extends WebController
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

        if (! $cloudStorage) {
            return view('web.file-not-found');
        }

        $fileName = $cloudStorage->file_name.'.'.$cloudStorage->file_ext;

        try {
            $file = Storage::disk($cloudStorage->disk)->download($cloudStorage->path, $fileName);

            if (! $file) {
                return view('web.file-not-found');
            }

            return $file;
        } catch (FileNotFoundException $exception) {
            return view('web.file-not-found');
        }
    }

    public function downloadMedia(MediaService $service, $id) {
        $file = $service->download($id);
        if (!$file) {
            return view('web.file-not-found');
        }
        return $file;
    }
}
