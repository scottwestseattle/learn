<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use DateTime;
use DateInterval;
use DB;
use App;
use Auth;
use Lang;
use App\User;
use App\Visitor;
use App\Event;
use App\Tools;

define('SITE_ID', intval(env('SITE_ID')));

define('MAX_DB_TEXT_COLUMN_LENGTH', 65535 - 2); // 2 byetes for db overhead

define('SHOW_NON_XS', 'd-none d-sm-block-tablecell');
define('SHOW_XS_ONLY', 'hidden-xl hidden-lg hidden-md hidden-sm');
define('VISITOR_MAX_LENGTH', 200);

// event logger info
define('LOG_TYPE_INFO', 1);
define('LOG_TYPE_WARNING', 2);
define('LOG_TYPE_ERROR', 3);
define('LOG_TYPE_EXCEPTION', 4);
define('LOG_TYPE_TRACKING', 5);
define('LOG_TYPE_OTHER', 99);

define('LOG_MODEL_ARTICLES', 'articles');
define('LOG_MODEL_BOOKS', 'books');
define('LOG_MODEL_COURSES', 'courses');
define('LOG_MODEL_DEFINITIONS', 'definitions');
define('LOG_MODEL_ENTRIES', 'entries');
define('LOG_MODEL_EVENTS', 'events');
define('LOG_MODEL_HOME', 'home');
define('LOG_MODEL_LESSONS', 'lessons');
define('LOG_MODEL_RECORD', 'record');
define('LOG_MODEL_SITES', 'sites');
define('LOG_MODEL_TAGS', 'tags');
define('LOG_MODEL_TRANSLATIONS', 'translations');
define('LOG_MODEL_USERS', 'users');
define('LOG_MODEL_VISITORS', 'visitors');
define('LOG_MODEL_WORDS', 'words');

define('LOG_ACTION_ACCESS', 'access');
define('LOG_ACTION_ADD', 'add');
define('LOG_ACTION_DELETE', 'delete');
define('LOG_ACTION_EDIT', 'edit');
define('LOG_ACTION_EMAIL', 'email');
define('LOG_ACTION_IMPORT', 'import');
define('LOG_ACTION_INDEX', 'index');
define('LOG_ACTION_LOGIN', 'login');
define('LOG_ACTION_LOGOUT', 'logout');
define('LOG_ACTION_MOVE', 'move');
define('LOG_ACTION_MKDIR', 'mkdir');
define('LOG_ACTION_OTHER', 'other');
define('LOG_ACTION_PERMALINK', 'permalink');
define('LOG_ACTION_PUBLISH', 'publish');
define('LOG_ACTION_QUIZ', 'quiz');
define('LOG_ACTION_SCANDIR', 'scandir');
define('LOG_ACTION_SEARCH', 'search');
define('LOG_ACTION_SELECT', 'select');
define('LOG_ACTION_TOUCH', 'touch');
define('LOG_ACTION_TRANSLATE', 'translate');
define('LOG_ACTION_UNDELETE', 'undelete');
define('LOG_ACTION_UPLOAD', 'upload');
define('LOG_ACTION_VIEW', 'view');

define('LOG_PAGE_INDEX', 'index');
define('LOG_PAGE_VIEW', 'view');
define('LOG_PAGE_PERMALINK', 'permalink');

// translations
define('TRANSLATIONS_FOLDER', '../resources/lang/');

// Release Status
define('RELEASE_NOTSET', 0);
define('RELEASE_PRIVATE', 10);	// visible for owner only and can't be promoted until approved
define('RELEASE_APPROVED', 20);	// visible for owner only and can be promoted
define('RELEASE_PAID', 50); 	// visible for logged-in paid members
define('RELEASE_MEMBER', 80); // visible for logged-in members
define('RELEASE_PUBLIC', 100);	// visible for all
define('RELEASE_DEFAULT', RELEASE_PRIVATE);

// Work in progress
define('WIP_NOTSET', 0);
define('WIP_INACTIVE', 10);
define('WIP_DEV', 20);
define('WIP_TEST', 30);
define('WIP_FINISHED', 100);
define('WIP_DEFAULT', WIP_DEV);

// Part of Speech flag
define('POS_NOTSET', 0);
define('POS_VERB', 1);
define('POS_NOUN', 2);
define('POS_ADJECTIVE', 3);
define('POS_ADVERB', 4);
define('POS_OTHER', 20);

