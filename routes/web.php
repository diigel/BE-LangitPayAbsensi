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

$router->get('/', function () {
    return "Langitpay Absensi";
});

$router->post("/request-login", "AbsensiController@login");
$router->post("/request-absen", "AbsensiController@requestAbsen");
$router->post("/request-update-token", "AbsensiController@updateToken");

$router->post("/get-notification", "AbsensiController@getNotification");
$router->post("/get-user", "AbsensiController@getUser");
