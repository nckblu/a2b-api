<?php

use App\Http\Controllers\LocationController;
use App\Http\Controllers\UberController;

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
    return response()->json([
        'myMessageToYou' => 'What you doin\' here Willis?',
    ]);
});

$router->get('location/{searchText}', [
    'uses' => 'LocationController@index',
]);

$router->get('price-estimate', [
    'uses' => 'UberController@index',
]);