// word types
define('WORDTYPE_LESSONLIST',           1);
define('WORDTYPE_LESSONLIST_USERCOPY',  2);
define('WORDTYPE_USERLIST',             3);
define('WORDTYPE_VOCABLIST',            4);
define('WORDTYPE_SNIPPET',              5);
define('WORDTYPE_USERLIST_LIMIT',       20);

// VocabList types
define('VOCABLISTTYPE_POTD', '2');
define('VOCABLISTTYPE_WOTD', '3');

define('TIMED_SLIDES_DEFAULT_BREAK_SECONDS', 20);
define('TIMED_SLIDES_DEFAULT_SECONDS', 50);

// entries
define('ENTRY_TYPE_NOTSET', 	-1);
define('ENTRY_TYPE_ENTRY', 		1);
define('ENTRY_TYPE_ARTICLE', 	2);
define('ENTRY_TYPE_BOOK',	 	3);
define('ENTRY_TYPE_OTHER',		99);

// query sorting
define('ORDERBY_APPROVED', 0);
define('ORDERBY_TITLE', 1);
define('ORDERBY_DATE', 2);
define('ORDERBY_VIEWS', 3);

// Tags
define('TAG_RECENT', 'recent');
define('TAG_BOOK', 'book');

// Tag types
define('TAG_TYPE_NOTSET',			   	0);
define('TAG_TYPE_SYSTEM',				1); // one for everybody, ex: recent article
//define('TAG_TYPE_RECENT_ARTICLE',	   	1); // old way
define('TAG_TYPE_BOOK',				   	2); //not implented yet: need one per book
define('TAG_TYPE_DEFINITION_FAVORITE', 	3); // one per user so we have empty favorites list
define('TAG_TYPE_OTHER',			   	99);
//define('TAG_TYPE_DEFAULT', TAG_TYPE_SYSTEM); // need this?

// Review type
define('REVIEWTYPE_NOTSET', 0);
define('REVIEWTYPE_FLASHCARDS', 1);
define('REVIEWTYPE_FIB', 2);
define('REVIEWTYPE_MC_RANDOM', 3);
define('REVIEWTYPE_MC_FIXED', 4);
define('REVIEWTYPE_MC_MIXED', 5);
define('REVIEWTYPE_DEFAULT', REVIEWTYPE_MC_RANDOM);

define('DEFINITIONS_SEARCH_NOTSET', 0);
define('DEFINITIONS_SEARCH_ALPHA', 1);
define('DEFINITIONS_SEARCH_REVERSE', 2);
define('DEFINITIONS_SEARCH_NEWEST', 3);
define('DEFINITIONS_SEARCH_RECENT', 4);
define('DEFINITIONS_SEARCH_MISSING_TRANSLATION', 5);
define('DEFINITIONS_SEARCH_MISSING_DEFINITION', 6);
define('DEFINITIONS_SEARCH_MISSING_CONJUGATION', 7);
define('DEFINITIONS_SEARCH_WIP_NOTFINISHED', 8);
define('DEFINITIONS_SEARCH_VERBS', 9);
define('DEFINITIONS_SEARCH_ALL', 10);
define('DEFINITIONS_SEARCH_NEWEST_VERBS', 11);
define('DEFINITIONS_SEARCH_RANDOM_VERBS', 12);
define('DEFINITIONS_SEARCH_RANDOM_WORDS', 13);

