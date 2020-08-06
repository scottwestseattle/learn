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

Route::get('/index2', 'FrontPageController@index2')->name('index2'); /* original version of the frontpage */
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
Route::get('/articles', 'EntryController@articles');

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

// Reader RSS
Route::get('/lessons/rss-reader/{lesson}/', 'LessonController@rssReader');
Route::get('/courses/rss-reader', 'CourseController@rssReader');

// Entries
Route::group(['prefix' => 'entries'], function () {
	
	// misc
	Route::get('/', 'EntryController@index');
	Route::get('/index/{type_flag?}', 'EntryController@indexadmin')->middleware('auth');
	Route::get('/show/{id}', 'EntryController@show');
	Route::get('/read/{entry}', 'EntryController@read');

	// publish
	Route::get('/publish/{entry}', 'EntryController@publish')->middleware('auth');
	Route::post('/publishupdate/{entry}', 'EntryController@publishupdate')->middleware('auth');
		
	// add/create
	Route::get('/add','EntryController@add')->middleware('auth');
	Route::post('/create','EntryController@create')->middleware('auth');

	// edit/update
	Route::get('/edit/{entry}','EntryController@edit')->middleware('auth');
	Route::post('/update/{entry}','EntryController@update')->middleware('auth');

	// delete / confirm delete
	Route::get('/confirmdelete/{entry}','EntryController@confirmdelete')->middleware('auth');
	Route::post('/delete/{entry}','EntryController@delete')->middleware('auth');	
	
	// permalink catch alls
	Route::get('/view/{title}/{id}', ['as' => 'entry.view', 'uses' => 'EntryController@view']);
	Route::get('/{permalink}', ['as' => 'entry.permalink', 'uses' => 'EntryController@permalink']);
	Route::resource('entry', 'EntryController');		
});


// History
Route::group(['prefix' => 'history'], function () {
	Route::get('/', 'HistoryController@index');
	Route::get('/rss', 'HistoryController@rss');

	// add/create
	Route::get('/add/{programName}/{programId}/{sessionName}/{sessionId}/{seconds}','HistoryController@add');
	Route::get('/add-public/{programName}/{programId}/{sessionName}/{sessionId}/{seconds}','HistoryController@addPublic');
	Route::post('/create','HistoryController@create');

	// edit/update
	Route::get('/edit/{history}','HistoryController@edit');
	Route::post('/update/{history}','HistoryController@update');
	
	// delete
	Route::get('/confirmdelete/{history}','HistoryController@confirmdelete');
	Route::post('/delete/{history}','HistoryController@delete');
	Route::get('/delete/{history}','HistoryController@delete');	
});

// Vocabulary Lists
Route::group(['prefix' => 'vocab-lists'], function () {

	Route::get('/', 'VocabListController@index');
	Route::get('/index', 'VocabListController@index');
	Route::get('/view/{vocabList}','VocabListController@view');
	Route::get('/review/{vocabList}', 'VocabListController@review');

	// add/create
	Route::get('/add','VocabListController@add');
	Route::post('/create','VocabListController@create');

	// edit/update
	Route::get('/edit/{vocabList}','VocabListController@edit');
	Route::post('/update/{vocabList}','VocabListController@update');

	// delete
	Route::get('/confirmdelete/{vocabList}','VocabListController@confirmdelete');
	Route::post('/delete/{vocabList}','VocabListController@delete');

	// publish
	Route::get('/publish/{vocabList}','VocabListController@publish');
	Route::post('/publishupdate/{vocabList}','VocabListController@publishupdate');

	Route::get('/undelete', 'VocabListController@undelete');
	Route::get('/fastdelete/{vocabList}','VocabListController@fastdelete');
});

// Words
Route::group(['prefix' => 'words'], function () {
	Route::get('/index', 'WordController@indexUser');
	Route::get('/indexowner/{parent_id?}', 'WordController@indexowner');
	Route::get('/admin', 'WordController@admin');
	Route::get('/view/{word}','WordController@view');
	Route::get('/review', 'WordController@review');
	Route::get('/touch/{word}', 'WordController@touch');

	// add/create
	Route::get('/add/{parent_id?}','WordController@add');
	Route::post('/create','WordController@create');
	Route::get('/add-user', 'WordController@addUser')->middleware('auth');
	Route::post('/create-user','WordController@createUser')->middleware('auth');
	Route::get('/add-vocab-word/{vocabList}','WordController@addVocabListWord');
	Route::post('/create-vocab-word','WordController@createVocabListWord');

	// edit/update
	Route::get('/edit/{word}','WordController@edit')->middleware('is_owner');
	Route::post('/update/{word}','WordController@update')->middleware('is_owner');
	Route::get('/edit-user/{word}','WordController@editUser')->middleware('is_owner');
	Route::post('/update-user/{word}','WordController@updateUser')->middleware('is_owner');

	Route::post('/updateajax/{word}','WordController@updateajax');
	Route::get('/updateajax/{word}','WordController@updateajax');

	// delete
	Route::get('/confirmdelete/{word}','WordController@confirmdelete');
	Route::post('/delete/{word}','WordController@delete');
	Route::get('/confirmdelete-user/{word}','WordController@confirmDeleteUser')->middleware('is_owner');
	Route::post('/delete-user/{word}','WordController@deleteUser')->middleware('is_owner');

	Route::get('/undelete', 'WordController@undelete');
	Route::get('/fastdelete/{word}','WordController@fastdelete');

	Route::get('/', 'WordController@indexUser');
});

// Courses
Route::group(['prefix' => 'courses'], function () {
	Route::get('/', 'CourseController@index');
	Route::get('/admin', 'CourseController@admin');
	Route::get('/view/{course}','CourseController@view');
	Route::get('/rss', 'CourseController@rss');

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

	// publish
	Route::get('/publish/{course}','CourseController@publish');
	Route::post('/publishupdate/{course}','CourseController@publishupdate');
});

// Lessons
Route::group(['prefix' => 'lessons'], function () {
	Route::get('/admin/{course_id?}', 'LessonController@admin');
	Route::get('/view/{lesson}','LessonController@view');
	Route::post('/view/{lesson}','LessonController@view'); // just in case they hit enter on the ajax form
	Route::get('/review/{lesson}/{reviewType?}','LessonController@review');
	Route::get('/reviewmc/{lesson}/{reviewType?}','LessonController@reviewmc');
	Route::get('/log-quiz/{lessonId}/{score}', 'LessonController@logQuiz');
	Route::get('/start/{lesson}/', 'LessonController@start');
	Route::get('/rss/{lesson}/', 'LessonController@rss');

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

    // convert to list
	Route::get('/convert-to-list/{lesson}','LessonController@convertToList');

	// ajax
	Route::get('/finished/{lesson}','LessonController@toggleFinished');

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
	Route::get('/{type_flag?}', 'EventController@index');
	Route::post('/{type_flag?}', 'EventController@index');
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

