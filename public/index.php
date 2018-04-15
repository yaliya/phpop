<?php 

$start_time = microtime(TRUE);

require "../vendor/autoload.php";

$app = new App\App([

	//Debug info
	'debug' => true,
	
	//Application name
	'name' 	=> 'Aggregate',
	
	//Default views directory
	'views'	=> '../resources/views',
	
	//Default middleware directory
	'middleware' => 'App\Http\Middleware',
	
	//Default controllers directory
	'controllers' => 'App\Http\Controllers',
]);

$app->singleton("database", function() {

	return App\Providers\Database::init("localhost", "homestead", "homestead", "secret");
});

//Create twig templating engine instance
$app->singleton("twig", function() use($app) {

    $loader = new \Twig_Loader_Filesystem($app->config()["views"]);

    return new \Twig_Environment($loader, $app->config());
});

//Require web routes
require_once "../routes/web.php";

//Require api routes
require_once "../routes/api.php";

$end_time = microtime(TRUE);

echo "<center><small>Execution time " . date("H:i:s:m", $end_time - $start_time) . "</small></center>";