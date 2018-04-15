<?php 

namespace App\Providers;

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
		}
	}
}