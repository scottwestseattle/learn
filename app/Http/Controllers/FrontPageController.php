<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Auth;

class FrontPageController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
		
		parent::__construct();		
    }
	
    /**
     * Show the application front page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
		return view('frontpage.index', $this->getViewData([
		]));		
    }	
	
    public function about()
    {
		return view('frontpage.about', $this->getViewData([
			'version' => 'Version: 0.0',
		]));		
    }

    public function contact()
    {
		return view('frontpage.contact', $this->getViewData([
		]));		
    }

    public function privacy()
    {
		return view('frontpage.privacy', $this->getViewData([
		]));		
    }

    public function terms()
    {
		return view('frontpage.terms', $this->getViewData([
		]));		
    }

    public function signup()
    {
		return view('frontpage.signup', $this->getViewData([
		]));		
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
		]));		
    }	
	
}
