<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Auth;

// user types
define('USER_SUBSCRIBER', -1);		// subscriber
define('USER_UNCONFIRMED', 0);		// user account email unconfirmed
define('USER_CONFIRMED', 100);		// user confirmed
define('USER_MEMBER', 200);		    // user paid member
define('USER_AFFILIATE', 300);		// affiliate
define('USER_SITE_ADMIN', 1000);	// user site admin
define('USER_SUPER_ADMIN', 10000);	// user super admin

class User extends Authenticatable
{
    use Notifiable;

	private static $userTypes = [
		USER_SUBSCRIBER => 'Subscriber',
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


    static public function getSuperAdminUserId()
    {
        $superAdminId = null;
        $record = null;
        $id = null;

		try
		{
            $record = User::select()
                ->where('site_id', SITE_ID)
                ->where('user_type', USER_SUPER_ADMIN)
                ->first();
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting super admin user id';
			Event::logException('user', LOG_ACTION_SELECT, null, null, $msg . ': ' . $e->getMessage());
			Tools::flash('danger', $msg);
		}

		if ($record !== NULL)
		{
		    $id = $record->id;
		}
		else
		{
			$msg = 'No super admin record found';
			Event::logError('user', LOG_ACTION_SELECT, /* title = */ $msg);
			Tools::flash('danger', $msg);
		}

		return $id;
	}

    static public function getIndex()
    {
		$records = User::select()
			->where('site_id', SITE_ID)
			->get();

		return $records;
	}

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
	    return User::$userTypes[$this->user_type];
	}

	static public function isOwner($user_id)
	{
		return ((Auth::check() && Auth::id() == $user_id) || self::isAdmin());
	}

	static public function isAdmin()
	{
		return (Auth::check() && Auth::user()->user_type >= USER_SITE_ADMIN);
	}

	static public function isSuperAdmin()
	{
		return (Auth::check() && Auth::user()->user_type >= USER_SUPER_ADMIN);
	}

	public function isSuperAdminUser()
	{
		return ($this->user_type >= USER_SUPER_ADMIN);
	}
}
