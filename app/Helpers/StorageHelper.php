<?php

namespace App\Helpers;

use App\Model\CloudStorage;
use App\Model\Project\Project;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StorageHelper
{
    public static function uploadFromBase64($base64, $feature, $featureId = null)
    {
        // Convert base64 to image file
        $tenant = app('request')->header('Tenant');
        $featureSlug = str_replace(' ', '-', $feature);
        $key = Str::random(16);
        $fileName = $key.'.jpg';
        $tmpPath = 'tenant/'.$tenant.'/tmp/';
        $path = 'tenant/'.$tenant.'/'.$featureSlug.'/';
        if (! Storage::exists($tmpPath)) {
            Storage::makeDirectory($tmpPath);
        }
        base64_to_jpeg($base64, storage_path('app/'.$tmpPath.$fileName));
        $file = \File::get(storage_path('app/'.$tmpPath.$fileName));
        Storage::disk(env('STORAGE_DISK'))->put($path.$fileName, $file);
        Storage::disk('local')->delete($tmpPath.$fileName);

        // Update database
        $cloudStorage = new CloudStorage;
        $cloudStorage->file_name = 'Sales Visitation '.date('d F Y H:i');
        $cloudStorage->file_ext = 'jpg';
        $cloudStorage->mime_type = 'image/jpg';
        $cloudStorage->feature = $feature;
        if ($featureId > 0) {
            $cloudStorage->feature_id = $featureId;
        }
        $cloudStorage->key = $key;
        $cloudStorage->path = $path.$fileName;
        $cloudStorage->disk = env('STORAGE_DISK');
        $cloudStorage->project_id = Project::where('code', strtolower($tenant))->first()->id;
        $cloudStorage->owner_id = auth()->user()->id;
        $cloudStorage->is_user_protected = false;
        $cloudStorage->download_url = env('API_URL').'/download?key='.$key;
        $cloudStorage->save();
    }
}
