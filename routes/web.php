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
use App\Models\Task;

// $router->get('/', ['uses' => 'TaskManagerController@getTasks']);

$router->get('/', function () {
    return view('taskboard');
});

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->post('task', ['uses' => 'TaskManagerController@getCreate']);
    $router->put('task/{id}', ['uses' => 'TaskManagerController@getUpdate']);
});
