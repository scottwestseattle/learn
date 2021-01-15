<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use DB;
use Auth;
use Lang;

use App\Course;
use App\Definition;
use App\Entry;
use App\Event;
use App\Lesson;
use App\User;
use App\Tools;
use App\VocabList;
use App\Word;

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
     * Handle the start button from the front page.
     */
    public function start()
    {
		return redirect('/courses/start');
	}

    /**
     * Show the application front page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index($id = null)
    {
        $bannerIx = isset($id) && Tools::siteUses(ID_FEATURE_BANNERPHOTO) ? intval($id) : null;

		$courses = []; // make this countable so view will always work
		$vocabLists = [];
		$articles = [];
		$lesson = [];
		$wod = null;
		$randomWord = null;

        // get word of the day
		if (false && Tools::siteUses(ID_FEATURE_LISTS))
		{
			$wod = Word::getWod(User::getSuperAdminUserId());

			if (isset($wod))
			{
				// format the examples to display as separate sentences
				$wod->examples = Tools::splitSentences($wod->examples);
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
		}

		if (Tools::siteUses(ID_FEATURE_COURSES) && Auth::check())
		{
			//
			// get user's last viewed lesson so he can resume where he left off
			//
			$lesson = Lesson::getCurrentLocation();
			$lesson['course'] = isset($lesson['lesson']) ? $lesson['lesson']->course : null;
		}

		if (Tools::siteUses(ID_FEATURE_COURSES))
		{
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
		}

        // voice recorder
        $recorder = Tools::siteUses(ID_FEATURE_RECORD);

        // articles
		if (Tools::siteUses(ID_FEATURE_ARTICLES))
		{
			try
			{
				$articles = Entry::getArticles($recorder ? 10 : 3);
			}
			catch (\Exception $e)
			{
				//dump($e);
				$msg = 'Error getting Articles';
				Event::logException(LOG_MODEL, LOG_ACTION_SELECT, $msg, null, $e->getMessage());
				Tools::flash('danger', $msg);
			}
		}

		$jumboTitle = null;
		$jumboSlug = 'jumboSlug';
		$banner = null;
		$potd = null;
		$wotd = null;
		$supportMessage = null;

        if (Tools::getSiteLanguage() == LANGUAGE_ES)
		{
			$randomWord = Definition::getRandomWord();
			$jumboTitle = 'jumboTitleSpanish';

            // get word of the day
            $wotd = Word::getWotd();

            // get the phrase of the day
            $potd = Word::getPotd();
            $potd = isset($potd) ? $potd : Lang::get('ui.Not Found');

            $files = preg_grep('/^([^.])/', scandir(base_path() . '/public/img/banners')); // grep removes the hidden files
            $fileCount = count($files);
            $lastIx = $fileCount;
            if (isset($bannerIx))
            {
                $ix = ($bannerIx <= $fileCount && $bannerIx > 0) ? $bannerIx : $lastIx;
            }
            else
            {
                $ix = rand(1, $fileCount);
            }
            $banner = 'es-banner' . $ix . '.png';

			$supportMessage = Lang::get('content.Support Message');
		}
		else if ((Tools::getSiteLanguage() == LANGUAGE_EN))
		{
			$jumboTitle = 'jumboTitleEnglish';
		}

        $view = 'frontpage.index';

        //
        // all the stuff for the speak and record module
        //
        $options = [];

        // get the snippets for the appropriate langauge
		$languageId = Tools::getSiteLanguage();
		$languageFlagCondition = ($languageId == LANGUAGE_ALL) ? '>=' : '=';
        $snippetsLimit = $recorder ? 10 : 5;
        $snippets = Word::getSnippets(['limit' => $snippetsLimit, 'languageId' => $languageId, 'languageFlagCondition' => $languageFlagCondition]);
        $options['records'] = $snippets;

        $options['siteLanguage'] = Tools::getLanguage();
        $options['showAllButton'] = true;
        $options['loadSpeechModules'] = true;
        $options['snippetLanguages'] = Tools::getLanguageOptions();
        $options['returnUrl'] = '/';

        // not implemented yet
        $options['snippet'] = null; //Word::getSnippet();
        if (!isset($options['snippet']))
        {
            $options['snippet'] = new Word();
            $options['snippet']->description = Lang::get('fp.recorderTextInit');
            $options['snippet']->language_flag = $languageId;
        }

        $options['languageCodes'] = null;
        if ($recorder)
        {
            $options['languageCodes'] = Tools::getSpeechLanguage($options['snippet']->language_flag);
        }
        else
        {
            $options['languageCodes'] = Tools::getSpeechLanguage();
        }
//dd($options);
		return view('frontpage.' . Tools::siteView(), $this->getViewData([
			'courses' => $courses,
			'vocabLists' => $vocabLists,
			'articles' => $articles,
			'lesson' => $lesson,
			'jumboTitle' => $jumboTitle,
			'jumboSlug' => $jumboSlug,
			'randomWord' => $randomWord,
			'banner' => $banner,
			'wod' => $wod,
			'wotd' => $wotd,
			'potd' => $potd,
			'supportMessage' => $supportMessage,
			'showShortcutWidgets' => Tools::siteUsesShortcutWidgets(),
            'options' => $options,
		], LOG_MODEL, LOG_PAGE_INDEX));
    }

    /**
     * Subscribe to the mailing list
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function subscribe(Request $request)
    {
        $user = new User;
        $user->email = $request->email;
        $user->name = $request->email;
        $user->password = Hash::make('noway' . $request->email);
        $user->ip_register = Tools::getIp();
        $user->site_id = Tools::getSiteId();
        $user->user_type = USER_SUBSCRIBER;

        $this->validate($request, [
          'email' => 'required|regex:/(.+)@(.+)\.(.+)/i',
        ]);

        $details = ': ' . $user->email . ' (' . $user->ip_register . ')';
        try
        {
            $user->save();

            $msg = 'Email address added to mailing list';
    		Tools::flash('success', $msg);
            Event::logInfo(LOG_MODEL, LOG_ACTION_EMAIL, $msg . $details);
        }
        catch (\Exception $e)
        {
            $msg = 'Error adding email address to mailing list';
            Event::logException(LOG_MODEL, LOG_ACTION_EMAIL, $msg . $details, null, $e->getMessage());
            Tools::flash('danger', $msg);
        }

		return redirect()->back();
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
