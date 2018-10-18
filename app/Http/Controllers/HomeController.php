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

		$user = $app->database->save([

			'name' => 'TestUpdate',
		
		])->where('email', 'test2@mail.com')->on('users');

		var_dump($user);

		return $app->twig->render('home.html', compact('users'));
	}
}