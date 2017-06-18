<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('laravel', function () {
    return view('welcome');
});

Route::get('/', 'TogglController@entry')->name('askApiToken');
Route::get('/test', 'TogglController@testActionZ')->name('testing');
Route::post('/', 'TogglController@entry');
Route::post('jira', 'TogglController@jiraPost')->name('jiraPost');

Route::get('lastWeek', 'TogglController@lastWeek')->name('lastWeekRoute');
