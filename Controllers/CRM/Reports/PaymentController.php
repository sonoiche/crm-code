<?php

namespace QxCMS\Modules\Client\Controllers\CRM\Reports;

use Carbon\Carbon;
use Illuminate\Http\Request;
use QxCMS\Http\Controllers\Controller;
use QxCMS\Modules\Client\Models\CRM\PaymentOR as Payment;
use Maatwebsite\Excel\Facades\Excel;

class PaymentController extends Controller
{
    protected $paymentRepo;

    public function __construct(Payment $paymentRepo)
    {
    	$this->paymentRepo = $paymentRepo;
    }

    public function index()
    {
    	return view('Client::crm.reports.payment.index');
    }

    public function store(Request $request)
    {
    	$added_date_range = $request['date_added'];
        $added_date = explode('-', $added_date_range);
        $added_from = Carbon::parse($added_date[0])->format('Y-m-d');
        $added_to = Carbon::parse($added_date[1])->format('Y-m-d');
        $from_display = Carbon::parse($added_date[0])->format('d F Y');
        $to_display = Carbon::parse($added_date[1])->format('d F Y');

        $sql = "date_paid >= '".$added_from."' and date_paid <= '".$added_to."'";
	    // $payments = $this->paymentRepo->with(['payment','payment.user','payment.customer','payment.service'])->whereRaw($sql)->orderBy('date_paid')->get();
	    $payments = Payment::leftJoin('payments','payments.id','=','payments_or.payment_id')
	    				   ->leftJoin(env('DB_DATABASE').'.client_users','payments.user_id','=',env('DB_DATABASE').'.client_users.id')
	    				   ->leftJoin('activity_types','payments.service_id','=','activity_types.id')
	    				   ->leftJoin('customers','payments.customer_id','=','customers.id')
	    				   ->select('payments.date_added','payments_or.pr_number','payments_or.or_number','customers.name','customers.tin_number','payments.id','payments.details','activity_types.name as activityname','client_users.name as username','payments_or.date_bill','payments_or.date_paid','payments_or.due_date','payments_or.amount','customers.tin_number','customers.id as customer_id','payments_or.id as or_id')
	    				   ->whereRaw($sql." and payments.deletestatus = '0'")
	    				   ->orderBy('payments_or.date_paid','desc')
	    				   ->get();
	    $pay = $this->paymentRepo->selectRaw("SUM(amount) as total, date_bill, date_paid, due_date, amount")->whereRaw($sql." and payment_id in (select id from payments where deletestatus = '0')")->first();
	    $pr_number = ($request['pr_number']) ? $request['pr_number'] : '0';
	    $or_number = ($request['or_number']) ? $request['or_number'] : '0';
	    $link = 'payment/'.$added_from.'xxx'.$added_to.'xxx'.$pr_number.'xxx'.$or_number;
	    return ['payments' => $payments, 'from_display' => $from_display, 'to_display' => $to_display, 'link' => $link, 'totalamount' => number_format($pay->total,2), 'totalcount' => count($payments), 'pr_number' => $pr_number, 'or_number' => $or_number];
    }

    public function show($datas)
    {
    	$data = explode('xxx', $datas);
        $added_from = Carbon::parse($data[0])->format('Y-m-d');
        $added_to = Carbon::parse($data[1])->format('Y-m-d');
        $from_display = Carbon::parse($data[0])->format('d F Y');
        $to_display = Carbon::parse($data[1])->format('d F Y');

        $pr_number = $data[2];
        $or_number = $data[3];

        if(isset($data[5]) && $data[5] == 'ascending'){
            $sort = $data[6];
            $sortorder = 'asc';
        } else if(isset($data[5]) && $data[5] == 'descending'){
            $sort = $data[6];
            $sortorder = 'desc';
        } else {
            $sort = 'payments_or.date_paid';
            $sortorder = 'asc';
        }

        $sql = "date_paid >= '".$added_from."' and date_paid <= '".$added_to."'";
	    // $payments = $this->paymentRepo->with(['payment','payment.user','payment.customer','payment.service'])->whereRaw($sql)->orderBy('date_paid')->get();
	    $payments = Payment::leftJoin('payments','payments.id','=','payments_or.payment_id')
	    				   ->leftJoin(env('DB_DATABASE').'.client_users','payments.user_id','=',env('DB_DATABASE').'.client_users.id')
	    				   ->leftJoin('activity_types','payments.service_id','=','activity_types.id')
	    				   ->leftJoin('customers','payments.customer_id','=','customers.id')
	    				   ->select('payments.date_added','payments_or.pr_number','payments_or.or_number','customers.name','customers.tin_number','payments.id','payments.details','activity_types.name as activityname','client_users.name as username','payments_or.date_bill','payments_or.date_paid','payments_or.due_date','payments_or.amount','customers.address')
	    				   ->whereRaw($sql." and payments.deletestatus = '0'")
	    				   ->orderBy($sort, $sortorder)
	    				   ->get();
	    $pay = $this->paymentRepo->selectRaw("SUM(amount) as total, date_bill, date_paid, due_date, amount")->whereRaw($sql." and payment_id in (select id from payments where deletestatus = '0')")->first();

        if($data[4] == 'csv'){
	    	Excel::create('Payment Report '.$from_display.' - '.$to_display, function($excel) use ($payments, $pay, $from_display, $to_display, $pr_number, $or_number) {
	            $excel->sheet('New sheet', function($sheet) use ($payments, $pay, $from_display, $to_display, $pr_number, $or_number) {
	                $sheet->loadView('Client::crm.reports.payment.excel', compact('payments','from_display','to_display','pay','pr_number','or_number'));
	            });
	        })->download('csv');
	    }

	    if($data[4] == 'pdf'){
	    	Excel::create('Payment Report '.$from_display.' - '.$to_display, function($excel) use ($payments, $pay, $from_display, $to_display, $pr_number, $or_number) {
	            $excel->sheet('New sheet', function($sheet) use ($payments, $pay, $from_display, $to_display, $pr_number, $or_number) {
	                $sheet->setOrientation('landscape')
	                	  ->setAllBorders('thin')
	                	  ->setWidth(array(
						    'A'  =>  5,
						    'B'  =>  10,
						    'C'  =>  10,
						    'D'  =>  15,
						    'E'  =>  10,
						    'F'  =>  10,
						    'G'  =>  20,
						    'H'  =>  10,
						    'I'  =>  10
						  ))->loadView('Client::crm.reports.payment.excel', compact('payments','from_display','to_display','pay','pr_number','or_number'));
	            });
	        })->download('pdf');
	    }
    }
}
