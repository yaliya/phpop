<?php 

namespace App\Http;

class Request
{
	protected $input;

	protected $query;
	
	protected $files;

	public function __construct()
	{
		if(isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) 
		{
			$_POST = array_merge($_POST, (array) json_decode(trim(file_get_contents('php://input')), true));
		}

		$this->query = new \stdClass;

		foreach($_GET as $param_name => $param_value)
		{
			$this->query->$param_name = $param_value;
		}

		$this->input = new \stdClass;

		foreach($_POST as $param_name => $param_value)
		{
			$this->input->$param_name = $param_value;
		}

		$this->files = new \stdClass;

		foreach($_FILES as $param_name => $param_value)
		{
			$params = (object)($param_value);

			$this->$files->$param_name = $params;
		}
	}

	public function has($param)
	{
		if($_SERVER["REQUEST_METHOD"] == "GET") 
		{
			return isset($this->query->$param);
		}

		else if($_SERVER["REQUEST_METHOD"] == "POST")
		{
			return isset($this->input->$param);
		}
	}

	public function input($param)
	{
		return $this->input->$param;
	}

	public function query($param)
	{
		return $this->query->$param;
	}

	public function files($param)
	{
		return $this->files->$param;
	}
}