// optional features
define('ID_FEATURE_ARTICLES',   1);
define('ID_FEATURE_BOOKS',      2);
define('ID_FEATURE_COURSES',    3);
define('ID_FEATURE_DICTIONARY', 4);
define('ID_FEATURE_PREFOOTER',  6);
define('ID_FEATURE_LISTS',      7);
define('ID_FEATURE_RECORD',     8);
define('ID_FEATURE_SUBSCRIBE',  9);
define('ID_FEATURE_BANNERPHOTO', 10);

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	private $domainName = null;

	//todo: make private if this is the approach we are using
	protected $prefix = 'prefix';
	protected $title = 'Title';
	protected $titlePlural = 'Titles';

	public function __construct()
	{
		// session don't work in constructors, work arround:
		$this->middleware(function ($request, $next){

			// set locale according to selected language
			$locale = session('locale');
			if (isset($locale))
				App::setLocale($locale);

			//todo: where does this go?
			//look at:
			if (Auth::user() && Auth::user()->blocked_flag)
			{
                Tools::flash('danger', 'Login Error: User is Blocked');
				Auth::logout();
			}

			return $next($request);
		});

		$id = Tools::getSiteId();
	}

	static private function showPrivacyNotice()
	{
		// don't show for admins or if user has closed it already
		return session('eunotice', false) && !User::isAdmin();
	}

	protected function getViewData($vdata = null, $model = null, $page = null)
	{
		//
		// set up data to send to view
		//
		$this->viewData = isset($vdata) ? $vdata : [];

		//
		// add-on the mandatory parts
		//
//todo:		$this->viewData['site'] = Controller::getSite();
		$this->viewData['siteTitle'] = $this->getSiteTitle();
		$this->viewData['siteTitleLite'] = $this->getSiteTitle(false);
		$this->viewData['showPrivacyNotice'] = Controller::showPrivacyNotice();

		$this->viewData['prefix'] = $this->prefix;
		$this->viewData['title'] = $this->title;
		$this->viewData['titlePlural'] = $this->titlePlural;
		$this->viewData['isAdmin'] = Tools::isAdmin();
		$this->viewData['isSuperAdmin'] = Tools::isSuperAdmin();

		$referrer = Tools::getReferrer();
		$this->viewData['referrer'] = $referrer['input'];
		$this->viewData['referrerUrl'] = $referrer['url'];

		if ($this->getDomainName() == 'localhost')
			$this->viewData['localhost'] = true;

		//
		// optional: save the visitor
		//
		if (isset($model) && isset($page))
		{
		    try
		    {
    			$this->saveVisitor($model, $page);
			}
            catch (\Exception $e)
            {
                Tools::flash('danger', 'Error Saving Visitor - Check Events');
           }
        }

		return $this->viewData;
	}

	protected function getSiteTitle($withDomainName = true)
	{
		$siteTitle = Tools::getSiteTitle($withDomainName);

		return $siteTitle;
	}

	protected function getDomainName()
	{
		// if not set yet
		if (!isset($this->domainName))
		{
			$this->domainName = Tools::getDomainName();
		}

		return $this->domainName;
	}

	protected function saveVisitor($model, $page, $record_id = null)
	{
		//todo: add log visitors to site record
		// ignore these
		if (strtolower($this->getDomainName()) == 'blog.scotthub.com')
			return;

		$spy = session('spy', null);
		if (isset($spy))
			return; // spy mode, don't count views

		if (Auth::check())
			return; // user logged in, don't count views

		if (User::isAdmin())
			return; // admin user, don't count views

		Visitor::add($this->getDomainName(), Tools::getIp(), $model, $page, $record_id);
	}

