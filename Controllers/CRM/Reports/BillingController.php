<?php

namespace QxCMS\Modules\Client\Controllers\CRM\Reports;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use QxCMS\Http\Controllers\Controller;
use QxCMS\Modules\Client\Repositories\CRM\Customer\PaymentRepositoryEloquent as Payment;
use QxCMS\Modules\Client\Models\CRM\Payment as Payments;

class BillingController extends Controller
{
	protected $paymentRepo;

	public function __construct(Payment $paymentRepo)
    {
    	$this->paymentRepo = $paymentRepo;
    }

    public function index()
    {
    	$statusList = [''=>'All','Unpaid'=>'Unpaid','Paid'=>'Paid','Cancelled'=>'Cancelled'];
    	return view('Client::crm.reports.billing.index', compact('statusList'));
    }

    public function store(Request $request)
    {
    	$added_date_range = $request['date_added'];
        $added_date = explode('-', $added_date_range);
        $added_from = Carbon::parse($added_date[0])->format('Y-m-d');
        $added_to = Carbon::parse($added_date[1])->format('Y-m-d');
        $from_display = Carbon::parse($added_date[0])->format('d F Y');
        $to_display = Carbon::parse($added_date[1])->format('d F Y');

        $sqlcustomer = '';
        $sqlstatus = '';
        if($request['customer_id']){
	    	$customer_id = $request['customer_id'];
	    	$sqlcustomer = "and payments.customer_id in (".$customer_id.")";
	    } else {
	    	$customer_id = '0x0';
	    }

	    if($request['status']){
	    	$status = $request['status'];
	    	$sqlstatus = "and payments.status = '".$status."'";
	    } else {
	    	$status = '0x0';
	    }

	    $sql = "date(date_added) >= '".$added_from."' and date(date_added) <= '".$added_to."' and payments.deletestatus = '0' $sqlstatus $sqlcustomer";
	    // $billings = $this->paymentRepo->rawWith(['paymentor','user','customer'], $sql, 'date_added');
	    $billings = Payments::leftJoin('payments_or','payments_or.payment_id','=','payments.id')
	    					  ->leftJoin(env('DB_DATABASE').'.client_users',env('DB_DATABASE').'.client_users.id','=','payments.user_id')
	    					  ->leftJoin('customers','customers.id','=','payments.customer_id')
	    					  ->select('customers.name as name','payments.details','payments.id','payments.status','payments_or.or_number','payments.date_added',env('DB_DATABASE').'.client_users.name as username','payments.attachment')
	    					  ->whereRaw($sql)
	    					  ->orderBy('date_added','desc')
	    					  ->groupBy('payments_or.payment_id')
	    					  ->get();
	    $link = 'billing/'.$added_from.'xxx'.$added_to.'xxx'.$customer_id.'xxx'.$status;
	    return ['billings' => $billings, 'from_display' => $from_display, 'to_display' => $to_display, 'link' => $link, 'totalcount' => count($billings)];
    }

    public function show($datas)
    {
    	$data = explode('xxx', $datas);
        $added_from = Carbon::parse($data[0])->format('Y-m-d');
        $added_to = Carbon::parse($data[1])->format('Y-m-d');
        $from_display = Carbon::parse($data[0])->format('d F Y');
        $to_display = Carbon::parse($data[1])->format('d F Y');

        $sqlcustomer = '';
        $sqlstatus = '';
        if($data[2] != '0x0'){
	    	$customer_id = $data[2];
	    	$sqlcustomer = "and payments.customer_id in (".$customer_id.")";
	    }

	    if($data[3] != '0x0'){
	    	$status = $data[3];
	    	$sqlstatus = "and payments.status = '".$status."'";
	    }

	    if(isset($data[5]) && $data[5] == 'ascending'){
            $sort = $data[6];
            $sortorder = 'asc';
        } else if(isset($data[5]) && $data[5] == 'descending'){
            $sort = $data[6];
            $sortorder = 'desc';
        } else {
            $sort = 'date_added';
            $sortorder = 'desc';
        }

	    $sql = "date(date_added) >= '".$added_from."' and date(date_added) <= '".$added_to."' and payments.deletestatus = '0' $sqlstatus $sqlcustomer";
	    // $billings = $this->paymentRepo->rawWith(['paymentor','user','customer'], $sql, $sort, $sortorder);
	    $billings = Payments::leftJoin('payments_or','payments_or.payment_id','=','payments.id')
	    					  ->leftJoin(env('DB_DATABASE').'.client_users',env('DB_DATABASE').'.client_users.id','=','payments.user_id')
	    					  ->leftJoin('customers','customers.id','=','payments.customer_id')
	    					  ->select('customers.name as name','payments.details','payments.id','payments.status','payments_or.or_number','payments.date_added',env('DB_DATABASE').'.client_users.name as username','payments.attachment')
	    					  ->whereRaw($sql)
	    					  ->orderBy($sort, $sortorder)
	    					  ->get();

	    if($data[4] == 'csv'){
	    	Excel::create('Billing Report '.$from_display.' - '.$to_display, function($excel) use ($billings, $from_display, $to_display) {
	            $excel->sheet('New sheet', function($sheet) use ($billings, $from_display, $to_display) {
	                $sheet->loadView('Client::crm.reports.billing.excel', compact('billings','from_display','to_display'));
	            });
	        })->download('csv');
	    }

	    if($data[4] == 'pdf'){
	    	Excel::create('Activity Report '.$from_display.' - '.$to_display, function($excel) use ($billings, $from_display, $to_display) {
	            $excel->sheet('New sheet', function($sheet) use ($billings, $from_display, $to_display) {
	                $sheet->setOrientation('landscape')
	                	  ->setAllBorders('thin')
	                	  ->setWidth(array(
						    'A'  =>  5,
						    'B'  =>  15,
						    'C'  =>  20,
						    'D'  =>  10,
						    'E'  =>  10,
						    'F'  =>  10,
						    'G'  =>  10,
						    'H'  =>  10
						  ))->loadView('Client::crm.reports.billing.excel', compact('billings','from_display','to_display'));
	            });
	        })->download('pdf');
	    }
    }
}
