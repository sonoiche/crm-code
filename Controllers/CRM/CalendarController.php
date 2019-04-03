<?php

namespace QxCMS\Modules\Client\Controllers\CRM;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use QxCMS\Http\Controllers\Controller;
use QxCMS\Modules\Client\Models\CRM\ActivityType;
use QxCMS\Modules\Client\Models\CRM\Event;
use QxCMS\Modules\Client\Repositories\CRM\Calendar\EventRepositoryEloquent as Events;
use QxCMS\Modules\Client\Repositories\CRM\Customer\CustomerRepositoryEloquent as Customer;
use QxCMS\Modules\Likod\Models\Clients\User;

class CalendarController extends Controller
{
	protected $eventRepo;
	protected $customerRepo;

	public function __construct(Events $eventRepo, Customer $customerRepo)
	{
		$this->eventRepo = $eventRepo;
		$this->customerRepo = $customerRepo;
	}

    public function index()
    {
    	$userList = [''=>'--']+User::where('status',1)->orderBy('name')->pluck('name','username')->toArray();
    	$activityList = [''=>'--']+ActivityType::where('calendar','0')->where('deletestatus','0')->where('status','0')->orderBy('name')->pluck('name', 'id')->toArray();
    	$activity_types = ActivityType::orderBy('name')->get();

        $dateComponents = getdate();
        $month = $dateComponents['mon'];                
        $year = $dateComponents['year'];

        $calendar = $this->build_calendar($month,$year);
        $event = '';
        $viewtype = '';

        $cmonth = date('m');                
        $cyear = date('Y');
        $username = '';   
        $days = cal_days_in_month(CAL_GREGORIAN,$cmonth,$cyear);
    	return view('Client::crm.calendar.index', compact('userList','activityList','calendar','activity_types'));
    }

    private function build_calendar($month,$year,$dateArray='')
    {

    	$today_date = date("Y-m-j");
		// Create array containing abbreviations of days of week.
		$daysOfWeek = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
		// What is the first day of the month in question?
		$firstDayOfMonth = mktime(0,0,0,$month,1,$year);
		// How many days does this month contain?
		$numberDays = date('t',$firstDayOfMonth);
		// Retrieve some information about the first day of the
		// month in question.
		$dateComponents = getdate($firstDayOfMonth);
		// What is the name of the month in question?
		$monthName = $dateComponents['month'];
		// What is the index value (0-6) of the first day of the
		// month in question.
		$dayOfWeek = $dateComponents['wday'];
		// Create the table tag opener and day headers
		$calendar = "<table class='table table-bordered'>";
		$calendar .= "<caption>$monthName $year</caption>";
		$calendar .= "<tr>";

		// Create the calendar headers
		foreach($daysOfWeek as $day) {
			$calendar .= "<th class='header' style='width:150px;border:1px solid #000 !important'>$day</th>";
		} 

		// Create the rest of the calendar
		// Initiate the day counter, starting with the 1st.

		$currentDay = 1;
		$calendar .= "</tr><tr>";

		// The variable $dayOfWeek is used to
		// ensure that the calendar
		// display consists of exactly 7 columns.

		if ($dayOfWeek > 0) { 
			$calendar .= "<td colspan='$dayOfWeek' style='width:150px;border:1px solid #000 !important'>&nbsp;</td>"; 
		}

		$month = str_pad($month, 2, "0", STR_PAD_LEFT);
	  
		while ($currentDay <= $numberDays) {

			// Seventh column (Saturday) reached. Start a new row.

			if ($dayOfWeek == 7) {

				$dayOfWeek = 0;
				$calendar .= "</tr><tr>";

			}

			$currentDayRel = str_pad($currentDay, 2, "0", STR_PAD_LEFT);

			$date = "$year-$month-$currentDayRel";
			if($year.'-'.$month.'-'.$currentDay == $today_date){
				$calendar .= "<td class='day' rel='$date' style='width:150px;height:150px;border:1px solid #000 !important; font-size:14px;background:#d2d6de'>";
			} else {
				$calendar .= "<td class='day' rel='$date' style='width:150px;height:150px;border:1px solid #000 !important; font-size:14px'>";
			}
			$calendar .= $currentDay;
			$calendar .= $this->getCalendar($year.'-'.$month.'-'.$currentDay);
			$calendar .= "</td>";

			// Increment counters

			$currentDay++;
			$dayOfWeek++;

		}

		// Complete the row of the last week in month, if necessary

		if ($dayOfWeek != 7) { 

		$remainingDays = 7 - $dayOfWeek;
		$calendar .= "<td colspan='$remainingDays'>&nbsp;</td>"; 

		}
	     
		$calendar .= "</tr>";

		$calendar .= "</table>";

		return $calendar;

	}

