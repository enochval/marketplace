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

    $router->group(['prefix' => 'job-board'], function () use($router) {
        $router->post('', 'JobBoardController@createJob');

        $router->get('', 'JobBoardController@myJobs');

        $router->post('{id}/bid', 'JobBoardController@bid');

        $router->patch('{id}/update', 'JobBoardController@updateJob');
        $router->patch('{id}/hire-worker/{worker_id}', 'JobBoardController@hireWorker');
        $router->patch('{id}/review-worker/{worker_id}', 'JobBoardController@reviewWorker');
        $router->patch('{id}/review-employer/{employer_id}', 'JobBoardController@reviewEmployer');
        $router->patch('{id}/complete', 'JobBoardController@completeJob');

        $router->get('{id}/pitches', 'JobBoardController@getJobPitches');
        $router->get('{id}/reviews', 'JobBoardController@jobReviews');
    });

    $router->get('job-listing', 'JobBoardController@jobListing');

    $router->group(['prefix' => 'admin'], function () use ($router) {
        $router->get('jobs', 'JobBoardController@allJobs');
        $router->patch('jobs/{id}/approve', 'JobBoardController@approveJob');
        $router->patch('jobs/{id}/reverse-approval', 'JobBoardController@unApproveJob');
    });
});
