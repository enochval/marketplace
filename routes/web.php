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
        "base_url" => url('/') . "api/v1/"
    ]);
});

$router->group(['prefix' => 'api/v1'], function () use ($router) {
    $router->post('worker-registration', 'AuthController@registerWorker');
    $router->post('employer-registration', 'AuthController@registerEmployer');
    $router->post('agent-registration', 'AuthController@registerAgent');
    $router->post('confirm-email', 'AuthController@confirmEmail');
    $router->post('authenticate', 'AuthController@authenticate');

    $router->patch('update-password', 'UserController@updatePassword');
    $router->patch('profile', 'UserController@profile');
    $router->post('work-history', 'UserController@workHistory');

    $router->post('verify-bvn', 'UserController@bvnVerification');
    $router->get('bvn-analysis', 'UserController@getBvnAnalysis');

    $router->post('subscribe', 'UserController@subscribe');
    $router->get('callback', 'UserController@paymentCallback');


    $router->group(['prefix' => 'job-board'], function () use ($router) {
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
    $router->get('bid-status/{job_id}', 'JobBoardController@bidStatus');
    $router->get('my-area-jobs', 'JobBoardController@myAreaJobs');
    $router->get('top-jobs', 'JobBoardController@topJobs');
    $router->get('dashboard-stat', 'JobBoardController@dashboardStat');

    $router->group(['prefix' => 'utils'], function () use ($router) {
        $router->get('cities', 'HomeController@getCities');
        $router->get('categories', 'HomeController@getCategories');
    });

    $router->group(['prefix' => 'admin'], function () use ($router) {
        $router->get('all-users', 'UserController@allUsers');
        $router->patch('subscribe/{user_id}', 'UserController@manuallySubscribeUser');
        $router->get('jobs', 'JobBoardController@allJobs');
        $router->patch('jobs/{id}/approve', 'JobBoardController@approveJob');
        $router->patch('jobs/{id}/reverse-approval', 'JobBoardController@unApproveJob');
    });

    $router->post('worker-registration-by-agent', 'UserController@registerWorkerByAgent');
    $router->post('agent-workers', 'UserController@getAgentWorkers');
});