	private function getCalendar($date)
    {
    	$eventdate = Carbon::parse($date)->format('Y-m-d');
    	list($year,$month,$day) = explode('-', $eventdate);
    	
    	$result = Event::with('user')->leftjoin('activity_types','activity_types.id','=','events.activity_id')
    				   ->leftjoin('customers','customers.id','=','events.customer_id')
    				   ->select('events.id as id','activity_types.name as activity','event_date','events.user_id','customers.name as company','events.details as remarks','attendee')
    				   ->whereRaw("date(event_date) = '".$eventdate."' and activity_id!='0'")
    				   ->orderBy('events.created_at','desc')
    				   ->get();

    	$leavelist = [
			'Vacation Leave',
			'Vacation Leave (Half)',
			'Sick Leave',
			'Sick Leave (Half)',
			'Offset',
			'Offset (Half)'
		];

    	$content = '';
    	unset($details);
    	$i = 0;
    	foreach ($result as $key => $value) {
    		$username = count($value->user) ? $value->user->username : '';
    		$details = '
    			<b>Company : </b> '.$value->company.'<br>
    			<b>Time : </b> '.Carbon::parse($value->event_date)->format('h:i A').'<br>
    			<b>Attendee : </b> '.$value->attendee_display.'<br>
    			<b>Remarks : </b> '.$value->remarks.'
    		';
    		if(in_array($value->remarks, $leavelist)){
    			$content .= '<br><a style="cursor:pointer" class="btn btn-flat btn-primary btn-xs" data-toggle="tooltip" data-placement="top" data-html="true" title="'.$value->remarks.'">';
    			$content .= 'Leave - '.$value->attendee_display;
    			$content .= '</a>';
    		} else {
    			if($value->user_id == Auth::guard('client')->user()->id){
    				$content .= '<br><a onclick="viewEvent('.$value->id.')" style="cursor:pointer" class="btn btn-flat btn-primary btn-xs" data-toggle="tooltip" data-placement="top" data-html="true" title="'.$details.'">';
    			} else {
    				$content .= '<br><a onclick="return false" style="cursor:pointer" class="btn btn-flat btn-primary btn-xs" data-toggle="tooltip" data-placement="top" data-html="true" title="'.$details.'">';
    			}
	    		$content .= Carbon::parse($value->event_date)->format('h:i A').' - '.$username;
	    		$content .= '</a>';
    		}
    	if (++$i == 3) break;
    	}
    	if(count($result)>3){
    		$content .= '<br><a class="btn btn-primary btn-flat btn-xs" href="'.url('client/crm/calendar/getEventDay',$date).'" data-toggle="modal" data-target="#vieweventModal">View More</a>';
    	}
    	return $content;
    }

