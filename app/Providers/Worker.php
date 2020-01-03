<?php 

namespace App\Providers;

use App\Queue\Consumer;

class Worker
{
	public function process()
	{
		$args = $_SERVER['argv'];

		if(isset($args[1]))
		{
			if($args[1] == "make:controller")
			{
				if(isset($args[2]))
				{

					$dir = explode("\\", $args[2]);

					$file = end($dir);

					array_pop($dir);

					$controllers = __DIR__ . "/../Http/Controllers/";

					$namespace = "App\Http\Controllers";

					$namespace .= "\\" . implode("\\", $dir);

					$namespace = rtrim($namespace, "\\");

					$directories = implode("/", $dir);

					if(!is_dir($controllers . "/" . $directories)) {

						mkdir($controllers . "/" . $directories);
					}

					if(file_exists($controllers . "/" . $directories . "/" . ucfirst($file) . ".php")) {

						echo "Controller already exists !\r\n";

						exit(1);
					}

					$contents = "<?php\n\n";

					$contents .= "namespace " . $namespace . "\n\n";

					$contents .= "class " . $file . "\n";
					
					$contents .= "{\n";
					
					$contents .= "\tpublic function index() \n\t{\n";
					
					$contents .= "\t\t//Code\n";
					
					$contents .= "\t}\n";
					
					$contents .= "};";
					
					file_put_contents($controllers . "/" . $directories . "/" .ucfirst($file) . ".php", $contents);
				}
			}

			if($args[1] == "make:job") 
			{
				if(isset($args[2]))
				{

					$dir = explode("\\", $args[2]);

					$file = end($dir);

					array_pop($dir);

					$jobs = __DIR__ . "/../Queue/Jobs/";

					$namespace = "App\Queue\Jobs";

					$namespace .= "\\" . implode("\\", $dir);

					$namespace = rtrim($namespace, "\\");

					$directories = implode("/", $dir);

					if(!is_dir($jobs . "/" . $directories)) {

						mkdir($jobs . "/" . $directories);
					}

					if(file_exists($jobs . "/" . $directories . "/" . ucfirst($file) . ".php")) {

						echo "Job already exists !\r\n";

						exit(1);
					}

					$contents = "<?php\n\n";

					$contents .= "namespace " . $namespace . ";\n\n";

					$contents .= "class " . $file . "\n";
					
					$contents .= "{\n";
					
					$contents .= "\tpublic function handle() \n\t{\n";
					
					$contents .= "\t\t//Code\n";
					
					$contents .= "\t}\n";
					
					$contents .= "};";
					
					file_put_contents($jobs . "/" . $directories . "/" .ucfirst($file) . ".php", $contents);
				}
			}

			if($args[1] == 'queue:listen')
			{
				$queue = 'default';

				if(isset($args[2])) {

					$queue = $args[2];
				}

				$consumer = new Consumer($queue);

				$consumer->listen();
			}

			if($args[1] == 'serve')
			{
				echo "Serving application on http://localhost:8000\n";

				exec('cd ' . __DIR__ . '/../../public && php -S localhost:8000');
			}
		}
	}
}