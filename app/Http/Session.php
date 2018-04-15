<?php 

namespace App\Http;

class Session
{
	public function __construct() {

		$this->start();
	}

	public function start()
	{
		if(session_status() == PHP_SESSION_NONE)
		{
			session_start();
		}
	}

	public function get($name)
	{
		self::start();

		return $_SESSION[$name];
	}

	public function set($name, $value)
	{
		self::start();

		$_SESSION[$name] = $value;
	}

	public function remove($name)
	{
		self::start();

		unset($_SESSION[$name]);
	}

	public function has($name)
	{
		self::start();

		return isset($_SESSION[$name]);
	}

	public function all()
	{
		self::start();

		return $_SESSION;
	}

	public function clear()
	{
		self::start();

		session_destroy();
	}
}