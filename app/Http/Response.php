<?php 

namespace App\Http;

class Response
{
	public function __construct() 
	{

	}

	public function next()
	{
		return true;
	}

	public function headers($code, $type = 'text/plain')
	{
		header_remove();

		header('Status: '.$code);

		header('Content-Type: '.$type);

		header($_SERVER['SERVER_PROTOCOL'] . ' ' . $code);
	}

	public function make($message, $type = "text/plain", $code = 200)
	{
		$this->headers($code, $type);

		return $message;
	}

	public function redirect($url)
	{
		header('Location: '.$url);
	}

	public function html($message, $code = 200)
	{
		return $this->make($message, "text/html", $code);
	}

	public function json($message, $code = 200)
	{
		$message = json_encode($message);

		return $this->make($message, "application/json", $code);
	}

	public function error($message, $code = 500)
	{
		header($_SERVER['SERVER_PROTOCOL'] . ' ' . $code . ' ' . $message);
	}
}