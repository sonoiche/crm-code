<?php

namespace QxCMS\Modules\Client\Controllers\CRM\Reports;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use QxCMS\Http\Controllers\Controller;
use QxCMS\Modules\Client\Repositories\CRM\Customer\ActivityRepositoryEloquent as Activity;
use QxCMS\Modules\Client\Models\CRM\Activity as Activitys;

class ActivityController extends Controller
{
    protected $activityRepo;

    public function __construct(Activity $activityRepo)
    {
    	$this->activityRepo = $activityRepo;
    }

    public function index()
    {
    	return view('Client::crm.reports.activity.index');
    }

    public function store(Request $request)
    {
    	$added_date_range = $request['date_added'];
        $added_date = explode('-', $added_date_range);
        $added_from = Carbon::parse($added_date[0])->format('Y-m-d');
        $added_to = Carbon::parse($added_date[1])->format('Y-m-d');
        $from_display = Carbon::parse($added_date[0])->format('d F Y');
        $to_display = Carbon::parse($added_date[1])->format('d F Y');

        $sqlduedate = '';
        $sqlcustomer = '';
        $sqltype = '';
        $sqluser = '';
        if($request['date_added']){
	        $date_added_range = $request['date_added'];
	        $date_added = explode('-', $date_added_range);
	        $due_from = Carbon::parse($date_added[0])->format('Y-m-d');
	        $due_to = Carbon::parse($date_added[1])->format('Y-m-d');
	        $sqlduedate = "and date_added >= '".$due_from."' and date_added <= '".$due_to."' and activities.deletestatus = '0' and activities.customer_id!='0'";
	    } else {
	    	$due_from = '0x0';
	    	$due_to = '0x0';
	    }

	    if($request['customer_id']){
	    	$customer_id = $request['customer_id'];
	    	$sqlcustomer = "and activities.customer_id = '".$customer_id."'";
	    } else {
	    	$customer_id = '0x0';
	    }

	    if($request['activity_type']){
	    	$activity_type = $request['activity_type'];
	    	$sqltype = "and activities.activity_type in ('".$activity_type."')";
	    } else {
	    	$activity_type = '0x0';
	    }

	    if($request['user_id']){
	    	$user_id = $request['user_id'];
	    	$sqluser = "and activities.assign_to = '".$user_id."'";
	    } else {
	    	$user_id = '0x0';
	    }

	    $sql = "activities.deletestatus = '0' and activities.customer_id!='0' $sqlduedate $sqlcustomer $sqltype $sqluser";
	    // $activity = $this->activityRepo->rawWith(['assign','customer','activitytype'], $sql, 'created_at');
	    $activity = Activitys::leftJoin(env('DB_DATABASE').'.client_users','assign_to','=',env('DB_DATABASE').'.client_users.id')
	    					  ->leftJoin('customers','customers.id','=','customer_id')
	    					  ->leftJoin('activity_types','activity_types.id','=','activity_type')
	    					  ->select('activities.id','activities.due_date','activities.attach_file','activities.date_added','customers.name','activity_types.name as activityname',env('DB_DATABASE').'.client_users.name as username','activities.remarks')
	    					  ->whereRaw($sql)
	    					  ->orderBy('activities.date_added')
	    					  ->get();
	    $link = 'activity/'.$added_from.'xxx'.$added_to.'xxx'.$due_from.'xxx'.$due_to.'xxx'.$customer_id.'xxx'.$activity_type.'xxx'.$user_id;

    	return ['activity' => $activity, 'from_display' => $from_display, 'to_display' => $to_display, 'link' => $link, 'totalcount' => count($activity)];
    }

    public function show($datas)
    {
    	$data = explode('xxx', $datas);
        $added_from = Carbon::parse($data[0])->format('Y-m-d');
        $added_to = Carbon::parse($data[1])->format('Y-m-d');
        $from_display = Carbon::parse($data[0])->format('d F Y');
        $to_display = Carbon::parse($data[1])->format('d F Y');

        $sqlduedate = '';
        $sqlcustomer = '';
        $sqltype = '';
        $sqluser = '';
        if($data[2] != '0x0' && $data[3] != '0x0'){
	        $due_from = Carbon::parse($data[2])->format('Y-m-d');
	        $due_to = Carbon::parse($data[3])->format('Y-m-d');
	        $sqlduedate = "and activities.date_added >= '".$due_from."' and date_added <= '".$due_to."'";
	    }

	    if($data[4] != '0x0'){
	    	$customer_id = $data[4];
	    	$sqlcustomer = "and activities.customer_id = '".$customer_id."'";
	    }

	    if($data[5] != '0x0'){
	    	$activity_type = $data[5];
	    	$sqltype = "and activities.activity_type in ('".$activity_type."')";
	    }

	    if($data[6] != '0x0'){
	    	$user_id = $data[6];
	    	$sqluser = "and activities.assign_to = '".$user_id."'";
	    }

	    if(isset($data[8]) && $data[8] == 'ascending'){
            $sort = $data[9];
            $sortorder = 'asc';
        } else if(isset($data[8]) && $data[8] == 'descending'){
            $sort = $data[9];
            $sortorder = 'desc';
        } else {
            $sort = 'date_added';
            $sortorder = 'asc';
        }

	    $sql = "activities.deletestatus = '0' and activities.customer_id!='0' $sqlduedate $sqlcustomer $sqltype $sqluser";
	    // $activity = $this->activityRepo->rawWith(['user','customer','activitytype'], $sql, 'created_at');
	    $activity = Activitys::leftJoin(env('DB_DATABASE').'.client_users','assign_to','=',env('DB_DATABASE').'.client_users.id')
	    					  ->leftJoin('customers','customers.id','=','customer_id')
	    					  ->leftJoin('activity_types','activity_types.id','=','activity_type')
	    					  ->select('activities.id','activities.due_date','activities.attach_file','activities.date_added','customers.name','activity_types.name as activityname',env('DB_DATABASE').'.client_users.name as username','activities.remarks')
	    					  ->whereRaw($sql)
	    					  ->orderBy($sort,$sortorder)
	    					  ->get();

	    if($data[7] == 'csv'){
	    	Excel::create('Activity Report '.$from_display.' - '.$to_display, function($excel) use ($activity, $from_display, $to_display) {
	            $excel->sheet('New sheet', function($sheet) use ($activity, $from_display, $to_display) {
	                $sheet->loadView('Client::crm.reports.activity.excel', compact('activity','from_display','to_display'));
	            });
	        })->download('csv');
	    }

	    if($data[7] == 'pdf'){
	    	Excel::create('Activity Report '.$from_display.' - '.$to_display, function($excel) use ($activity, $from_display, $to_display) {
	            $excel->sheet('New sheet', function($sheet) use ($activity, $from_display, $to_display) {
	                $sheet->setOrientation('landscape')
	                	  ->setAllBorders('thin')
	                	  ->setWidth(array(
						    'A'  =>  5,
						    'B'  =>  10,
						    'C'  =>  15,
						    'D'  =>  10,
						    'E'  =>  10,
						    'F'  =>  25
						  ))->loadView('Client::crm.reports.activity.excel', compact('activity','from_display','to_display'));
	            });
	        })->download('pdf');
	    }
    }
}
