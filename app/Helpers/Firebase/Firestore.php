<?php

namespace App\Helpers\Firebase;

use Google\Cloud\Core\Exception\GoogleException;
use Google\Cloud\Firestore\FirestoreClient;

class Firebase
{
    var $firestore;

    /**
     * Firebase constructor.
     *
     * @throws GoogleException
     */
    function __construct()
    {
        $this->firestore = new FirestoreClient([
            'keyFilePath' => storage_path('red-point-firebase.json')
        ]);

//    $docRef = $db->collection('users')->document('lovelace');
//    $docRef->set([
//        'first' => 'Ada',
//        'last' => 'Lovelace',
//        'born' => 1815
//    ]);
//    printf('Added data to the lovelace document in the users collection.' . PHP_EOL);
    }
}
