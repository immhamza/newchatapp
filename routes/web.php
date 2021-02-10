<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
//
// Route::get('/profile', function () {
//    dd('df');
//    return view('welcome');
// });



Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::post('/message/store/{user}', 'MessageController@store');
Route::post('/user/markMessagesSeen/{user}','UserController@markMessagesSeen'); 
Route::get('/user/unseenMessagesCount/{user}','UserController@unseenMessagesCount'); 
Route::get('/user/messages_between/{user}', 'UserController@messages_between'); 
Route::resource('user','UserController');
