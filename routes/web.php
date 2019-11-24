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
Route::get('/authenticated', 'HomeController@authenticated');

// Site Admin Pages
Route::get('/admin', 'HomeController@admin')->middleware('is_admin')->name('admin');
Route::get('/home', 'HomeController@index')->name('home');
Route::get('/unauthorized', 'HomeController@unauthorized');

Route::get('/search', 'HomeController@search');
Route::post('/search', 'HomeController@search');

// protected
Route::get('/hash', 'HomeController@hash');
Route::post('/hasher', 'HomeController@hasher');

// Email
Route::get('/send/email', 'HomeController@wod');
Route::get('/send/wod', 'HomeController@wod');

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

	Route::get('/view/{filename}', 'TranslationController@view');

	// add
	//todo: Route::get('/add','TranslationController@add');
	//todo: Route::post('/create','TranslationController@create');

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

// Samples
Route::group(['prefix' => 'samples'], function () {
	Route::get('/', 'SampleController@index');
	Route::get('/admin', 'SampleController@admin');
	Route::get('/view/{course}','SampleController@view');

	// add/create
	Route::get('/add','SampleController@add');
	Route::post('/create','SampleController@create');

	// edit/update
	Route::get('/edit/{course}','SampleController@edit');
	Route::post('/update/{course}','SampleController@update');

	// delete
	Route::get('/confirmdelete/{course}','SampleController@confirmdelete');
	Route::post('/delete/{course}','SampleController@delete');
	Route::get('/undelete', 'SampleController@undelete');

	// add/create
	Route::get('/publish/{course}','SampleController@publish');
	Route::post('/publishupdate/{course}','SampleController@publishupdate');
});

