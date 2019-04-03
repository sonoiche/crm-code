<?php

namespace QxCMS\Modules\Client\Controllers\CRM;

use QxCMS\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
	protected $auth;

	public function __construct()
	{
		$this->auth = auth();
	}

    public function index()
    {
    	$username = $this->auth->user()->username;
    	return \Redirect::to('http://attendance.quantumx.com/attendancelogin.php?usernamexx='.$username.'');
    }
}
