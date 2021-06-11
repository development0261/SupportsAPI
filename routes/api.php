<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:api'])->group(function (){
	// Route::get('data', 'App\Http\Controllers\AuthController@getData');
	Route::post('createTicket', 'App\Http\Controllers\TicketController@createTicket');
	Route::get('getTickets', 'App\Http\Controllers\TicketController@getTickets');
	Route::get('getTicketDetails/{id}', 'App\Http\Controllers\TicketController@getTicketDetails');
	Route::post('sendMessage', 'App\Http\Controllers\TicketController@sendMessage');
	Route::post('filterTickets', 'App\Http\Controllers\TicketController@filterTickets');
	Route::post('updateTicketStatus', 'App\Http\Controllers\TicketController@updateTicketStatus');
});

Route::get('/InvalidAccessOfURL', function(){
	$response['status'] = 'error';
    $response['response'] = 'You cannot access this API without login';
    return response()->json($response, 401);
});

Route::post('register', 'App\Http\Controllers\AuthController@register');
Route::post('login', 'App\Http\Controllers\AuthController@login');