<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return 'Go to /reserve for reserving a queue.';
});

Route::get('/reserve/{customer_id}', function($customer_id){
	\Event::fire(new App\Events\TestEvent($customer_id));
	return 'Reserved. Go to /show to see your position.';
});

Route::get('/release/{customer_id}', function($customer_id){
	\Event::fire(new App\Events\ReleaseEvent($customer_id));
	return 'Released. Go to /show to see your position.';
});

Route::get('/show', function(){
	return view('welcome');
});