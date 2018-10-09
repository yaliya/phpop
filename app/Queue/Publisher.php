<?php 

namespace App\Queue;

use Pheanstalk\Pheanstalk;

class Publisher
{
	private static $client;

    public static function dispatch($obj, $queue = 'default')
    {
    	self::$client = new Pheanstalk('127.0.0.1');

    	self::$client->useTube($queue)->put(base64_encode(serialize($obj)));
    }
}