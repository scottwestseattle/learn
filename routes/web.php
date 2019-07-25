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

// protected
Route::get('/hash', 'HomeController@hash');
Route::post('/hasher', 'HomeController@hasher');
Route::get('/home', 'HomeController@index')->name('home');

// Site Admin Pages
Route::get('/admin', 'HomeController@admin')->middleware('is_admin')->name('admin');

// Courses
Route::group(['prefix' => 'courses'], function () {
	Route::get('/', 'CourseController@index');
	Route::get('/admin', 'CourseController@admin');
	Route::get('/view/{course}','CourseController@view');

	// add/create
	Route::get('/add','CourseController@add');
	Route::post('/create','CourseController@create');

	// edit/update
	Route::get('/edit/{course}','CourseController@edit');
	Route::post('/update/{course}','CourseController@update');

	// delete
	Route::get('/confirmdelete/{course}','CourseController@confirmdelete');
	Route::post('/delete/{course}','CourseController@delete');
	Route::get('/undelete', 'CourseController@undelete');

	// add/create
	Route::get('/publish/{course}','CourseController@publish');
	Route::post('/publishupdate/{course}','CourseController@publishupdate');
});

// Lessons
Route::group(['prefix' => 'lessons'], function () {
	Route::get('/admin/{course_id?}', 'LessonController@admin');
	Route::get('/view/{lesson}','LessonController@view');
	Route::get('/review/{lesson}/{reviewType?}','LessonController@review');

	// add/create
	Route::get('/add','LessonController@add');
	Route::get('/add/{course}','LessonController@add');
	Route::post('/create','LessonController@create');

	// edit/update
	Route::get('/edit/{lesson}','LessonController@edit');
	Route::post('/update/{lesson}','LessonController@update');
	Route::get('/edit2/{lesson}','LessonController@edit2');
	Route::post('/update2/{lesson}','LessonController@update2');

	// delete
	Route::get('/confirmdelete/{lesson}','LessonController@confirmdelete');
	Route::post('/delete/{lesson}','LessonController@delete');
	Route::get('/undelete', 'LessonController@undelete');

	// add/create
	Route::get('/publish/{lesson}','LessonController@publish');
	Route::post('/publishupdate/{lesson}','LessonController@publishupdate');
	
	Route::get('/{parent_id}', 'LessonController@index');
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