    private function calendarList($cmonth,$cyear,$days,$user)
	{
		$calendar = '
		<div class="box box-primary">
			<div class="box-body">
				<table class="table table-hover">
					<thead>
						<th style="text-align:center; width:3%">Day</th>
						<th style="text-align:center; width:3%">Date</th>
						<th style="text-align:center; width:7%">Time</th>
						<th style="width: 15%">Company</th>
						<th style="width: 25%">Remarks</th>
						<th style="width: 15%">Attendee</th>
						<th style="text-align:center; width: 5%">Action</th>
					</thead>
					<tbody>';
						if($user == ''){
							$mydate = date("Y-m-d",mktime(0,0,0,$cmonth,'1',$cyear));
							for($day=1; $day<=$days; $day++){
								$mydate = date("Y-m-d",mktime(0,0,0,$cmonth,$day,$cyear));
								if(date("D",mktime(0,0,0,$cmonth,$day,$cyear)) == 'Sat'){
								$calendar .= '<tr class="bg-danger">';
								} else if(date("D",mktime(0,0,0,$cmonth,$day,$cyear)) == 'Sun'){
								$calendar .= '<tr class="bg-danger">';
								} else if(date("d")==$day && $cmonth==date('m') && $cyear==date('Y')) {
								$calendar .= '<tr class="bg-info">';
								} else {
								$calendar .= '<tr>';
								}
								$events = Event::selectRaw("name, events.customer_id, events.details, attendee, event_date, events.id, events.user_id")
												  ->join('customers','events.customer_id','=','customers.id')
												  ->whereRaw("date(event_date) = '".$mydate."'")
												  ->filterusercal(['username'=>$user])
												  ->orderBy('event_date')
												  ->get();
								if(count($events)){
									$time = ($events[0]['user_id'] == Auth::guard('client')->user()->id) ? '<a onclick="viewEvent('.$events[0]['id'].')" style="cursor:pointer">'.Carbon::parse($events[0]['event_date'])->format('h:i A').'</a>' : ''.Carbon::parse($events[0]['event_date'])->format('h:i A').'';
									$name = '<a href="'.url('client/crm/customer', hashid($events[0]['customer_id'])).'" target="_blank">'.$events[0]['name'].'</a>';
									$remarks = $events[0]['details'];
									$attendee = $events[0]['attendee_display'];
									$action = ($events[0]['user_id'] == Auth::guard('client')->user()->id) ? '<a href="javascript:void(0)" onclick="deleteevent(\''.hashid($events[0]['id']).'\')" class="btn btn-danger btn-xs"><i class="fa fa-trash fa-fw"></i> &nbsp;Delete</a>' : '';
								} else {
									$time = '';
									$name = '';
									$remarks = '';
									$attendee = '';
									$action = '';
								}

								$calendar .= '
									<td align="center">'.date("D",mktime(0,0,0,$cmonth,$day,$cyear)).'</td>
									<td align="center">'.$day.'</td>
									<td align="center">'.$time.'</td>
									<td>'.$name.'</td>
									<td>'.$remarks.'</td>
									<td>'.$attendee.'</td>
									<td class="text-center">'.$action.'</td>
								</tr>';
								for ($i=1; $i < count($events); $i++) { 
									if(count($events) && $events[$i]['event_date']!='00:00:00'){
										$time = ($events[$i]['user_id'] == Auth::guard('client')->user()->id) ? '<a onclick="viewEvent('.$events[$i]['id'].')" style="cursor:pointer">'.Carbon::parse($events[$i]['event_date'])->format('h:i A').'</a>' : ''.Carbon::parse($events[$i]['event_date'])->format('h:i A').'';
										$name = '<a href="'.url('client/crm/customer', hashid($events[$i]['customer_id'])).'" target="_blank">'.$events[$i]['name'].'</a>';
										$remarks = $events[$i]['details'];
										$attendee = $events[$i]['attendee_display'];
										$action = ($events[$i]['user_id'] == Auth::guard('client')->user()->id) ? '<a href="javascript:void(0)" onclick="deleteevent(\''.hashid($events[$i]['id']).'\')" class="btn btn-danger btn-xs"><i class="fa fa-trash fa-fw"></i> &nbsp;Delete</a>' : '';
									} else {
										$time = '';
										$name = '';
										$remarks = '';
										$attendee = '';
										$action = '';
									}
									$calendar .= '
										<td align="center"></td>
										<td align="center"></td>
										<td align="center">'.$time.'</td>
										<td>'.$name.'</td>
										<td>'.$remarks.'</td>
										<td>'.$attendee.'</td>
										<td class="text-center">'.$action.'</td>
									</tr>';
								}
							}
						} else {
							$mydate = date("Y-m-d",mktime(0,0,0,$cmonth,'1',$cyear));
							for($day=1; $day<=$days; $day++){
								$mydate = date("Y-m-d",mktime(0,0,0,$cmonth,$day,$cyear));
								if(date("D",mktime(0,0,0,$cmonth,$day,$cyear)) == 'Sat'){
								$calendar .= '<tr class="bg-danger">';
								} else if(date("D",mktime(0,0,0,$cmonth,$day,$cyear)) == 'Sun'){
								$calendar .= '<tr class="bg-danger">';
								} else if(date("d")==$day && $cmonth==date('m') && $cyear==date('Y')) {
								$calendar .= '<tr class="bg-info">';
								} else {
								$calendar .= '<tr>';
								}
								$events = Event::selectRaw("name, events.customer_id, events.details, attendee, event_date, events.id, events.user_id")
												  ->join('customers','events.customer_id','=','customers.id')
												  ->whereRaw("date(event_date) = '".$mydate."'")
												  ->filterusercal(['username'=>$user])
												  ->orderBy('event_date')
												  ->get();
								if(count($events)){
									for ($i=0; $i < count($events); $i++) { 
										$time = ($events[$i]['user_id'] == Auth::guard('client')->user()->id) ? '<a onclick="viewEvent('.$events[$i]['id'].')" style="cursor:pointer">'.Carbon::parse($events[$i]['event_date'])->format('h:i A').'</a>' : ''.Carbon::parse($events[$i]['event_date'])->format('h:i A').'';
										$name = '<a href="'.url('client/crm/customer', hashid($events[$i]['customer_id'])).'" target="_blank">'.$events[$i]['name'].'</a>';
										$remarks = $events[$i]['details'];
										$attendee = $events[$i]['attendee_display'];
										$action = ($events[$i]['user_id'] == Auth::guard('client')->user()->id) ? '<a href="javascript:void(0)" onclick="deleteevent(\''.hashid($events[$i]['id']).'\')" class="btn btn-danger btn-xs"><i class="fa fa-trash fa-fw"></i> &nbsp;Delete</a>' : '';

										$calendar .= '
											<td align="center">'.date("D",mktime(0,0,0,$cmonth,$day,$cyear)).'</td>
											<td align="center">'.$day.'</td>
											<td align="center">'.$time.'</td>
											<td>'.$name.'</td>
											<td>'.$remarks.'</td>
											<td>'.$attendee.'</td>
											<td class="text-center">'.$action.'</td>
										</tr>';
									}
								}
							}
						}
					$calendar .= '</tbody>
				</table>
			</div>
		</div>';

		return $calendar;
	}

