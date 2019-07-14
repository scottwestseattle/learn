<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Auth;
use App\Tools;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
		View::share('domainName', Tools::getDomainName());
		View::share('isAdmin', false); // Auth::user not available yet but set the variable anyway so code doesn't break
		View::share('isSuperAdmin', false);  // Auth::user not available yet but set the variable anyway so code doesn't break
    }
}
