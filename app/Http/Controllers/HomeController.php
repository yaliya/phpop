<?php 

namespace App\Http\Controllers;

class HomeController
{
	public function index($app, $request)
	{
		$users = $app->database

		->select('*')

		->from('users')

		->get();

		return $app->twig->render('home.html', compact('users'));
	}
}