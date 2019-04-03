<?php

namespace QxCMS\Modules\Client\Controllers\CRM;

use QxCMS\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Config;

class APITaskController extends Controller
{
	public function taskLogin()
	{
		$email = auth()->user()->email;
		$password = auth()->user()->password;
		$client_id = taskapi();
		$post = [
	        'email' => $email,
	        'client_id' => $client_id
	    ];

	    $ch = curl_init('http://192.168.2.133:8000/task/auth/apilogin');
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

	    // execute!
	    $response = curl_exec($ch);

	    // close the connection, release resources used
	    curl_close($ch);
	}

	public function taskLogout()
	{
		$email = auth()->user()->email;
		$password = auth()->user()->password;
		$client_id = taskapi();
		$post = [
	        'email' => $email,
	        'client_id' => $client_id
	    ];

	    $ch = curl_init(config('app.taskmoduleurl').'task/auth/apilogout');
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

	    // execute!
	    $response = curl_exec($ch);

	    // close the connection, release resources used
	    curl_close($ch);
	}

    public function taskList()
    {
    	$client_id = taskapi();
    }
}
