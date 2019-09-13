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
    return response()->json([
        "message" => "Welcome to Timbala marketplace APIs",
        "base_url" => url('/')."api/v1/"
    ]);
});

$router->group(['prefix' => 'api/v1'], function () use($router) {
    $router->post('worker-registration', 'AuthController@registerWorker');
    $router->post('employer-registration', 'AuthController@registerEmployer');
    $router->post('confirm-email', 'AuthController@confirmEmail');
    $router->post('authenticate', 'AuthController@authenticate');

    $router->post('profile', 'UserController@profile');
    $router->post('work-history', 'UserController@workHistory');
    $router->post('worker-skill', 'UserController@workerSkills');

    $router->post('verify-bvn', 'UserController@bvnVerification');
    $router->get('bvn-analysis', 'UserController@getBvnAnalysis');
    $router->get('callback', 'UserController@paymentCallback');
});
