<?php

namespace App\Http\Middleware;

use Closure;
use App\Tools;

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
				dd($role);
				// i don't think this is being used for super admin
				if (Tools::isSuperAdmin()) {
					return $next($request);
				}
			}
			else
			{
				if (Tools::isAdmin()) {
					return $next($request);
				}
			}
		}

        return redirect('unauthorized');
    }
}
