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
    private static function db()
    {
        // To use firebase, you need to create service account
        // https://cloud.google.com/docs/authentication/getting-started
        try {
            return new FirestoreClient([
                'keyFilePath' => storage_path('firebase-service-account.json'),
            ]);
        } catch (GoogleException $e) {
        }
    }

    public static function set($collection, $document, $data)
    {
        $docRef = self::db()->collection($collection);
        if ($document) {
            $docRef = $docRef->document($document);
            $docRef->set($data);
        } else {
            // document will be random unique
            $docRef->add($data);
        }
    }
}
