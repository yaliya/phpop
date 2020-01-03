<?php 

namespace App\Http\Controllers;

class HomeController
{
	public function index($app, $request)
	{
		return $app->twig->render('home.html');
	}
}