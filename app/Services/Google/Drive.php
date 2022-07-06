<?php

namespace App\Services\Google;

class Drive
{
    private \Google_Client $client;
    private \Google\Service\Drive $service;
    private \Masbug\Flysystem\GoogleDriveAdapter $adapter;
    private \League\Flysystem\Filesystem $driver;
    private \Illuminate\Filesystem\FilesystemAdapter $disk;
    
    public function __construct()
    {    
        $this->client = Google::client();

        // https://github.com/masbug/flysystem-google-drive-ext#using-with-laravel-framework
        $this->service = new \Google\Service\Drive($this->client);
        $this->adapter = new \Masbug\Flysystem\GoogleDriveAdapter($this->service);
        $this->driver = new \League\Flysystem\Filesystem($this->adapter);
        $this->disk = new \Illuminate\Filesystem\FilesystemAdapter($this->driver, $this->adapter);
    }
    
    /**
     * Upload file to user's Google Drive account, set permission, get file id and path.
     * 
     * @param  \Illuminate\Http\File|\Illuminate\Http\UploadedFile|string $file
     * @param  string  $dir
     * @return string
     */
    public function store($file, $dir = '')
    {
        if (empty($dir)) {
            $dir = config('services.google.drive.root');
        }
        
        // Upload file to google drive, get the filename and extension
        $filename = $this->disk->putFile($dir, $file);

        // get file metadata to get the file id
        // file id is used to set permission and get preview link
        $metadata = $this->adapter->getMetadata($filename);

        $fileId = $metadata['id'];

        // set file permission to "Anyone on the internet with this link can view"
        // https://stackoverflow.com/a/57450358/3671954
        $newPermission = new \Google\Service\Drive\Permission([
            'type' => 'anyone',
            'role' => 'reader',
            'additionalRoles' => [],
            'withLink' => true,
            'value' => '' 
        ]);
        $this->service->permissions->create($fileId, $newPermission);
        
        return $fileId;
    }

    /**
     * Delete existing file from user's Google Drive account.
     * 
     * @param  string  $fileId
     * @return void
     */
    public function destroy(string $fileId)
    {
        $this->service->files->delete($fileId);
    }

    /**
     * Get preview link from google drive file id
     * 
     * @param  string  $fileId
     * @return string
     */
    public static function previewUrl(string $fileId)
    {
        return "https://drive.google.com/file/d/$fileId/preview?usp=drivesdk";
    }
}
