<?php 

//Require autoload
require "../vendor/autoload.php";

//Initialize new App
$app = new App\App([
	//Debug info
	'debug' => true,
	//Application name
	'name' 	=> 'PHPOP',
	//Application directorh
	'dir' 	=> __DIR__ . '/../',
	//Default views directory
	'views'	=> '../resources/views',
	//Default middleware directory
	'middleware' => 'App\Http\Middleware',
	//Default controllers directory
	'controllers' => 'App\Http\Controllers',
]);

//Configure environment
$app->singleton('env', function() use($app) {
	//Intialize Dotenv and load .env from app dir
	return new \Dotenv\Dotenv($app->config()['dir']); })->load();

//Initialize database connection
$app->singleton("database", function() {
	//Return database instance to singleton
	return new App\Providers\Database(
		//Database host
		getenv('DB_HOST'), 
		//Database name
		getenv('DB_NAME'), 
		//Database user
		getenv('DB_USER'),
		//Database pass		
		getenv('DB_PASS'));
});

//Create twig templating engine instance
$app->singleton("twig", function() use($app) {
	//Intialize twig filesystem loader
    $loader = new \Twig_Loader_Filesystem($app->config()["views"]);
    //Return twig environment instance
    return new \Twig_Environment($loader, $app->config());
});

//Require web routes
require_once "../routes/web.php";

//Require api routes
require_once "../routes/api.php";