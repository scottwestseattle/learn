<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Auth;

// user types
define('USER_UNCONFIRMED', 0);		// user account email unconfirmed
define('USER_CONFIRMED', 100);		// user confirmed
define('USER_PAID', 200);			// user paid
define('USER_AFFILIATE', 300);		// affiliate
define('USER_SITE_ADMIN', 1000);		// user site admin
define('USER_SUPER_ADMIN', 10000);	// user super admin

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'ip_register'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

	static public function isAdmin()
	{
		return (Auth::check() && Auth::user()->user_type >= USER_SITE_ADMIN);
	}

	static public function isSuperAdmin()
	{
		return (Auth::check() && Auth::user()->user_type >= USER_SUPER_ADMIN);
	}
}