	public function store(Request $request)
	{
		$event_date = Carbon::parse($request['date'])->format('Y-m-d').' '.Carbon::parse($request['time'])->format('H:i:00');
		$attendees = '';
		if($request['user_ids']){
			$users = User::select('id','username')->where('status',1)->whereRaw("id in (".$request['user_ids'].")")->get();
			foreach ($users as $user) {
				$attendees .= $user->username.',';
			}
		}
		$attendee = ($attendees) ? substr($attendees, 0, -1) : 'All QX Employee';
		$calendarattendee = ($request['user_ids']) ? $request['user_ids'] : '';
		$makeRequest = [
			'user_id' => Auth::guard('client')->user()->id,
			'activity_id' => $request['activity_id'],
			'customer_id' => $request['customer_id'],
			'event_date' => $event_date,
			'details' => $request['details'],
			'attendee' => $attendee,
			'fyi' => $request['fyi']
		];

		if($request['event_id']){
			$this->eventRepo->update($makeRequest, $request['event_id']);
			return 1;
		}

		$calendar_id = $this->eventRepo->create($makeRequest);
		$calendar = $this->eventRepo->findWith($calendar_id, ['customer','user','activity']);
		$customername = count($calendar->customer) ? $calendar->customer->name : '';
		$activityname = count($calendar->activity) ? $calendar->activity->name : '';
		$sqlfyi = ($calendar->fyi) ? $calendarattendee.','.$calendar->fyi : $calendarattendee;
		$fyi = '';
        $fyi_email = '';
        if($sqlfyi){
            $resultfyis = DB::select("select id,email,name from ".env('DB_DATABASE').".client_users where id in (".$sqlfyi.")");;
            foreach ($resultfyis as $resultfyi) {
                $fyi .= $resultfyi->name.', ';
                $fyi_email .= $resultfyi->email.',';
            }

            $details = '
	        <p>
	        <b>Customer : </b> '.$customername.'<br>
	        <b>Activity : </b> '.$activityname.'<br>
	        <b>Date and Time : </b> '.$calendar->event_date_display.'<br>
	        <b>Attendee : </b> '.$calendar->attendee_display.'<br><br>
	        <b>Remarks : </b><br>'.$calendar->details.'<br><br>
	        <b>From: </b>'.Auth::user()->name.'
	        </p>
	        ';
        }
        
        try {
            DB::connection('live-mysql')->getPdo();
        } catch (\Exception $e) {
            return ['result' => 1, 'customer_id' => $request['customer_id'], 'message' => 'Unfortunately, email could not be sent due to internet connection.'];
        }

        if($sqlfyi){
            $fyi_email = explode(',', $fyi_email);
            $ccemails = array_filter($fyi_email);

            foreach ($ccemails as $key => $value) {
                DB::connection('live-mysql')->insert("insert into email_logs (email,subject,details,created_at,sender) values ('".$value."','CRM Invitation Event','".addslashes($details)."','".Carbon::now()."','".Auth::user()->email."')");
            }
        }

		return 1;
	}

