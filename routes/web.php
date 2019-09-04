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
    return $router->app->version();
});

$router->group(['prefix' => 'api/v1'], function () use($router) {
    $router->post('worker-registration', 'AuthController@registerWorker');
    $router->post('employer-registration', 'AuthController@registerEmployer');
    $router->post('confirm-email', 'AuthController@confirmEmail');
    $router->post('authenticate', 'AuthController@authenticate');

    // protected routes group
    $router->group(['middleware' => 'auth:api'], function () use($router) {
            $router->post('employer-update-profile', 'UserController@updateEmployer');
            $router->get('employer-edit-profile', 'UserController@editEmployer');
        });

    // $router->post('employer-update-profile', 'UserController@updateEmployer');

});
