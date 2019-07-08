<?php

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

/* original laravel page */
Route::get('/welcome', function () {    return view('welcome');  });

Auth::routes();

/* public pages */
Route::get('/', 'FrontPageController@index')->name('frontpage');
Route::get('/about', 'FrontPageController@about')->name('about');
Route::get('/contact', 'FrontPageController@contact')->name('contact');
Route::get('/privacy', 'FrontPageController@privacy')->name('privacy');
Route::get('/terms', 'FrontPageController@terms')->name('terms');
Route::get('/signup', 'FrontPageController@signup')->name('signup');

/* protected pages */
Route::get('/admin', 'HomeController@admin')->name('admin');


