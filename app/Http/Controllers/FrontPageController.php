<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Auth;
use App\Event;
use App\Tools;
use App\Course;
use App\VocabList;
use App\Word;
use App\User;

define('LOG_MODEL', 'frontpage');

class FrontPageController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
		parent::__construct();
    }

    /**
     * Show the application front page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
		$courses = []; // make this countable so view will always work
		$vocabLists = [];

        // get word of the day
        $wod = Word::getWod(User::getSuperAdminUserId());

		if (isset($wod))
		{
			// format the examples to display as separate sentences
			$wod->examples = Tools::splitSentences($wod->examples);
		}

		try
		{
			$courses = Course::getIndex(['public']);
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting courses';
			Event::logException(LOG_MODEL, LOG_ACTION_SELECT, $msg, null, $e->getMessage());
			Tools::flash('danger', $msg);
		}

		try
		{
			$vocabLists = VocabList::getIndex();
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting Vocab Lists';
			Event::logException(LOG_MODEL, LOG_ACTION_SELECT, $msg, null, $e->getMessage());
			Tools::flash('danger', $msg);
		}

		return view('frontpage.index', $this->getViewData([
			'courses' => $courses,
			'vocabLists' => $vocabLists,
			'wod' => $wod,
		], LOG_MODEL, LOG_PAGE_INDEX));
    }

    /**
     * Show the original application front page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index2()
    {
		if (Tools::isAdmin())
		{
		}
		else if (Auth::check())
		{
		}

		return view('frontpage.index2', $this->getViewData([
		], LOG_MODEL, LOG_PAGE_INDEX));
    }

    public function about()
    {
		return view('frontpage.about', $this->getViewData([
			'version' => 'Version: 0.0',
		], LOG_MODEL, 'about'));
    }

    public function contact()
    {
		$email = 'info@' . Tools::getDomainName();

		return view('frontpage.contact', $this->getViewData([
			'email' => $email,
		], LOG_MODEL, 'contact'));
    }

    public function privacy()
    {
		return view('frontpage.privacy', $this->getViewData([
		], LOG_MODEL, 'privacy'));
    }

    public function terms()
    {
		return view('frontpage.terms', $this->getViewData([
		], LOG_MODEL, 'terms'));
    }

    public function signup()
    {
		return view('frontpage.signup', $this->getViewData([
		], LOG_MODEL, 'signup'));
    }

	public function language($locale)
	{
		if (ctype_alpha($locale))
		{
			session(['locale' => $locale]);
		}

		return redirect()->back();
	}

    public function phpinfo()
	{
		phpinfo();
	}

    public function eunoticeaccept()
    {
		// set eunotice cookie to show that user has accepted it
		session(['eunotice' => true]);
    }

    public function eunoticereset()
    {
		// set eunotice cookie to show that user has accepted it
		session(['eunotice' => false]);

		return redirect()->back();
    }

    public function sample()
    {
		return view('frontpage.sample', $this->getViewData([
		], LOG_MODEL, 'sample'));
    }

}
