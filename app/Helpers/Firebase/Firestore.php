<?php
// Firestore documentation
// https://firebase.google.com/docs/firestore/quickstart
// You need to install gRPC to use firestore
// https://cloud.google.com/php/grpc
namespace App\Helpers\Firebase;

use Google\Cloud\Core\Exception\GoogleException;
use Google\Cloud\Firestore\FirestoreClient;

class Firestore
{
    var $db;

    /**
     * Firebase constructor.
     *
     * @throws GoogleException
     */
    public function __construct()
    {
        // To use firebase, you need to create service account
        // https://cloud.google.com/docs/authentication/getting-started
        $this->db = new FirestoreClient([
            'keyFilePath' => storage_path('firebase-service-account.json')
        ]);
    }

    public function set($collection, $document, $data)
    {
        $docRef = $this->db->collection($collection);
        if ($document) {
            $docRef = $docRef->document($document);
            $docRef->set($data);
        } else {
            // document will be random unique
            $docRef->add($data);
        }
    }
}
