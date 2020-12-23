<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Lang;
use Auth;

use App\Entry;
use App\Event;
use App\Course;
use App\Definition;
use App\Lesson;
use App\Tools;
use App\User;
use App\Visitor;
use App\VocabList;
use App\Word;

use Illuminate\Support\Facades\Mail;
use App\Mail\SendMailable;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth')->except('index', 'search', 'mail', 'wod');

		parent::__construct();
    }

    public function mail()
    {
		$name = 'scott';
		$addressTo = 'scottscott@yopmail.com';
		$addressFrom = env('MAIL_FROM_ADDRESS', '63f42e54a4-f10d4b@inbox.mailtrap.io');
		$to = Lang::get('content.To:');
		$from = Lang::get('content.From:');

		$msg = $from . ' ' . $addressFrom . ', ' . $to . ' ' . $addressTo;

		try
		{
			Mail::to($address)->send(new SendMailable($name));

			$msg = 'Email has been sent ' . $msg;

			Event::logInfo(LOG_MODEL_HOME, LOG_ACTION_EMAIL, $msg);

			Tools::flash('success', $msg);
		}
		catch (\Exception $e)
		{
			$msg = 'Error sending email ' . $msg;
			Event::logException(LOG_MODEL_HOME, LOG_ACTION_EMAIL, $msg, null, $e->getMessage());
			Tools::flash('danger', $msg);
		}

		if (Auth::check())
			return redirect('/events');
		else
			return redirect('/');
	}

	//
	// word of the day
	//
	// this is the main function used in the cron
	// cron settings:
	// Minute: 0
	// Hour: 7,19
	// Day/Month/Weekday: *
	// Command: wget https://learnfast.xyz/send/email
	//
    public function wod()
    {
		$users = User::getIndex();

		foreach($users as $record)
		{
		    // for now, just send one for testing
			if ($record->isSuperAdminUser())
			{
				$this->sendWod($record);
			}
		}

		if (Auth::check())
			return redirect('/admin');
		else
			return redirect('/');
	}

    public function sendWod($user)
    {
		$name = $user->name;
		$addressTo = $user->email;
		$addressFrom = env('MAIL_FROM_ADDRESS', '63f42e54a4-f10d4b@inbox.mailtrap.io');
		$to = Lang::get('content.To');
		$from = Lang::get('content.From');
		$debug = false;

		//
		// get the wod
		//
		$word = Word::getWod($user->id);
		if (isset($word))
		{
			//
			// send the email
			//
			// From: from@mail.com, To: to@mail.com
			$msg = $from . ': ' . $addressFrom . ', ' . $to . ': ' . $addressTo . ', ' . $word->title;
			try
			{
				$email = new SendMailable($name);

				$email->word = $word->title;
				$email->definition = $word->description;
				$email->subject = Lang::get('content.Word of the Day') . ': ' . $word->title;

				$d = 'https://' . (Tools::isLocalhost() ? 'learnfast.xyz' : Tools::getDomainName());
				$email->link = $d . '/words/view/' . $word->id;

				if (!$debug)
					Mail::to($addressTo)->send($email);

				$msg = Lang::get('flash.Email has been sent') . ': ' . $msg;
				Event::logInfo(LOG_MODEL_HOME, LOG_ACTION_EMAIL, $msg);
				Tools::flash('success', 'translated.' . $msg);

				// since it was successfully set, update it's latest viewed time so it goes to the end of the list
				$word->updateLastViewedTime();
			}
			catch (\Exception $e)
			{
				$msg = Lang::get('flash.Error sending email') . ': ' . $msg;
				Event::logException(LOG_MODEL_HOME, LOG_ACTION_EMAIL, $msg, null, $e->getMessage());
				Tools::flash('danger', 'translated.' . $msg);
			}
		}
   }

    public function unauthorized()
    {
        return view('home.unauthorized', [
			]);
    }

    public function authenticated()
    {
		Event::logTracking(LOG_MODEL_USERS, LOG_ACTION_LOGIN);

		return $this->index();
    }

    public function hash()
    {
		return view('home.hash', $this->getViewData([
			'hash' => '',
			'hashed' => '',
		]));
	}

	public function hasher(Request $request)
	{
		$hash = trim($request->get('hash'));
		$hashed = Tools::getHash($hash);

		if (Tools::startsWith($hash, 'F'))
			$hashed .= '!';
		else
			$hashed .= '#';

		return view('home.hash', $this->getViewData([
			'hash' => $hash,
			'hashed' => $hashed,
		]));
	}

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // if not logged in send them to the fp
        if (!Auth::check())
            return redirect('/');

        $words = null;
		$course = null;
		$lesson = null;
		$quizes = null;
		$vocabLists = null;
		$stats['lessonDate'] = null;

		if (false && Tools::siteUses(LOG_MODEL_WORDS))
		{
			//
			// user's vocab lists
			//
			$vocabLists = VocabList::getIndex(['owned']);
		}

		if (Tools::siteUses(LOG_MODEL_LESSONS))
		{
			//
			// get user's last viewed lesson so he can resume where he left off
			//
			$record = Lesson::getCurrentLocation();
			$lesson = $record['lesson'];
			$course = isset($lesson) ? $lesson->course : null;
			$stats['lessonDate'] = $record['date'];

			//
			// get quiz results
			//
			$quizes = Lesson::getQuizScores(5);
		}
		//
		// get some user stats
		//
		$lastLogin = Event::getLast(LOG_TYPE_TRACKING, LOG_MODEL_USERS, LOG_ACTION_LOGIN);
		$stats['lastLogin'] = isset($lastLogin) ? $lastLogin->created_at : Lang::get('content.none');
		$stats['accountCreated'] = Auth::check() ? Auth::user()->created_at : Lang::get('content.none');

        return view('home.index', $this->getViewData([
			'course' => $course,
			'lesson' => $lesson,
			'quizes' => $quizes,
			'stats' => $stats,
			'words' => $words,
			'vocabLists' => $vocabLists,
			]));
    }

    public function admin()
    {
		//
		// courses that aren't finished
		//
		$courses = Course::getIndex(['unfinished']);

		//
		// get Sites
		//
		$sites = Tools::getSiteIds();

		//
		// get unapproved comments
		//
		$comments = null;
		/*todo:
		$comments = Comment::select()
			->where('deleted_flag', 0)
			->where('approved_flag', 0)
			->orderByRaw('id DESC')
			->get();
		if (count($comments) === 0)
			$comments = null;
		*/


		//
		// get latest events
		//
		$events = Event::getAlerts(10);

		//
		// get latest users
		//
		$users = User::select()
			->where('user_type', '<=', USER_UNCONFIRMED)
			->orderByRaw('id DESC')
			//->limit(5)
			->get();

		//
		// get today's visitors
		//
		$visitors = VisitorController::removeRobots(Visitor::getVisitorsGrouped());
		//dd($visitors);


		$ip = Tools::getIp();
		return view('home.admin', $this->getViewData([
			'sites' => $sites,
			'events' => $events,
			'users' => $users,
			'visitors' => $visitors,
			'comments' => $comments,
			'courses' => $courses,
			'ip' => $ip,
			'new_visitor' => Visitor::isNew($ip),
			'domain' => Tools::getDomainName(),
		]));
    }

    public function search(Request $request)
    {
		$search = null;
		$definitions = null;
		$entries = null;
		$lessons = null;
		$words = null;
		$wordsUser = null;
		$isPost = $request->isMethod('post');
		$count = 0;

		if ($isPost)
		{
			// do the search

			$search = $request->searchText;
			if (strlen($search) > 1)
			{
				try
				{
					// double check for dangerous characters
					//$clean = preg_replace("/[^a-zA-Z0-9\s\p{P}]/", 'X', $search);
					$clean = preg_replace("/[^a-zA-Z0-9\s\.\-\,\?\'\;\(\)\_\=\+\!\#\$\%\*áéíóúüñÁÉÍÚÜÑ]/", '', $search);
					if ($search != $clean) // if not alphanumeric or punctuation
					{
						$search = $clean;
						throw new \Exception("dangerous search characters");
					}

					if (Tools::siteUses(LOG_MODEL_ARTICLES))
					{
						$entries = Entry::search($search);
						$count += (isset($entries) ? count($entries) : 0);
					}

					if (Tools::siteUses(LOG_MODEL_DEFINITIONS))
					{
						$definitions = Definition::searchGeneral($search);
						$count += (isset($definitions) ? count($definitions) : 0);
					}

					if (Tools::siteUses(LOG_MODEL_LESSONS))
					{
						$lessons = Lesson::search($search);
						$count += (isset($lessons) ? count($lessons) : 0);
					}

					if (Tools::siteUses(LOG_MODEL_WORDS))
					{
						$words = Word::search($search);
						$count += (isset($words) ? count($words) : 0);
					}
				}
				catch (\Exception $e)
				{
					$msg = 'Search Error';
					Event::logException(LOG_MODEL_HOME, LOG_ACTION_SEARCH, $msg . ': ' . $search, null, $e->getMessage());
					Tools::flash('danger', $msg);
				}
			}
		}
		else // show the results
		{
		}

		return view('home.search', $this->getViewData([
			'lessons' => $lessons,
			'words' => $words,
			'definitions' => $definitions,
			'entries' => $entries,
			'isPost' => $isPost,
			'count' => $count,
			'search' => $search,
		]));
	}

}
