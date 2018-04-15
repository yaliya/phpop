<?php 


$app->get("/", "HomeController@index");

$app->get("/login", "AuthenticateController@index");

$app->post("/login", "AuthenticateController@login");

$app->get("/logout", "AuthenticateController@logout");

$app->get("/admin", "Admin\DashboardController@index", "Authenticated");