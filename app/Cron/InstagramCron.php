<?php

/**
 * Created by PhpStorm.
 * User: leon.okubo
 * Date: 30/10/2018
 * Time: 11:50
 */
error_reporting(E_ALL);
include realpath(dirname(__DIR__) . '/../vendor/autoload.php');
$settings = require realpath(dirname(__DIR__) . '/settings.php');
require_once realpath(dirname(__DIR__) . '/middleware.php');

use App\Library\PubsubLibrary;
use App\Library\InstagramLibrary;
use App\Model\User;
use App\Model\Instagram;
use App\Library\GcVision;

while(true){
    $instagramModel = new Instagram();
    $gcVision = new GcVision();
    $subscription = (new PubsubLibrary('instagram-check'))->createSubscription('cron');

    foreach ($subscription->pull() as $message) {
        $profile = $message->data();
        $id = (new User())->getUser($profile)->id;
        $instagramModel->insert($id);
        $dadosInsta = $instagramModel->getUser($id);

        $instagramLibrary = new InstagramLibrary($profile);
        $getPaginateMedias = $instagramLibrary->getPaginateMedias();
        $path = "../tmp/$profile/";
        mkdir($path, 0777, true);

        $total = 0;
        foreach ($getPaginateMedias['data'] as $midia){
            $total++;
            $ext = strtolower(pathinfo($midia['getImageThumbnailUrl'], PATHINFO_EXTENSION));
            $image = "$path{$midia['id']}.$ext";
            file_put_contents($image, file_get_contents($midia['getImageThumbnailUrl']));
            $getAnalyze = $gcVision->detect_face_gcs($image);
            $getAnalyze['img'] = $midia['getImageThumbnailUrl'];
            $getAnalyze['path'] = $image;

            $getObject = $gcVision->objectLocalization($image);
            print_r($getObject);
            #print_r($getAnalyze);
            printf('Total analisadas: %s' . PHP_EOL, $total);
        }
        printf('Message: %s' . PHP_EOL, $profile);
        $subscription->acknowledge($message);
    }
}