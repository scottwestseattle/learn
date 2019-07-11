<?php

namespace App\Http\Middleware;

use Closure;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $role = null)
    {
		if (auth()->user())
		{
			if ($role == 'super')
			{
				if (auth()->user()->isSuperAdmin()) {
					return $next($request);
				}
			}
			else
			{
				if (auth()->user()->isAdmin()) {
					return $next($request);
				}
			}
		}

        return redirect('home');
    }
}
