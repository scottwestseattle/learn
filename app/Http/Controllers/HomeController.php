<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Lang;
use Auth;

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
        $this->middleware('auth')->except('index', 'search');

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
		$words = Word::getIndex(/* parentId = */ null, /* limit = */ 10);
		
		//
		// get user's last viewed lesson so he can resume where he left off
		//
		$record = Lesson::getCurrentLocation();
		$lesson = $record['lesson'];
		$course = isset($lesson) ? $lesson->course : null;
		$stats['lessonDate'] = $record['date'];
		
		//
		// get some user stats
		//
		$lastLogin = Event::getLast(LOG_TYPE_TRACKING, LOG_MODEL_USERS, LOG_ACTION_LOGIN);
		$stats['lastLogin'] = isset($lastLogin) ? $lastLogin->created_at : Lang::get('content.none');
		$stats['accountCreated'] = Auth::check() ? Auth::user()->created_at : Lang::get('content.none');
		
		//
		// get quiz results
		//
		$quizes = Auth::check() ? ['Africa Hard Ones' => '87.3% (7/9)', 'Spanish Vocabulary 1' => '63.2% (17/29)', 'English 1 - Vocabulary List 3' => '46.7% (11/23)'] : [];
		$quizes = Lesson::getQuizScores(5);

        return view('home.index', $this->getViewData([
			'course' => $course,
			'lesson' => $lesson,
			'quizes' => $quizes,
			'stats' => $stats,
			'words' => $words,
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
	
    public function search(Request $request)
    {
		$search = null;
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
					
					$lessons = Lesson::search($search);
					$count += (isset($lessons) ? count($lessons) : 0);
					
					$words = Word::search($search);
					$count += (isset($words) ? count($words) : 0);			
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
			'isPost' => $isPost,
			'count' => $count,
			'search' => $search,
		]));		
	}
		
}
