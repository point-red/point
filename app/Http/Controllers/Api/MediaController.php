<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Media\StoreMediaRequest;
use App\Http\Resources\ApiResource;
use App\Model\HumanResource\Employee\Employee;
use App\Services\MediaService;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function mediaEmployee(MediaService $service, Request $request, $id) {
        return new ApiResource($service->findBy($request, Employee::$morphName, $id));
    }

    public function mediaEmployeeStore(MediaService $service, StoreMediaRequest $request)
    {
        $file = $request->file('file');
        $id = $request->get('id');
        $note = $request->get('note');
        return new ApiResource($service->create(Employee::$morphName, $id, $file, $note));
    }

    public function download(MediaService $service, $id) {
        $media = $service->download($id);
        $headers = [
            'Content-Type' => $media['mime'],
         ];

        return response()->download($media['file'], $media['name'], $headers);
    }

    public function destroy(MediaService $service, $id) {
        return new ApiResource($service->delete($id));
    }
}
