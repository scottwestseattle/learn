<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use App\User;

class isOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
		//
		// This only works when the full model is passed as a parameter
		//
		if (Auth::check())
		{
			if (User::isAdmin())
				return $next($request);
			
			$p = $request->route()->parameters();
			if (isset($p))
			{
				foreach($p as $record)
				{
					if (isset($record->user_id) && $record->user_id == Auth::id())
					{
						return $next($request);
					}
				}
			}
			
			// user logged in but he's not the owner			
			return redirect('/404/' . $request->route()->uri() . ' - not owner');  
		}

		return redirect('/login'); // not logged in
    }
}
