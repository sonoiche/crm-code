<?php

namespace QxCMS\Modules\Client\Controllers\CRM;

use Carbon\Carbon;
use Config;
use Illuminate\Http\Request;
use QxCMS\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use QxCMS\Modules\Client\Repositories\CRM\Calendar\EventRepositoryEloquent as Event;
use QxCMS\Modules\Likod\Models\Clients\User;

class EmailController extends Controller
{
	protected $eventRepo;
	protected $user;

	public function __construct(Event $eventRepo, User $user)
	{
		$this->eventRepo = $eventRepo;
		$this->user = $user;
	}

    public function template()
    {
    	config([
            'mail.driver' => 'smtp',
            'mail.host' => 'smtp.mailtrap.io',
            'mail.port' => 2525,
            'mail.username' => '6126fad658c4b2',
            'mail.password' => 'c95d974c6bef38',
            'mail.encryption' => ''
        ]);

    	$body = "";
    	$today = Carbon::now()->format('Y-m-d');
    	$todaydisplay = Carbon::now()->format('F d, Y');
    	$sql = "date(event_date) = '".$today."'";
    	$events =  $this->eventRepo->rawWith(['activity','customer'], $sql, 'event_date');

    	$details = '<table border="0" align="center" width="790" cellpadding="0" cellspacing="0" class="container590">';
            $details .= '<tr>';
                $details .= '<td align="center" style="color: #343434; font-size: 24px; font-family: Quicksand, Calibri, sans-serif; font-weight:700;letter-spacing: 3px; line-height: 35px;" class="main-header">';
                    $details .= '<div style="line-height: 35px">';
                        $details .= 'Today\'s Schedule <span style="color: #5caad2;">'.$todaydisplay.'</span>';
                    $details .= '</div>';
                $details .= '</td>';
            $details .= '</tr>';
            $details .= '<tr>';
                $details .= '<td height="10" style="font-size: 10px; line-height: 10px;">&nbsp;</td>';
            $details .= '</tr>';
            $details .= '<tr>';
                $details .= '<td align="center">';
                    $details .= '<table border="0" width="40" align="center" cellpadding="0" cellspacing="0" bgcolor="eeeeee">';
                        $details .= '<tr>';
                            $details .= '<td height="2" style="font-size: 2px; line-height: 2px;">&nbsp;</td>';
                        $details .= '</tr>';
                    $details .= '</table>';
                $details .= '</td>';
            $details .= '</tr>';
            $details .= '<tr>';
                $details .= '<td height="20" style="font-size: 20px; line-height: 20px;">&nbsp;</td>';
            $details .= '</tr>';
            $details .= '<tr>';
                $details .= '<td align="left" style="color: #888888; font-size: 16px; font-family: \'Work Sans\', Calibri, sans-serif; line-height: 24px;">';
                    foreach($events as $event){
                    $company = (count($event->customer)) ? $event->customer->name : 'No Customer Tagged';
                    $activity = (count($event->activity)) ? $event->activity->name : 'No Activity Tagged';
                    $time = ($event->event_date!='0000-00-00 00:00:00') ? Carbon::parse($event->event_date)->format('g:i A') : '';
                    $details .= '<table border="0" width="790" align="center" cellpadding="2" cellspacing="2" class="container590">';
                        $details .= '<tr>';
                            $details .= '<td style="width: 25%"><small>Company</small><br>'.$company.'</td>';
                            $details .= '<td style="width: 10%"><small>Time</small><br>'.$time.'</td>';
                            $details .= '<td style="width: 10%"><small>Activity</small><br>'.$activity.'</td>';
                        $details .= '</tr>';
                        $details .= '<tr>';
                            $details .= '<td colspan="3">';
                                $details .= '<b>Attendee: </b>'.$event->attendee_display.'<br>';
                                $details .= '<b>Details: </b> '.$event->details.'';
                            $details .= '</td>';
                        $details .= '</tr>';
                    $details .= '</table>';
                    $details .= '<br><br>';
                    }
                    $details .= '<p style="padding-top: 80px">';
                        $details .= 'Regards,</br>';
                        $details .= 'The Quantum X team';
                    $details .= '</p>';
                $details .= '</td>';
            $details .= '</tr>';
        $details .= '</table>';

        // $sender = 'crm@quantumx.com';
        // $sendername = 'QX CRM';
        // $subject = 'CRM Today\'s Schedule '.$todaydisplay;       
        // $data = ['body'=>$details];        
        
        // $send_email = Mail::send('Client::crm.email.template', $data, function($message) use ($sender, $sendername, $subject) {
        //     $message->from($sender, $sendername);
        //     $message->to('jelson@quantumx.com');
        //     $message->subject($subject);
        // });

        $users = $this->user->where('status',1)->whereIn('id', [1,10,11])->pluck('email');
        return $users;

    	return view('Client::crm.email.template', compact('body','events','todaydisplay'));
    }
}
