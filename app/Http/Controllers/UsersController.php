<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\User;

class UsersController extends Controller
{
	private $redirect = '/users/';
	private $redirect_error = '/';
	
    public function __construct()
    {
        $this->middleware('is_admin');
		
		parent::__construct();		
    }	
	
    public function index()
    {
		if (User::isSuperAdmin())
		{
			// show all users for all sites
			$records = User::select()
				->orderByRaw('id DESC')
				->get();
		}
		else
		{
			// show users for current site
			$records = User::select()
				->where('site_id', SITE_ID)
				->where('user_type', '<=', USER_SITE_ADMIN)
				->orderByRaw('id DESC')
				->get();
		}

		return view('users.index', 
			$this->getViewData([
				'records' => $records,
			])
		);
    }

    public function view(User $user)
    {
		return view('users.view', $this->getViewData([
				'user' => $user,
				'data' => null,
			]));
    }
	
    public function edit(User $user)
    {
		return view('users.edit', $this->getViewData([
				'user' => $user,
				'data' => null,
			]));		
    }
	
    public function update(Request $request, User $user)
    {	
		$user->name = trim($request->name);
		$user->email = trim($request->email);
		$user->user_type = intval($request->user_type);
		$user->password = $request->password;
		$user->blocked_flag = isset($request->blocked_flag) ? 1 : 0;

		$user->save();
		
		return redirect($this->redirect); 
    }

    public function confirmdelete(User $user)
    {				 		
		return view('users.confirmdelete', $this->getViewData([
				'user' => $user,
				'data' => null,
			]));		
    }
	
    public function delete(User $user)
    {	
		$user->deleteSafe();
		
    	return redirect($this->redirect); 
    }	
	
}
