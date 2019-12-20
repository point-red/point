<?php

namespace App\Services;

use App\Model\Media;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class MediaService {

    const FOLDER_UPLOAD='upload/';

    public function create($table, $id, UploadedFile $file, $note) {
        DB::connection('tenant')->beginTransaction();
        $media = new Media();
        $media->owner_table=$table;
        $media->owner_id=$id;
        $media->note=$note;
        $media->name=sprintf('%s-%s.%s', $file->getFilename(), time(), $file->getClientOriginalExtension());
        $media->name_ori=$file->getClientOriginalName();
        $path = self::FOLDER_UPLOAD . $media->name;
        Storage::disk($this->getStorage())->put($path, $file->get());
        $media->mime=$file->getMimeType();
        $media->save();
        DB::connection('tenant')->commit();
        return $media;
    }

    public function changeOwner($id, $owner) {
        DB::connection('tenant')->beginTransaction();
        $media = Media::findOrFail($id);
        $media->owner_id = $owner;
        $media->save();
        DB::connection('tenant')->commit();
    }

    public function findBy($request, $table, $id) {
        $medias = Media::eloquentFilter($request)
                        ->where('owner_table', $table)
                        ->where('owner_id', $id)
                        ->select('*');
        return pagination($medias, $request->get('limit'));
    }

    public function delete($id) {
        DB::connection('tenant')->beginTransaction();
        $media = Media::findOrFail($id);
        Storage::disk($this->getStorage())->delete($media->path);
        $media->delete();
        DB::connection('tenant')->commit();
        return $media;
    }

    public function download($id) {
        DB::connection('tenant')->beginTransaction();
        $media = Media::findOrFail($id);
        DB::connection('tenant')->commit();
        try {
            $file = Storage::disk($this->getStorage())->download(self::FOLDER_UPLOAD . $media->name, $media->name_ori);
            if (!$file) {
                return null;
            }
            return $file;
        } catch (FileNotFoundException $exception) {
            return null;
        }
    }

    private function getStorage() {
        return env('STORAGE_DISK');
    }

    public function update($id, $note) {
        DB::connection('tenant')->beginTransaction();
        $media = Media::findOrFail($id);
        $media->note = $note;
        $media->save();
        DB::connection('tenant')->commit();
        return $media;
    }

}