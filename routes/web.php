<?php 


$app->get("/", "HomeController@index");

$app->get("/login", "AuthenticateController@index", "RedirectIfAuthenticated");

$app->post("/login", "AuthenticateController@login", "RedirectIfAuthenticated");

$app->get("/logout", "AuthenticateController@logout", "Authenticated");

$app->get("/admin", "Admin\DashboardController@index", "Authenticated");