<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Lang;

use App\Event;
use App\Tag;
use App\Tools;

define('PREFIX', 'tags');
define('LOG_MODEL', 'Tag');
define('TITLE', 'Tag');
define('TITLE_LC', 'tag');
define('TITLE_PLURAL', 'tags');
define('REDIRECT', '/tags');
define('REDIRECT_ADMIN', '/tags');

class TagController extends Controller
{
	public function __construct ()
	{
        //$this->middleware('is_admin')->except(['add']);
        $this->middleware('auth')->except(['add']);

		$this->prefix = PREFIX;
		$this->title = TITLE;
		$this->titlePlural = TITLE_PLURAL;

		parent::__construct();
	}
	
	// for admin only
    public function index()
    {		
		$tags = Tag::select()
			->orderBy('name')
			->get();
		
    	return view('tags.index', $this->getViewData([
				'records' => $tags,
			]));
    }

    public function entries(Tag $tag)
    {		
    	return view('entries.index', ['entries' => $tag->entries]);
    }
	
    public function add()
    {		
    	return view('tags.add', $this->getViewData());
    }

    public function create(Request $request)
    {
		try
		{
			$record = new Tag();
			$record->name = Tools::alphanum($request->name);
			$record->type_flag = $request->type_flag;
			$record->user_id = Auth::id();
			$record->save();

			Event::logAdd(LOG_MODEL, $record->name, '', $record->id);
			Tools::flash('success', Lang::get('flash.New list has been added'));
		}
		catch (\Exception $e)
		{
			$msg = 'Error adding new ' . TITLE_LC;
			Event::logException(LOG_MODEL, LOG_ACTION_ADD, $record->mag, null, $msg . ': ' . $e->getMessage());
			Tools::flash('danger', $msg);

			return back();
		}
				
    	return redirect('/vocabulary'); 
    }
	
    public function addUserFavoriteList()
    {		
    	return view('tags.add', $this->getViewData());
    }

    public function createUserFavoriteList(Request $request)
    {
		try
		{
			$record = new Tag();
			$record->name = Tools::alphanum($request->name);
			$record->type_flag = TAG_TYPE_DEFINITION_FAVORITE;
			$record->user_id = Auth::id();
			$record->save();

			Event::logAdd(LOG_MODEL, $record->name, '', $record->id);
			Tools::flash('success', Lang::get('flash.New list has been added'));
		}
		catch (\Exception $e)
		{
			$msg = 'Error adding new ' . TITLE_LC;
			Event::logException(LOG_MODEL, LOG_ACTION_ADD, $record->mag, null, $msg . ': ' . $e->getMessage());
			Tools::flash('danger', $msg);

			return back();
		}
				
    	return redirect('/vocabulary'); 
    }

    public function edit(Tag $tag)
    {
		$record = $tag;
		
		return view('tags.edit', $this->getViewData([
			'record' => $record,
		]));
    }
	
    public function update(Request $request, Tag $tag)
    {			 
		$tag->name = $request->name;
		$tag->type_flag = $request->type_flag;
		$tag->save();
		
		return redirect('/tags/'); 
    }

    public function confirmdelete(Tag $tag)
    {	
    	if (Auth::check())
        {			
			return view('tags.confirmdelete', $this->getViewData());				
        }           
        else 
		{
             return redirect('/tags');
		}            	
    }
	
    public function delete(Tag $tag)
    {		
    	if (Auth::check())
        {			
			$tag->delete();
		}
		
		return redirect('/tags');
    }	
}

