<?php

namespace App;

use Auth;
use Lang;
use App\User;
use DateTime;

class Status
{
    const _releaseFlags = [
		RELEASE_NOTSET => 'Not Set',
		RELEASE_PRIVATE => 'Private',
		RELEASE_APPROVED => 'Approved',
		RELEASE_PAID => 'Paid',
		RELEASE_MEMBER => 'Member',
		RELEASE_PUBLIC => 'Public',
    ];

	const _wipFlags = [
		WIP_NOTSET => 'Not Set',
		WIP_INACTIVE => 'Inactive',
		WIP_DEV => 'Dev',
		WIP_TEST => 'Test',
		WIP_FINISHED => 'Finished',
	];

    static public function isFinished($wip_flag)
    {
		return ($wip_flag == WIP_FINISHED);
	}
    static public function isPublished($release_flag)
    {
		return ($release_flag == RELEASE_PUBLIC);
	}

    static public function getReleaseFlags()
    {
		return self::_releaseFlags;
	}

    static public function getWipFlags()
    {
		return self::_wipFlags;
	}

    static public function getReleaseStatus($release_flag)
    {
		$btn = '';
		$text = Tools::safeArrayGetString(self::_releaseFlags, $release_flag, 'Unknown Value: ' . $release_flag);
		$done = false;

		switch ($release_flag)
		{
			case RELEASE_NOTSET:
				$btn = 'btn-danger';
				break;
			case RELEASE_PRIVATE:
				$btn = 'btn-primary';
				break;
			case RELEASE_APPROVED:
				$btn = 'btn-success';
				break;
			case RELEASE_PAID:
				$btn = 'btn-success';
				break;
			case RELEASE_MEMBER:
				$btn = 'btn-success';
				break;
			case RELEASE_PUBLIC:
				// don't show anything for published records
				$btn = '';
				$text = '';
				$done = true;
				break;
			default:
				$btn = 'btn-danger';
				$text = 'Unknown Value';
				break;
		}

		return [
				'btn' => $btn,
				'text' => $text,
				'done' => $done,
			];
	}

    static public function getWipStatus($wip_flag)
    {
		$btn = '';
		$text = Tools::safeArrayGetString(self::_wipFlags, $wip_flag, 'Unknown Value: ' . $wip_flag);
        $done = false;

		switch ($wip_flag)
		{
			case WIP_NOTSET:
				$btn = 'btn-danger';
				break;
			case WIP_INACTIVE:
				$btn = 'btn-info';
				break;
			case WIP_DEV:
				$btn = 'btn-warning';
				break;
			case WIP_TEST:
				$btn = 'btn-primary';
				break;
			case WIP_FINISHED:
				// don't show anything for finished records
				$btn = '';
				$text = '';
				$done = true;
				break;
			default:
				$btn = 'btn-danger';
				$text = 'Unknown Value';
				break;
		}

		return [
				'btn' => $btn,
				'text' => $text,
				'done' => $done,
			];
	}

}
