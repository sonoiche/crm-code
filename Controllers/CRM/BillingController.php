<?php

namespace QxCMS\Modules\Client\Controllers\CRM;
ini_set('max_execution_time', 180);

use Datatables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use QxCMS\Http\Controllers\Controller;
use QxCMS\Modules\Client\Models\CRM\ActivityType;
use QxCMS\Modules\Client\Models\CRM\Salutation;
use QxCMS\Modules\Client\Models\CRM\Service;
use QxCMS\Modules\Client\Repositories\CRM\Customer\ActivityRepositoryEloquent as Activity;
use QxCMS\Modules\Client\Repositories\CRM\Customer\CustomerRepositoryEloquent as Customer;
use QxCMS\Modules\Client\Repositories\CRM\Customer\RecurringRepositoryEloquent as Recurring;

class BillingController extends Controller
{
	protected $customerRepo;
	protected $activityRepo;
	protected $recurringRepo;

	public function __construct(Customer $customerRepo, Activity $activityRepo, Recurring $recurringRepo)
	{
		$this->customerRepo = $customerRepo;
		$this->activityRepo = $activityRepo;
		$this->recurringRepo = $recurringRepo;
	}

    public function index($id)
    {
    	$id = decode($id);
    	$sql = "customer_id = '".$id."' and deletestatus = '0'";
        $sqlarch = "customer_id = '".$id."' and deletestatus = '1'";
    	$customer = $this->customerRepo->find($id);
    	// $serviceList = [''=>'--']+ActivityType::whereRaw("id in (76,75,74,77)")->orderBy('name')->pluck('name', 'id')->toArray();
    	$frequencyList = ['Annual' => 'Annual', 'Semi-Annual' => 'Semi-Annual', 'Quarterly' => 'Quarterly', 'Monthly' => 'Monthly'];
    	$services = ActivityType::where('recurring','0')->where('deletestatus','0')->where('status','0')->orderBy('name')->get();
    	$billings = $this->recurringRepo->rawAll($sql,'created_at','desc');
        $archives = $this->recurringRepo->rawAll($sqlarch,'updated_at','desc');

        $salutationList = [''=>'--']+Salutation::orderBy('name')->pluck('name', 'name')->toArray();
        $activityList = [''=>'--']+ActivityType::where('deletestatus','0')->where('status','0')->orderBy('name')->pluck('name', 'id')->toArray();
        $serviceList = [''=>'--']+ActivityType::where('recurring','0')->where('deletestatus','0')->where('status','0')->orderBy('name')->pluck('name', 'id')->toArray();
        $activity_types = ActivityType::where('deletestatus','0')->where('status','0')->orderBy('name')->get();
    	return view('Client::crm.billing.index', compact('customer','serviceList','services','frequencyList','billings','activityList','serviceList','activity_types','archives','salutationList'));
    }

    public function store(Request $request)
    {
    	$customer_id = decode($request['customer_id']);
    	$anniversary = explode('/', $request['anniversary']);
        $makeRequest = [
    		'user_id' => Auth::guard('client')->user()->id,
    		'customer_id' => $customer_id,
    		'service_id' => $request['service_id'],
    		'amount' => str_replace([',',' '], ['',''], $request['amount']),
    		'frequency' => $request['frequency'],
    		'anniv_month' => (isset($request['anniversary']) && count($anniversary) == '2') ? $anniversary[0] : '',
    		'anniv_day' => (isset($request['anniversary']) && count($anniversary) == '2') ? $anniversary[1] : '',
    		'remarks' => $request['remarks']
    	];

    	if($request['billing_id']){
    		$billing_id = decode($request['billing_id']);
    		$this->recurringRepo->update($makeRequest, $billing_id);
    		return 1;
    	}

    	$this->recurringRepo->create($makeRequest);
    	return 1;
    }

    public function edit($id)
    {
    	$id = decode($id);
    	$billing = $this->recurringRepo->find($id);
        return [
            'amount' => $billing->amount,
            'anniv_day' => sprintf("%02s", $billing->anniv_day),
            'anniv_month' => sprintf("%02s", $billing->anniv_month),
            'anniversary' => $billing->anniversary,
            'created_at' => $billing->created_at,
            'customer_id' => $billing->customer_id,
            'deletestatus' => $billing->deletestatus,
            'frequency' => $billing->frequency,
            'hashid' => $billing->hashid,
            'id' => $billing->id,
            'remarks' => $billing->remarks,
            'service_id' => $billing->service_id,
            'updated_at' => $billing->updated_at,
            'user_id' => $billing->user_id,
            'username' => $billing->username
        ];
    }

    public function destroy($id)
    {
    	$id = decode($id);
    	$this->recurringRepo->update(['deletestatus' => '1'], $id);
        return 1;
    }

    public function recurring(Request $request)
    {
        $frequencyList = [''=>'--', 'Annual' => 'Annual', 'Semi-Annual' => 'Semi-Annual', 'Quarterly' => 'Quarterly', 'Monthly' => 'Monthly'];
        $serviceList = [''=>'--']+ActivityType::where('recurring','0')->where('deletestatus','0')->orderBy('name')->pluck('name', 'id')->toArray();
        $sql_frequency = '';
        $sql_service = '';
        $from = $request['from'];
        $to = $request['to'];
        if($request['frequency']) $sql_frequency = "and frequency = '".$request['frequency']."'";
        if($request['serviceid']) $sql_service = "and service_id = '".$request['serviceid']."'";
        if($request['from']){
            $sql_date = "and anniv_month >= '".$from."' and anniv_month <= '".$to."'";
        } else {
            $sql_date = '';
        }
        $sql = "deletestatus = '0' and customer_id in (select id from customers where 1) $sql_frequency $sql_service $sql_date";
        $customers = $this->recurringRepo->customerIndex($sql);
        $billing_arr = $this->recurringRepo->datatablesIndex($sql);

        $sqlx = "deletestatus = '1' and customer_id in (select id from customers where 1) $sql_frequency $sql_service $sql_date";
        $customersx = $this->recurringRepo->customerIndex($sqlx);
        $billing_arrx = $this->recurringRepo->datatablesIndex($sqlx);

    	return view('Client::crm.billing.recurring', compact('frequencyList','serviceList','customers','billing_arr','customersx','billing_arrx'));
    }

