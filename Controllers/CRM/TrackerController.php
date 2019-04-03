<?php

namespace QxCMS\Modules\Client\Controllers\CRM;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use QxCMS\Http\Controllers\Controller;
use QxCMS\Modules\Client\Models\CRM\Service;
use QxCMS\Modules\Client\Repositories\CRM\Customer\CustomerRepositoryEloquent as Customer;
use QxCMS\Modules\Client\Repositories\CRM\Customer\PaymentRepositoryEloquent as Payment;
use QxCMS\Modules\Client\Repositories\CRM\Tracker\TrackerRepositoryEloquent as Tracker;

class TrackerController extends Controller
{
    protected $trackerRepo;
    protected $customerRepo;
    protected $paymentRepo;

    public function __construct(Tracker $trackerRepo, Customer $customerRepo, Payment $paymentRepo)
    {
    	$this->trackerRepo = $trackerRepo;
    	$this->customerRepo = $customerRepo;
        $this->paymentRepo = $paymentRepo;
    }

    public function index()
    {
    	$from = date('Y', strtotime("-5 year"));
    	$to = date('Y', strtotime("+2 year"));
    	$year = date('Y');
    	$sql = "tracker_status = '1'";
    	$customers = $this->customerRepo->rawAll($sql, 'name');
    	$serviceList = [''=>'--']+Service::orderBy('name')->pluck('name', 'id')->toArray();
    	$services = Service::orderBy('name')->get();
    	return view('Client::crm.tracker.index', compact('from','to','year','customers','serviceList','services'));
    }

    public function addcustomer(Request $request)
    {
    	$customer_id = $request['customer_id'];
    	$this->customerRepo->update(['tracker_status' => '1'], $customer_id);
    	return 1;
    }

    public function store(Request $request)
    {
    	// return $request->all();
    	if($request['date_type'] == 1){
            $from = Carbon::parse($request['from_date'])->format('Y-m-d');
            $to = Carbon::parse($request['to_date'])->format('Y-m-d');
        } else {
            list($fromMonth, $fromYear) = explode("/",$request['from_date']);
            list($toMonth, $toYear) = explode("/",$request['to_date']);
            $from = lastOfMonth($fromMonth,$fromYear);
            $to = lastOfMonth($toMonth,$toYear);
        }

        $mon = get_months($from,$to);
        $random_id = str_random(5);

        $postyear = (Carbon::parse($request['from_date'])->format('Y')!=Carbon::parse($request['to_date'])->format('Y')) ? ' - '.Carbon::parse($request['to_date'])->format('F Y') : '';

        // insert to payment
        $paymentRequest = [
            'user_id' => Auth::guard('client')->user()->id,
            'customer_id' => $request['customer_id'],
            'service_id' => $request['service_id'],
            'title' => 'Payment Tracker '.Carbon::parse($request['from_date'])->format('Y').$postyear,
            'date_added' => Carbon::now()->format('Y-m-d'),
            'status' => 'Paid',
            'details' => $request['remarks']
        ];

        $payment = $this->paymentRepo->create($paymentRequest);

        foreach ($mon as $key => $value) {
        	list($mymonth, $myyear) = explode("-",$value);
            if($request['date_type'] == 1){
            	if(Carbon::parse($request['from_date'])->format('d') > '30'){
	            	if(in_array($mymonth,[2,4,6,9,11])){
	            		$date = lastOfMonth($mymonth,$myyear);
	            	} else {
	            		$date = $myyear."-".$mymonth."-".Carbon::parse($request['from_date'])->format('d');
	            	}
	            } else if($mymonth == '2' && Carbon::parse($request['from_date'])->format('d') > '28'){
            		$date = lastOfMonth($mymonth,$myyear);
            	} else {
	            	$date = $myyear."-".$mymonth."-".Carbon::parse($request['from_date'])->format('d');
	            }
            } else {
                $date = lastOfMonth($mymonth,$myyear);
            }

            $chk = DB::connection('client')->table('payment_trackers')
                                           ->where('customer_id', $request['customer_id'])
                                           ->where('month', $mymonth)
                                           ->where('year', $myyear)
                                           ->count();
            if($chk != '0'){
                DB::connection('client')->table('payment_trackers')
                                        ->where('customer_id', $request['customer_id'])
                                        ->where('month', $mymonth)
                                        ->where('year', $myyear)
                                        ->delete();
            }

            $makeRequest = [
            	'customer_id' => $request['customer_id'],
            	'date_type' => $request['date_type'],
            	'payment_date' => $date,
            	'service_id' => $request['service_id'],
            	'amount' => $request['amount'],
            	'remarks' => $request['remarks'],
            	'status' => '1',
            	'group_id' => $random_id,
            	'month' => $mymonth,
            	'year' => $myyear,
            	'created_at' => Carbon::now(), 
            	'updated_at' => Carbon::now()
            ];

            $tracker_id = DB::connection('client')->table('payment_trackers')->insertGetId($makeRequest);

            $orRequest = [
                'payment_id' => $payment->id,
                'due_date' => $date,
                'date_paid' => $date,
                'date_bill' => Carbon::now()->format('Y-m-d'),
                'amount' => $request['amount'],
                'tracker_id' => $tracker_id,
                'customer_id' => $request['customer_id'],
                'remarks' => $request['remarks'],
                'created_at' => Carbon::now(), 
                'updated_at' => Carbon::now()
            ];

            DB::connection('client')->table('payments_or')->insert($orRequest);
        }

        $customer_id = $request['customer_id'];
        $this->customerRepo->update(['tracker_status' => '1'], $customer_id);

        return 1;
    }

    public function edit($id)
    {
    	return $this->trackerRepo->find($id);
    }

    public function changeyear(Request $request)
    {
        $from = date('Y', strtotime("-5 year"));
        $to = date('Y', strtotime("+2 year"));
        $year = $request['year'];
        $sql = "tracker_status = '1'";
        $customers = $this->customerRepo->rawAll($sql, 'name');
        $serviceList = [''=>'--']+Service::orderBy('name')->pluck('name', 'id')->toArray();
        $services = Service::orderBy('name')->get();
        return view('Client::crm.tracker.index', compact('from','to','year','customers','serviceList','services'));
    }

    public function refreshcache($id)
    {
        $customer = $this->customerRepo->find($id);
        $page_content = get_content($customer->usage_link);
        if(count(explode("QXQXQX",$page_content)) < 3){
            return 0;
        }
        list($usage,$applicants,$last_login) = explode("QXQXQX",$page_content);
        $makeRequest = [
            'usage' => $usage,
            'applicants' => $applicants,
            'last_login' => $last_login
        ];
        $this->customerRepo->update($makeRequest, $id);
        return 1;
    }

    public function update(Request $request)
    {
        $id = $request['id'];
        $makeRequest = [
            'amount' => $request['amount'],
            'remarks' => $request['remarks']
        ];

        $this->trackerRepo->update($makeRequest, $id);
        return 1;
    }
}
