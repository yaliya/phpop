<?php 

namespace App\Http\Controllers;

class HomeController
{
	public function index($app, $request)
	{
		$users = $app->database

		->raw("SELECT * FROM posts")

		->get(function($query, $post) {

			return [

				'title' => $post->title,

				'body' => $post->body,

				'publisher' => $query->raw("SELECT * FROM users WHERE id = :id", [':id' => $post->users_id])->get(function($query, $user) {

					return [

						'id' => $user->id,

						'name' => $user->name
					];
				})
			];
		});

		return $app->twig->render('home.html', compact('users'));
	}
}