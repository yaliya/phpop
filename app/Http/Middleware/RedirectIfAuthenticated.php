<?php

namespace App\Http\Middleware;

class RedirectIfAuthenticated
{
	public function request($app, $request, $response)
	{
		//Code
		if($app->session()->has("auth")) {

			return $response->redirect("/");
		}

		return $response->next();
	}
};