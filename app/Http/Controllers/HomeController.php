<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Tools;
use App\Event;
use App\Visitor;
use App\User;
use App\Course;
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
        $this->middleware('auth');

		parent::__construct();
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
		// users's courses that have been started or finished
		//
		$courses = Course::getIndex(['public']);
		
        return view('home.index', [
			'courses' => $courses,
			'words' => $words,
			]);
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
