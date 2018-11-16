<?php
/**
 * Created by PhpStorm.
 * User: leon.okubo
 * Date: 01/11/2018
 * Time: 19:54
 */

namespace App\Library;

use App\Library\Request;
use Predis;

class GcFunction
{
    public $gcFunction = "https://us-central1-automl-vision-219821.cloudfunctions.net/";
    public $redis;

    public function __construct()
    {
        $this->redis = new Predis\Client(REDIS_CONF);
    }

    public function sentPubSubInstagram($profile)
    {
        $find = $this->redis->get("sentPubSubInstagram:$profile");

        if($find)
            return ['success' => true, 'message' => 'cache'];

        $publish = Request::rest($this->gcFunction . "instagram-check", json_encode(['data'=>$profile]), ["Content-Type:application/json"]);
        if($publish->info['http_code'] != 200)
            return ['success' => false, 'message' => $publish->error];

        $this->redis->set("sentPubSubInstagram:$profile", true);
        $this->redis->expire("sentPubSubInstagram:$profile", 10);
        return ['success' => true];
    }
}