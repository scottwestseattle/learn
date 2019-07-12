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
Route::get('/', 'FrontPageController@index');
Route::get('/about', 'FrontPageController@about')->name('about');
Route::get('/contact', 'FrontPageController@contact')->name('contact');
Route::get('/privacy', 'FrontPageController@privacy')->name('privacy');
Route::get('/terms', 'FrontPageController@terms')->name('terms');
Route::get('/signup', 'FrontPageController@signup')->name('signup');
Route::get('/language/{locale}', 'FrontPageController@language');
Route::get('/phpinfo', 'FrontPageController@phpinfo');
Route::get('/eunoticeaccept/', 'FrontPageController@eunoticeaccept');
Route::get('/eunoticereset/', 'FrontPageController@eunoticereset');
Route::get('/sample/', 'FrontPageController@sample');

Route::get('/home', 'HomeController@index')->name('home');

// Site Admin Pages
Route::get('/admin', 'HomeController@admin')->middleware('is_admin')->name('admin');

// Super Admin Pages
Route::get('/superadmin', 'HomeController@superadmin')->middleware('is_admin:super')->name('superadmin');

// Lessons
Route::group(['prefix' => 'lessons'], function () {
	Route::get('/', 'LessonController@index');
	Route::get('/admin', 'LessonController@admin');
	Route::get('/view/{lesson}','LessonController@view');

	// add/create
	Route::get('/add','LessonController@add');
	Route::post('/create','LessonController@create');

	// edit/update
	Route::get('/edit/{lesson}','LessonController@edit');
	Route::post('/update/{lesson}','LessonController@update');

	// delete
	Route::get('/confirmdelete/{lesson}','LessonController@confirmdelete');
	Route::post('/delete/{lesson}','LessonController@delete');
	Route::get('/undelete', 'LessonController@undelete');
	
	// add/create
	Route::get('/publish/{lesson}','LessonController@publish');
	Route::post('/publishupdate/{lesson}','LessonController@publishupdate');
});

// Users
Route::group(['prefix' => 'users'], function () {
	Route::get('/', 'UsersController@index');
	Route::get('/index', 'UsersController@index');
	Route::get('/view/{user}','UsersController@view');

	// add/create
	Route::get('/add','UsersController@add');
	Route::post('/create','UsersController@create');

	// edit/update
	Route::get('/edit/{user}','UsersController@edit');
	Route::post('/update/{user}','UsersController@update');

	// delete / confirm delete
	Route::get('/confirmdelete/{user}','UsersController@confirmdelete');
	Route::post('/delete/{user}','UsersController@delete');
});


// Translations
Route::group(['prefix' => 'translations'], function () {
	// index
	Route::get('/', 'TranslationController@index');

	// add
	Route::get('/add','TranslationController@add');
	Route::post('/create','TranslationController@create');

	// edit
	Route::get('/edit/{filename}','TranslationController@edit');
	Route::post('/update/{filename}','TranslationController@update');
});

// Visitors
Route::group(['prefix' => 'visitors'], function () {

	// index
	Route::get('/', 'VisitorController@index');
	Route::post('/', 'VisitorController@index');
});

// Visitors
Route::group(['prefix' => 'events'], function () {

	// index
	Route::get('/', 'EventController@index');
	Route::post('/', 'EventController@index');
});
