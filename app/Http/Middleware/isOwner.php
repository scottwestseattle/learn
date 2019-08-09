<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class isOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $id = null)
    {
		if (Auth::check())
		{
			$p = $request->route()->parameters();
			if (isset($p) && array_key_exists('word', $p))
			{
				$record = $p['word'];
				
				if (isset($record) && $record->user_id == Auth::id())
				{
					return $next($request);
				}
			}
		}

        return redirect('unauthorized');		
    }
}
