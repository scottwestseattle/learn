<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Auth;

// user types
define('USER_UNCONFIRMED', 0);		// user account email unconfirmed
define('USER_CONFIRMED', 100);		// user confirmed
define('USER_MEMBER', 200);		// user paid member
define('USER_AFFILIATE', 300);		// affiliate
define('USER_SITE_ADMIN', 1000);	// user site admin
define('USER_SUPER_ADMIN', 10000);	// user super admin

class User extends Authenticatable
{
    use Notifiable;

	private static $userTypes = [
		USER_UNCONFIRMED => 'Unconfirmed',
		USER_CONFIRMED => 'Confirmed',
		USER_MEMBER => 'Member',
		USER_AFFILIATE => 'Affiliate',
		USER_SITE_ADMIN => 'Admin',
		USER_SUPER_ADMIN => 'Super Admin',
	];
	
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'ip_register', 'site_id'
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

	public function getBlocked()
	{
		return $this->blocked_flag ? 'yes' : 'no';
	}

	public function getUserTypes()
	{
		return User::$userTypes;
	}
	
	public function getUserType()
	{
		$v = '';
	
		switch($this->user_type)
		{
			case USER_UNCONFIRMED:
				$v = 'Unconfirmed';
				break;
			case USER_CONFIRMED:
				$v = 'Confirmed';
				break;
			case USER_MEMBER:
				$v = 'Member';
				break;
			case USER_AFFILIATE:
				$v = 'Affiliate';
				break;
			case USER_SITE_ADMIN:
				$v = 'Admin';
				break;
			case USER_SUPER_ADMIN:
				$v = 'Super Admin';
				break;
			default:
				$v = 'Unknown';
		}
		
		return $v;
	}
	
	static public function isAdmin()
	{
		return (Auth::check() && Auth::user()->user_type >= USER_SITE_ADMIN);
	}

	static public function isSuperAdmin()
	{
		return (Auth::check() && Auth::user()->user_type >= USER_SUPER_ADMIN);
	}
}
