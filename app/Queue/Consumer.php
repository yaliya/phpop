<?php 

namespace App\Queue;

use Pheanstalk\Pheanstalk;

class Consumer
{
	private $client;

	private $queue;

	public function __construct($queue)
	{
		$this->queue = $queue;

		$this->client = new Pheanstalk('127.0.0.1');
	}

	public function listen()
	{
		$this->client->watch($this->queue);

		while($job = $this->client->reserve()) {

			$class = unserialize(base64_decode($job->getData()));

            echo "Processing " . get_class($class) . "\r\n"; 

            $status = $class->handle();

            $this->client->delete($job);

            echo "Processed " . get_class($class) . "\r\n";
		}
	}

	public function isListening()
	{
		return $this->client->getConnection()->isServiceListening();
	}
}