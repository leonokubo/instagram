<?php
/**
 * Created by PhpStorm.
 * User: leon.okubo
 * Date: 29/10/2018
 * Time: 19:08
 */

namespace App\Library;

use Google\Cloud\Core\Exception\GoogleException;
use Google\Cloud\PubSub\PubSubClient;

class PubsubLibrary
{
    private $pubsub, $topicName;

    public function __construct($topicName, $projectId = 'automl-vision-219821')
    {
        $this->topicName = $topicName;
        $this->pubsub = new PubSubClient([
            'projectId' => $projectId,
        ]);
    }

    public function createTopic()
    {
        $topic = $this->pubsub->topic($this->topicName);
        return $topic->exists() === true ? true : $topic->create();
    }

    public function createSubscription($subscriptionName)
    {
        try {
            $subscription = $this->pubsub->subscription($subscriptionName, $this->topicName);
            $subscription->exists() === true ? true : $subscription->create();
            return $subscription;
        }catch (GoogleException $exception)
        {
            print $exception->getMessage();
            return $exception->getMessage();
        }
    }

    public function publish_message($message)
    {
        try{
            $topic = $this->pubsub->topic($this->topicName);
            return ($topic->publish(['data' => $message])['messageIds'][0]) ?? null;
        }catch (GoogleException $exception)
        {
            print $exception->getMessage();
            return $exception->getMessage();
        }
    }
}