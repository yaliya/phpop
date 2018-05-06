<?php 

namespace App\Http\Controllers;

class AuthenticateController
{
	public function index($app, $request, $response)
	{
		if($app->session()->has("auth")) {

			return $response->redirect("/admin");
		}

		return $app->twig->render("auth/login.html");
	}

	public function login($app, $request, $response)
	{
		$user = $app->database
	
				->select('*')
	
				->from('users')
	
				->where('email', $request->input('username'))
	
				->where('password', $request->input('password'))
	
				->first();

		if($user) {

			$app->session()->set("auth", $user);

			return $response->redirect("/admin");
		}

		return $app->twig->render("auth/login.html", [

			'error' => 'Wrong email or password'
		]);
	}

	public function logout($app, $request, $response)
	{
		$app->auth = null;

		$app->session()->remove("auth");

		return $response->redirect("/");
	}
}