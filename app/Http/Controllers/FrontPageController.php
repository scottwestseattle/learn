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
    }
	
    /**
     * Show the application front page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('frontpage.index');
    }	
	
    public function about()
    {
        return view('frontpage.about');
    }

    public function contact()
    {
        return view('frontpage.contact');
    }

    public function privacy()
    {
        return view('frontpage.privacy');
    }

    public function terms()
    {
        return view('frontpage.terms');
    }

    public function signup()
    {
        return view('frontpage.signup');
    }
	
}
