<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Lang;
use Auth;

use App\Tools;
use App\Event;
use App\Visitor;
use App\User;

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
		$addressTo = 'scott@scotthub.com';
		$addressTo = 'sbwilkinson1@gmail.com';
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
    public function wod()
    {
		$users = User::getIndex();

		foreach($users as $record)
		{
			if ($record->isSuperAdminUser())
			{
				$this->sendWod($record);
				//break; // too many emails per second
			}
		}

		if (Auth::check())
			return redirect('/events');
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
			$msg = $from . ': ' . $addressFrom . ', ' . $to . ': ' . $addressTo;
			try
			{
				$email = new SendMailable($name);

				$email->word = $word->title;
				$email->definition = $word->description;
				$email->subject = Lang::get('content.Word of the Day');

				$d = 'https://' . (Tools::isLocalhost() ? 'learnfast.xyz' : Tools::getDomainName());
				$email->link = $d . '/words/edit-user/' . $word->id;

				if (!$debug)
					Mail::to($addressTo)->send($email);

				$msg = Lang::get('flash.Email has been sent') . ': ' . $msg;

				Event::logInfo(LOG_MODEL_HOME, LOG_ACTION_EMAIL, $msg);

				Tools::flash('success', 'translated.' . $msg);
			}
			catch (\Exception $e)
			{
				$msg = Lang::get('flash.Error sending email') . ': ' . $msg;
				Event::logException(LOG_MODEL_HOME, LOG_ACTION_EMAIL, $msg, null, $e->getMessage());
				Tools::flash('danger', 'translated.' . $msg);
			}
		}
		else
		{
			$msg = 'Error getting word of the day';
			Event::logException(LOG_MODEL_HOME, LOG_ACTION_SELECT, $msg);
			Tools::flash('danger', $msg);
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
		//
		// get some user stats
		//
		$lastLogin = Event::getLast(LOG_TYPE_TRACKING, LOG_MODEL_USERS, LOG_ACTION_LOGIN);
		$stats['lastLogin'] = isset($lastLogin) ? $lastLogin->created_at : Lang::get('content.none');
		$stats['accountCreated'] = Auth::check() ? Auth::user()->created_at : Lang::get('content.none');

        return view('home.index', $this->getViewData([
			'stats' => $stats,
			]));
    }

    public function admin()
    {

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
			->get();

		//
		// get today's visitors
		//
		$visitors = VisitorController::removeRobots(Visitor::getVisitors());
        $ip = Tools::getIp();
        $location = Tools::getIpLocation($ip);
        $location = (strlen($location) > 0) ? '(' . $location . ')' : '';

		return view('home.admin', $this->getViewData([
			'events' => $events,
			'users' => $users,
			'visitors' => $visitors,
			'comments' => $comments,
			'ip' => $ip,
			'location' => $location,
			'new_visitor' => Visitor::isNew($ip),
		]));
    }

    public function search(Request $request)
    {
		$search = null;
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
			'isPost' => $isPost,
			'count' => $count,
			'search' => $search,
		]));
	}

}