	public function getEvent($id)
    {
        $event = $this->eventRepo->find($id);
        $customer = $this->customerRepo->find($event->customer_id);
        $attendee = explode(',', $event->attendee);
        $fyi = explode(',', $event->fyi);
        $users = User::select('id','name')->whereIn('username', str_replace(" ", "", $attendee))->get();
        $fyis = User::select('id','name')->whereIn('id', $fyi)->get();

        $event = array_add($event, 'customername', $customer->name);
        $event = array_add($event, 'users', $users);
        return $event = array_add($event, 'fyis', $fyis);
    }

    public function changeDate(Request $request)
    {
        $cmonth = $request['month'];                
        $cyear = $request['year'];
        $username = $request['username'];  
        $viewtype = $request['viewtype'];  
        $userList = [''=>'--']+User::where('status',1)->orderBy('name')->pluck('name','username')->toArray();
    	$activityList = [''=>'--']+ActivityType::where('calendar','0')->where('deletestatus','0')->where('status','0')->orderBy('name')->pluck('name', 'id')->toArray();
    	$activity_types = ActivityType::where('calendar','0')->where('deletestatus','0')->where('status','0')->orderBy('name')->get();

        if($username || $viewtype == 2){
            $events = Event::leftjoin('customers','customers.id','=','events.customer_id')
                                  ->select('customers.name','event_date','events.details','events.attendee','events.id')
                                  ->filterevent(['username'=>$username])
                                  ->whereRaw('month(event_date) = '.$cmonth.'')
                                  ->whereRaw('year(event_date) = '.$cyear.'')
                                  ->orderBy('event_date')
                                  ->get();

            $dateComponents = getdate();

            $calendar = $this->build_calendar($cmonth,$cyear);
            $days = cal_days_in_month(CAL_GREGORIAN,$cmonth,$cyear);
            $calendar = $this->calendarList($cmonth,$cyear,$days,$username);
            return view('Client::crm.calendar.index', compact('calendar','activityList','cmonth','cyear','userList','username','viewtype','activity_types'));
        } else {
            $dateComponents = getdate();

            $calendar = $this->build_calendar($cmonth,$cyear);
            $days = cal_days_in_month(CAL_GREGORIAN,$cmonth,$cyear);

            return view('Client::crm.calendar.index', compact('calendar','activityList','cmonth','cyear','userList','username','days','event','viewtype','activity_types'));
        }
    }

    public function destroy($id)
    {
    	$id = decode($id);
    	$this->eventRepo->delete($id);
    	return 1;
    }

    public function getEventDay($date)
    {
        $events = Event::leftjoin('customers','customers.id','=','events.customer_id')
                              ->select('events.*','customers.name as company')
                              ->whereRaw("date(event_date) = '".$date."'")->get();
        $eventdate = Carbon::parse($date)->format('d F Y');

        return view('Client::crm.calendar.eventlist', compact('events','eventdate'));
    }
}
