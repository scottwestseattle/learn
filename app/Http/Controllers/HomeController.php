<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Lang;

use App\Tools;
use App\Event;
use App\Visitor;
use App\User;
use App\Course;
use App\Lesson;
use App\Word;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth')->except('index');

		parent::__construct();
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
		// user's vocab lists
		//
		$words = Word::getIndex();
		
		//
		// get user's last viewed lesson so he can resume where he left off
		//
		$record = Lesson::getCurrentLocation();
		$date = $record['date'];
		$lesson = $record['lesson'];
		$course = isset($lesson) ? $lesson->course : null;

		//
		// get some user stats
		//
		$lastLogin = Event::getLast(LOG_TYPE_TRACKING, LOG_MODEL_USERS, LOG_ACTION_LOGIN);
		$lastLogin = isset($lastLogin) ? $lastLogin->created_at : Lang::get('content.none');
		
        return view('home.index', $this->getViewData([
			'course' => $course,
			'lesson' => $lesson,
			'lessonDate' => $record['date'],
			'words' => $words,
			'lastLogin' => $lastLogin,
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
		$sites = null;
		if (User::isSuperAdmin())
		{
			$sites = [
				'English50.com',
				'Spanish50.com',
				'VirtualEnglish.xyz',
				'Conversar.xyz',
				'LearnFast.xyz',
			];
		}

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
		return view('home.admin', $this->getViewData([
			'sites' => $sites,
			'events' => $events,
			'users' => $users,
			'visitors' => $visitors,
			'comments' => $comments,
			'courses' => $courses,
			'ip' => $ip,
			'new_visitor' => Visitor::isNew($ip),
		]));
    }
}
