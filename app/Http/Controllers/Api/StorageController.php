<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\CloudStorage;
use Illuminate\Http\Request;

class StorageController extends Controller
{
    public function index(Request $request)
    {
        $cloudStorages = CloudStorage::where('owner_id', auth()->user()->id)->with('project');

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

        return new ApiCollection($cloudStorages);
    }
}
