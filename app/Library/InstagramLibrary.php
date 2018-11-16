<?php

/**
 * Created by PhpStorm.
 * User: leon.okubo
 * Date: 19/10/2018
 * Time: 19:44
 */

namespace App\Library;

use InstagramScraper\Instagram;

class InstagramLibrary
{
    public $profile, $instagram;

    public function __construct($profile)
    {
        $this->profile = $profile;
        $this->instagram = new Instagram();
    }

    public function getAccount()
    {
        return $this->instagram->getAccount($this->profile)->getId();
    }

    public function getMedias()
    {
        $this->instagram->getMedias('fscompanyoficial', 20, '');
    }

    public function getPaginateMedias($maxId = null)
    {
        $nonPrivateAccountMedias = $this->instagram->getPaginateMedias($this->profile, $maxId);

        $arr = [];
        $arr['maxId'] = $nonPrivateAccountMedias['maxId'];
        $arr['hasNextPage'] = $nonPrivateAccountMedias['hasNextPage'];

        foreach($nonPrivateAccountMedias['medias'] as $inst)
        {
            $arr['data'][] = [
                'image'          => $inst->getImageHighResolutionUrl(),
                'id'             => $inst->getId(),
                'getCreatedTime' => date('Y-m-d', $inst->getCreatedTime()),
                'getCaption'     => $inst->getCaption(),
                'getImageThumbnailUrl' => $inst->getImageThumbnailUrl(),
            ];
        }

        return $arr;
    }
}