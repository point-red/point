<?php

namespace App\Http\Controllers\Api\Media;

use App\Http\Controllers\Controller;
use App\Http\Requests\Media\StoreMediaRequest;
use App\Http\Resources\ApiResource;
use App\Model\HumanResource\Employee\Employee;
use App\Services\MediaService;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function mediaEmployee(MediaService $service, Request $request, $id) {
        $data = $service->findBy($request, Employee::$morphName, $id);
        $result = $data->toArray();
        $result['download_url'] =  env('API_URL') . '/media/';
        return new ApiResource($result);
    }

    public function mediaEmployeeUpdate(MediaService $service, Request $request)  {
        $id = $request->get('id');
        $note = $request->get('note');
        return new ApiResource($service->update($id, $note));
    }

    public function mediaEmployeeStore(MediaService $service, StoreMediaRequest $request)
    {
        $file = $request->file('file');
        $id = $request->get('id');
        $note = $request->get('note');
        return new ApiResource($service->create(Employee::$morphName, $id, $file, $note));
    }

    public function download(MediaService $service, $id) {
        $file = $service->download($id);
        if (!$file) {
            return view('web.file-not-found');
        }
        return $file;
    }

    public function destroy(MediaService $service, $id) {
        return new ApiResource($service->delete($id));
    }
}