/****************************************************************************

	Static Functions

****************************************************************************/

    static protected function getDateControlDates()
    {
		$months = [
			1 => 'January',
			2 => 'February',
			3 => 'March',
			4 => 'April',
			5 => 'May',
			6 => 'June',
			7 => 'July',
			8 => 'August',
			9 => 'September',
			10 => 'October',
			11 => 'November',
			12 => 'December',
		];

		$days = [];
		for ($i = 1; $i <= 31; $i++)
			$days[$i] = $i;

		$years = [];
		$startYear = 1997; //
		$endYear = intval(date('Y')) + 1; // end next year
		for ($i = $startYear; $i <= $endYear; $i++)
		{
			$years[$i] = $i;
		}

		$dates = [
			'months' => $months,
			'years' => $years,
			'days' => $days,
		];

		return $dates;
	}

    static protected function getSelectedDate($request)
    {
		$filter = Controller::getFilter($request);

		$date = Tools::trimNull($filter['from_date']);

		return $date;
	}

    static protected function getFilter($request, $today = false, $month = false)
    {
		$filter = Controller::getDateFilter($request, $today, $month);

		$filter['account_id'] = false;
		$filter['category_id'] = false;
		$filter['subcategory_id'] = false;
		$filter['search'] = false;
		$filter['unreconciled_flag'] = false;
		$filter['unmerged_flag'] = false;
		$filter['showalldates_flag'] = false;
		$filter['showphotos_flag'] = false;

		if (isset($request))
		{
			if (isset($request->account_id))
			{
				$id = intval($request->account_id);
				if ($id > 0)
					$filter['account_id'] = $id;
			}

			if (isset($request->category_id))
			{
				$id = intval($request->category_id);
				if ($id > 0)
					$filter['category_id'] = $id;
			}

			if (isset($request->subcategory_id))
			{
				$id = intval($request->subcategory_id);
				if ($id > 0)
					$filter['subcategory_id'] = $id;
			}

			if (isset($request->search))
			{
				if (strlen($request->search) > 0)
					$filter['search'] = $request->search;
			}

			if (isset($request->unreconciled_flag))
			{
				$filter['unreconciled_flag'] = $request->unreconciled_flag;
			}

			if (isset($request->unmerged_flag))
			{
				$filter['unmerged_flag'] = $request->unmerged_flag;
			}

			if (isset($request->showalldates_flag))
			{
				$filter['showalldates_flag'] = $request->showalldates_flag;
			}

			if (isset($request->showphotos_flag))
			{
				$filter['showphotos_flag'] = $request->showphotos_flag;
			}
		}

		return $filter;
	}

    static protected function getDateFilter($request = false, $today, $monthFlag)
    {
		$dates = [];

		$dates['selected_month'] = false;
		$dates['selected_day'] = false;
		$dates['selected_year'] = false;

		$month = 0;
		$year = 0;
		$day = 0;

		if (isset($request) && (isset($request->day) && $request->day > 0 || isset($request->month) && $request->month > 0 || isset($request->year) && $request->year > 0))
		{
			// date filter is on, use it
			if (isset($request->month))
				if (($month = intval($request->month)) > 0)
					$dates['selected_month'] = $month;

			if (isset($request->day))
				if (($day = intval($request->day)) > 0)
					$dates['selected_day'] = $day;

			if (isset($request->year))
				if (($year = intval($request->year)) > 0)
					$dates['selected_year'] = $year;
		}
		else
		{
			if ($today)
			{
				$month = intval(date("m"));
				$year = intval(date("Y"));

				// if we're showing a month then we put then day will be false
				$day = $monthFlag ? false : intval(date("d"));

				// if nothing is set use current month
				$dates['selected_day'] = $day;
				$dates['selected_month'] = $month;
				$dates['selected_year'] = $year;
			}
			else
			{
				$dates['from_date'] = null;
				$dates['to_date'] = null;

				return $dates;
			}
		}

		//
		// put together the search dates
		//

		// set month range
		$fromMonth = 1;
		$toMonth = 12;
		if ($month > 0)
		{
			$fromMonth = $month;
			$toMonth = $month;
		}

		// set year range
		$fromYear = 2010;
		$toYear = 2050;
		if ($year > 0)
		{
			$fromYear = $year;
			$toYear = $year;
		}
		else
		{
			// if month set without the year, default to current year
			if ($month > 0)
			{
				$fromYear = intval(date("Y"));
				$toYear = $fromYear;
			}
		}

		$fromDay = 1;
		$toDate = "$toYear-$toMonth-01";
		$toDay = intval(date('t', strtotime($toDate)));

		if ($day > 0)
		{
			$fromDay = $day;
			$toDay = $day;
		}

		$dates['from_date'] = '' . $fromYear . '-' . $fromMonth . '-' . $fromDay;
		$dates['to_date'] = '' . $toYear . '-' . $toMonth . '-' . $toDay;

		return $dates;
	}

    static protected function getDateControlSelectedDate($date)
    {
		$date = DateTime::createFromFormat('Y-m-d', $date);

		$parts = [
			'selected_day' => intval($date->format('d')),
			'selected_month' => intval($date->format('m')),
			'selected_year' => intval($date->format('Y')),
		];

		return $parts;
	}

    public function pageNotFound404($model, $view = null, $parameters = null)
    {
		$title = 'Page Not Found (404)';
		$description = $model . '/' . $view . '/' . $parameters;
		//$geo = new Geo;
		//$desc = $geo->visitorInfoDebug();
		Event::logError('controller', LOG_ACTION_VIEW, $title, $description);
		$data['title'] = '404';
		$data['name'] = 'Page not found';

		return response()->view('errors.404', $data, 404);
	}

	// can't use 'Request $request' because multiple Request classes are used
    protected function getReferrer($request, $default)
    {
		$rc = $default;
		if (isset($request) && isset($request->referrer))
		{
			$rc = $request->referrer;
		}

		return $rc;
	}
}
