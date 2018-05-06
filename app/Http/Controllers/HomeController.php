<?php 

namespace App\Http\Controllers;

class HomeController
{
	public function index($app, $request)
	{
		$users = $app->database

		->select('*')

		->from('users')

		->where('email', 'yaliyyaa@gmail.com')

		->where('password', 'test123')

		->orWhere('password', 'test123')

		->get();

		return $app->twig->render('home.html', compact('users'));
	}
}