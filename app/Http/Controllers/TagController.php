<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Tag;

class TagController extends Controller
{
	public function __construct ()
	{
        $this->middleware('is_admin')->except(['index', 'view']);

		$this->prefix = 'tags';
		$this->title = 'Tag';
		$this->titlePlural = 'Tags';

		parent::__construct();
	}
	
    public function index()
    {		
		$tags = Tag::select()
			//->where('user_id', '=', Auth::id())
			->orderByRaw('tags.id ASC')
			->get();
		
    	return view('tags.index', ['tags' => $tags]);
    }

    public function entries(Tag $tag)
    {		
    	return view('entries.index', ['entries' => $tag->entries]);
    }
	
    public function add()
    {
		if (!$this->isAdmin())
             return redirect('/');
		
    	return view('tags.add', ['data' => $this->viewData]);
    }

    public function create(Request $request)
    {		 
    	$tag = new Tag();
    	$tag->name = $request->name;
    	$tag->user_id = Auth::id();
    	$tag->save();
		
    	return redirect('/tags'); 
    }

    public function edit(Tag $tag)
    {
    	if (Auth::check())
        {
			return view('tags.edit', ['tag' => $tag, 'data' => $this->viewData]);			
        }
        else 
		{
             return redirect('/tags');
		}            	
    }
	
    public function update(Request $request, Tag $tag)
    {			 
    	if (Auth::check())
        {
			$tag->name = $request->name;
			$tag->save();
			
			return redirect('/tags/'); 
		}
		else
		{
			return redirect('/');
		}
    }

    public function confirmdelete(Tag $tag)
    {	
    	if (Auth::check())
        {			
			return view('tags.confirmdelete', ['tag' => $tag, 'data' => $this->viewData]);				
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

