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

/*$router->get('/', function () use ($router) {
    return view('taskboard', ['name' => 'Riad']);
});*/

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->get('create', ['uses' => 'TaskManagerController@getCreate']);
    $router->get('update/{id}', ['uses' => 'TaskManagerController@getUpdate']);
    $router->post('task', ['uses' => 'TaskManagerController@getCreate']);
    $router->put('task/{id}', ['uses' => 'TaskManagerController@getUpdate']);
});