    public function storerecurring(Request $request)
    {
        $frequencyList = [''=>'--', 'Annual' => 'Annual', 'Semi-Annual' => 'Semi-Annual', 'Quarterly' => 'Quarterly', 'Monthly' => 'Monthly'];
        $serviceList = [''=>'--']+ActivityType::where('recurring','0')->where('deletestatus','0')->orderBy('name')->pluck('name', 'id')->toArray();
        $sql_frequency = '';
        $sql_service = '';
        $from = $request['from'];
        $to = $request['to'];
        if($request['frequency']) $sql_frequency = "and frequency = '".$request['frequency']."'";
        if($request['serviceid']) $sql_service = "and service_id = '".$request['serviceid']."'";
        if($request['from']){
            $sql_date = "and anniv_month >= '".$from."' and anniv_month <= '".$to."'";
        } else {
            $sql_date = '';
        }
        $sql = "deletestatus = '0' and customer_id in (select id from customers where 1) $sql_frequency $sql_service $sql_date";
        $customers = $this->recurringRepo->customerIndex($sql);
        $billing_arr = $this->recurringRepo->datatablesIndex($sql);

        $sqlx = "deletestatus = '1' and customer_id in (select id from customers where 1) $sql_frequency $sql_service $sql_date";
        $customersx = $this->recurringRepo->customerIndex($sqlx);
        $billing_arrx = $this->recurringRepo->datatablesIndex($sqlx);

        return view('Client::crm.billing.recurring', compact('frequencyList','serviceList','customers','billing_arr','customersx','billing_arrx','from','to'));
    }

    public function getrecurring(Request $request)
    {
        $sql_frequency = '';
        $sql_service = '';
        $from = $request['from'];
        $to = $request['to'];
        if($request['frequency']) $sql_frequency = "and frequency = '".$request['frequency']."'";
        if($request['serviceid']) $sql_service = "and service_id = '".$request['serviceid']."'";
        if($request['from']){
            $sql_date = "and anniv_month >= '".$from."' and anniv_month <= '".$to."'";
        } else {
            $sql_date = '';
        }
        $sql = "deletestatus = '0' and customer_id in (select id from customers where 1) $sql_frequency $sql_service $sql_date";
        return $billings = $this->recurringRepo->datatablesIndex($sql);
        return Datatables::of($billings)
        ->editColumn('customername',function($billings){
            if(count($billings->customer)){
                return $billings->customer->name;
            }
            return '';
        })
        ->editColumn('servicename',function($billings){
            if(count($billings->service)){
                return $billings->service->name;
            }
            return '';
        })->make(true);
    }

    public function getrecurringarchive(Request $request)
    {
        $sql_frequency = '';
        $sql_service = '';
        $from = $request['from'];
        $to = $request['to'];
        if($request['frequency']) $sql_frequency = "and frequency = '".$request['frequency']."'";
        if($request['serviceid']) $sql_service = "and service_id = '".$request['serviceid']."'";
        if($request['from']){
            $sql_date = "and anniv_month >= '".$from."' and anniv_month <= '".$to."'";
        } else {
            $sql_date = '';
        }
        $sql = "deletestatus = '1' and customer_id in (select id from customers where 1) $sql_frequency $sql_service $sql_date";
        $billings = $this->recurringRepo->datatablesIndex($sql);
        return Datatables::of($billings)
        ->editColumn('customername',function($billings){
            if(count($billings->customer)){
                return $billings->customer->name;
            }
            return '';
        })
        ->editColumn('servicename',function($billings){
            if(count($billings->service)){
                return $billings->service->name;
            }
            return '';
        })->make(true);
    }

    public function annualize(Request $request)
    {
        $frequencyList = [''=>'--', 'Annual' => 'Annual', 'Semi-Annual' => 'Semi-Annual', 'Quarterly' => 'Quarterly', 'Monthly' => 'Monthly'];
        $serviceList = [''=>'--']+ActivityType::where('recurring','0')->where('deletestatus','0')->where('status','0')->orderBy('name')->pluck('name', 'id')->toArray();
        $services = ActivityType::where('recurring','0')->where('deletestatus','0')->where('status','0')->orderBy('name')->get();
        $sql_frequency = '';
        $sql_service = '';
        $from = $request['from'];
        $to = $request['to'];
        if($request['frequency']) $sql_frequency = "and frequency = '".$request['frequency']."'";
        if($request['serviceid']) $sql_service = "and service_id = '".$request['serviceid']."'";
        if($request['from']){
            $sql_date = "and anniv_month >= '".$from."' and anniv_month <= '".$to."'";
        } else {
            $sql_date = '';
        }
        $sql = "deletestatus = '0' and customer_id in (select id from customers where 1) $sql_frequency $sql_service $sql_date";
        $customers = $this->recurringRepo->customerIndex($sql);

        return view('Client::crm.billing.annualize', compact('frequencyList','serviceList','customers','services'));
    }

}
