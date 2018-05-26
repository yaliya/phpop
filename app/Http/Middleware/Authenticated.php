<?php 

namespace App\Http\Middleware;

use App\Http\Session;

class Authenticated
{
	public function request($app, $request, $response)
	{

		if($app->session()->has("auth")) {

			$app->singleton("auth", function() use($app) {

				return json_decode(json_encode($app->session()->get("auth"), true));
			});

			return $response->next();
		}

		return $response->redirect("/login");
	}
}