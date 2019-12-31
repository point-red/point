<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\CloudStorage;
use App\Model\Project\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StorageController extends Controller
{
    public function index(Request $request)
    {
        $cloudStorages = CloudStorage::eloquentFilter($request)->with('project');

        $cloudStorages->where(function ($query) {
            $query->where(function ($query2) {
                $query2->where('owner_id', auth()->user()->id)
                    ->where('is_user_protected', '=', true);
            })->orWhere(function ($query2) {
                $query2->where('is_user_protected', '=', false);
            });
        });

        if ($request->get('file_name')) {
            $value = $request->get('file_name');
            $cloudStorages->where(function ($query) use ($value) {
                $words = explode(' ', $value);
                foreach ($words as $word) {
                    $query->where('file_name', 'like', '%'.$word.'%');
                }
            });
        }

        $cloudStorages = pagination($cloudStorages, $request->get('limit'));

        $allowedMimeTypes = ['image/jpeg','image/gif','image/png','image/bmp','image/svg+xml'];
        foreach ($cloudStorages as $key => $cloudStorage) {
            if (!Storage::disk(env('STORAGE_DISK'))->exists($cloudStorage->path)) {
                continue;
            }
            $fullPath = Storage::disk($cloudStorage->disk)->path($cloudStorage->path);
            $base64 = base64_encode(Storage::disk($cloudStorage->disk)->get($cloudStorage->path));
            $preview = 'data:'.mime_content_type($fullPath) . ';base64,' . $base64;
            if (in_array(mime_content_type($fullPath), $allowedMimeTypes)) {
                $cloudStorage->preview = $preview;
            }
        }

        return new ApiCollection($cloudStorages);
    }

    public function update(Request $request, $id)
    {
        $cloudStorage = CloudStorage::findOrFail($id);

        DB::beginTransaction();

        if ($cloudStorage && $cloudStorage->key == $request->get('key')) {
            $cloudStorage->notes = $request->get('notes');
            $cloudStorage->save();
        }

        DB::commit();

        return response()->json([], 204);
    }

    public function destroy(Request $request, $id)
    {
        $cloudStorage = CloudStorage::findOrFail($id);

        DB::beginTransaction();

        if ($cloudStorage && $cloudStorage->key == $request->get('key')) {
            $cloudStorage->delete();

            $path = $cloudStorage->path . '' . $cloudStorage->key . '.' . $cloudStorage->file_ext;
            if (Storage::disk(env('STORAGE_DISK'))->exists($path)) {
                Storage::disk(env('STORAGE_DISK'))->delete($path);
            }
        }

        DB::commit();

        return response()->json([], 204);
    }

    public function upload(Request $request)
    {
        $file = $request->file('file');
        $feature = $request->get('feature');
        $featureSlug = str_replace(' ', '-', $request->get('feature'));
        $featureId = $request->get('feature_id') ?? null;
        $tenant = strtolower($request->header('Tenant'));
        $key = Str::random(16);
        $path = 'tenant/'.$tenant.'/upload/'.$featureSlug.'/';
        Storage::disk(env('STORAGE_DISK'))->putFileAs(
            $path, $file, $key.'.'.$file->getClientOriginalExtension()
        );

        $cloudStorage = new CloudStorage;
        $fileName = basename($request->file('file')->getClientOriginalName(), '.' . $request->file('file')->getClientOriginalExtension());
        $cloudStorage->file_name = $fileName;
        $cloudStorage->file_ext = $request->file('file')->getClientOriginalExtension();
        $cloudStorage->feature = $feature;
        if ($featureId > 0) {
            $cloudStorage->feature_id = $featureId;
        }
        $cloudStorage->key = $key;
        $cloudStorage->path = $path . $key.'.'.$file->getClientOriginalExtension();
        $cloudStorage->disk = env('STORAGE_DISK');
        $cloudStorage->project_id = Project::where('code', strtolower($tenant))->first()->id;
        $cloudStorage->owner_id = auth()->user()->id;
        $userProtected = filter_var($request->get('is_user_protected'), FILTER_VALIDATE_BOOLEAN);
        $cloudStorage->is_user_protected = $userProtected;
        $cloudStorage->notes = $request->get('notes');
        $cloudStorage->expired_at = Carbon::now()->addDay(1);
        $cloudStorage->download_url = env('API_URL').'/download?key='.$key;
        $cloudStorage->save();
    }
}
