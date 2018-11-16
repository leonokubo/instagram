<?php

/**
 * Created by PhpStorm.
 * User: leon.okubo
 * Date: 19/10/2018
 * Time: 18:48
 */

namespace App\Controller;

use App\Library\InstagramLibrary;
use App\Library\PubsubLibrary;
use App\Model\User;
use App\Library\Request;
use App\Library\GcFunction;

class Instagram
{

    public $gcFunction;

    public function __construct($setting)
    {
        //fscompanyoficial
        $this->gcFunction = $setting['gcFunction']['URL'];
    }

    public function api($request, $response, $args)
    {
        global $app;
        $user = new User();
        $getUser = $user->getUser($args['profile'])->user;
        $instagramLibrary = new InstagramLibrary($args['profile']);
        $maxId = isset($args['maxId']) ? $args['maxId'] : null;

        if(empty($getUser)){
            $instagramLibrary->getAccount();
            $user->insert($args['profile']);
        }

        $pubsub = new PubsubLibrary('instagram-check');
        if(!$pubsub->createTopic()){
            return $response->withStatus(500)
                ->withHeader('Content-type', 'application/json')
                ->withJson(["success" => false, "message" => "createTopic"]);
        }
        else{
            $gcfunction = new GcFunction();
            $publish = $gcfunction->sentPubSubInstagram($args['profile']);

            if(!$publish['success'])
                return $response->withStatus(500)->withHeader('Content-type', 'application/json')->withJson($publish['message']);

            $container = $app->getContainer();
            $container['renderer']->render($response, 'instagram.phtml', ['instagram' => $instagramLibrary->getPaginateMedias($maxId)], $request);
        }
    }
}