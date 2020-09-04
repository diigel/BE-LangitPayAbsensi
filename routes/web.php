<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return "Langitpay Absensi";
});

$router->post("/login", "AbsensiController@login");
$router->post("/requestAbsen", "AbsensiController@requestAbsen");
$router->post("/getUser", "AbsensiController@getUser");
