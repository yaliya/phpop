<?php 

namespace App\Http\Controllers\Admin;

use App\Http\Session;

class DashboardController
{
	public function index($app, $request, $response)
	{
		
		return $app->twig->render("admin/index.html", compact('app'));
	}
}