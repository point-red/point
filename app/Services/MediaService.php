<?php

namespace App\Services;

use App\Model\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class MediaService {

    public function create($table, $id, UploadedFile $file, $note) {
        DB::connection('tenant')->beginTransaction();
        $media = new Media();
        $media->owner_table=$table;
        $media->owner_id=$id;
        $media->note=$note;
        $media->name=sprintf('%s-%s.%s', $file->getFilename(), time(), $file->getClientOriginalExtension());
        $media->name_ori=$file->getClientOriginalName();
        $media->path = $file->storeAs('upload', $media->name);
        $media->mime=$file->getMimeType();
        $media->save();
        DB::connection('tenant')->commit();
        return $media;
    }

    public function findBy($request, $table, $id) {
        $medias = Media::eloquentFilter($request)
                        ->where('owner_table', $table)
                        ->where('owner_id', $id)
                        ->select('*');
        return pagination($medias, $request->get('limit'));
    }

    public function delete($id) {
        $media = Media::findOrFail($id);
        File::delete($this->getPath($media->path));
        $media->delete();
        return $media;
    }

    public function download($id) {
        $media = Media::findOrFail($id);
        $path = $this->getPath($media->path);
        return array (
                'file' => $path,
                'mime' => $media->mime,
                'name' => $media->name_ori
            );
    }

    private function getPath($path) {
        return storage_path('app/'.$path);
    }

}