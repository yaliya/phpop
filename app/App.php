<?php 

namespace App;

use App\Http\Session;

use App\Http\Request;

use App\Http\Response;

class App 
{
	protected $config;

	protected $request;
	
	protected $session;	
	
	protected $response;
	
	protected $requests;
	
	protected $middleware;

	public function __construct($config = ["debug" => false])
	{
		$this->config = $config;

		$this->session = new Session;

		$this->request = new Request;

		$this->response = new Response;
	}

	public function config() 
	{
		return $this->config;
	}

	public function session()
	{
		return $this->session;
	}

	public function request()
	{
		return $this->request;
	}

	public function response()
	{
		return $this->response;
	}

	public function singleton($obj, $callback)
	{
		$instance = call_user_func($callback);

		if($instance) 
		{
			$this->$obj = $instance;
		}

		return $instance;
	}

	private function processRequest($method, $pattern, $callback = null, $middleware = null)
	{
		$args = array();

		$margs = array();

		$requesturi = explode("?", $_SERVER["REQUEST_URI"])[0];

		$pattern = "@^" . preg_replace('/\\\:[a-zA-Z0-9%=.\_\-]+/', '([a-zA-Z0-9%=.\-\_]+)', preg_quote($pattern)) . "$@D";

		if($_SERVER["REQUEST_METHOD"] == $method && preg_match($pattern, $requesturi, $args)) {

			$args[] = $this;

			$args[] = $this->request;

			$args[] = $this->response;

			array_shift($args);

			if($middleware) {

				$margs[] = $this;

				$margs[] = $this->request;

				$margs[] = $this->response;

				if(is_callable($middleware)) {

					if(!call_user_func_array($middleware, $margs)) {

						// exit(1);
						return;
					}
				}

				else {

					$class = $middleware;

					if(isset($this->config["middleware"])) {

						$class = $this->config["middleware"]."\\".$class;
					}

					if(class_exists($class)) {
						
						if(!call_user_func_array(array(new $class, "request"), $margs)) {

							// exit(1);
							return;
						}
					}
				}
			}

			if(is_callable($callback)) {

				echo call_user_func_array($callback, $args);

				// exit(1);
			}

			else {

				if(is_string($callback)) 
				{
					$callback = explode("@", $callback);
					
					if(sizeof($callback) == 2) {

						$class = $callback[0];

						if(isset($this->config["controllers"])) {

							$class = $this->config["controllers"]."\\".$class;
						}

						$method = $callback[1];

						if(class_exists($class)) {

							echo call_user_func_array(array(new $class, $method), $args);

							// exit(1);
						}
					}
				}
			}
		}

		return $this;
	}

	public function get($pattern, $callback = null, $middleware = null)
	{
		return $this->processRequest("GET", $pattern, $callback, $middleware);
	}

	public function post($pattern, $callback = null, $middleware = null)
	{
		return $this->processRequest("POST", $pattern, $callback, $middleware);
	}

	public function render($filename, $contents = array())
	{

		$contents["config"] = $this->config;

		$contents["request"] = $this->request;

		extract($contents);

		$filename = str_replace(".", "/", $filename);

		include($this->config["templates"]."/".$filename.".php");
	}
};
