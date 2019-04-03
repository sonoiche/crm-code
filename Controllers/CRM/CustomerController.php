<?php

namespace QxCMS\Modules\Client\Controllers\CRM;
ini_set('max_execution_time', 180);
ini_set('memory_limit', '512M');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use QxCMS\Http\Controllers\Controller;
use QxCMS\Http\Requests;
use QxCMS\Modules\Client\Models\CRM\ActivityType;
use QxCMS\Modules\Client\Models\CRM\Industry;
use QxCMS\Modules\Client\Models\CRM\Product;
use QxCMS\Modules\Client\Models\CRM\Salutation;
use QxCMS\Modules\Client\Models\CRM\Service;
use QxCMS\Modules\Client\Models\CRM\Customer as Customers;
use QxCMS\Modules\Client\Repositories\CRM\Customer\ContactRepositoryEloquent as Contact;
use QxCMS\Modules\Client\Repositories\CRM\Customer\CustomerRepositoryEloquent as Customer;
use QxCMS\Modules\Client\Repositories\CRM\Customer\ActivityRepositoryEloquent as Activity;
use QxCMS\Modules\Likod\Models\Clients\User;

use QxCMS\Modules\Client\Models\CRM\Activity as mActivity;
use QxCMS\Modules\Client\Models\CRM\Payment as mPayment;
use QxCMS\Modules\Client\Models\CRM\Proposal as mProposal;
use QxCMS\Modules\Client\Models\CRM\RecurringBilling as mRecurringBilling;
use QxCMS\Modules\Client\Models\CRM\Event as mEvent;
use QxCMS\Modules\Client\Models\CRM\Contract as mContract;

use DB;

class CustomerController extends Controller
{   
	protected $customerRepo;
    protected $contactRepo;
    protected $activityRepo;

	public function __construct(Customer $customerRepo, Contact $contactRepo, Activity $activityRepo)
	{
		$this->customerRepo = $customerRepo;
        $this->contactRepo = $contactRepo;
        $this->activityRepo = $activityRepo;
	}

    public function index()
    {
        $activityList = [''=>'--']+ActivityType::where('deletestatus','0')->where('status','0')->orderBy('name')->pluck('name', 'id')->toArray();
        $serviceList = [''=>'--']+ActivityType::where('service','0')->where('deletestatus','0')->where('status','0')->pluck('name', 'id')->toArray();
        $activity_types = ActivityType::where('deletestatus','0')->where('status','0')->orderBy('name')->get();
        $services = Service::orderBy('name')->get();
    	return view('Client::crm.customer.index', compact('activityList','activity_types','serviceList','services'));
    }

    public function create()
    {
    	$industryList = [''=>'--']+Industry::orderBy('name')->pluck('name', 'id')->toArray();
    	$industries = Industry::orderBy('name')->get();
        $productList = [''=>'--']+Product::orderBy('name')->pluck('name', 'id')->toArray();
        $products = Product::orderBy('name')->get();

    	$userList = [''=>'--']+User::orderBy('name')->pluck('name', 'id')->toArray();
        $productarray = [1,2,3,4,5];
    	return view('Client::crm.customer.create', compact('industryList','industries','userList','productList','products','productarray'));
    }

    public function store(Request $request)
    {
        $arr = json_decode($request['array_email']);
        $emails = '';
        if($request['array_email']!='""' && $request['array_email']!=''){
            foreach ($arr as $key => $value) {
                $emails .= $value->text.', ';
            }
        }

        $link_old = ['http://','https://'];
        $link_new = ['',''];
        $url_link = str_replace($link_old, $link_new, $request['website']);

    	$makeRequest = [
            'product_id' => $request['product_id'],
    		'industry_id' => $request['industry_id'],
	        'name' => $request['name'],
	        'tin_number' => $request['tin_number'],
	        'address' => $request['address'],
	        'address2' => $request['address2'],
            'mobile_number' => $request['mobile_number'],
            'telephone' => $request['telephone'],
	        'local' => $request['local'],
	        'fax_number' => $request['fax_number'],
	        'email' => ($emails) ? substr($emails, 0, -2) : '',
	        'website' => 'http://'.$url_link,
	        'person_in_charge' => $request['person_in_charge'],
	        'status' => $request['status'],
	        'firstcontact' => $request['firstcontact'],
	        'remarks' => $request['remarks'],
	        'user_id' => Auth::guard('client')->user()->id,
            'usage_link' => $request['usage_link']
    	];

        if($request['id']){
            $id = decode($request['id']);
            $this->customerRepo->update($makeRequest, $id);
            return ['response' => 1];
        }

    	$id = hashid($this->customerRepo->create($makeRequest));
    	return ['response' => 1, 'customer_id' => $id];
    }

    public function storeindustry(Request $request)
    {
        if($request['industry_id']){
            $id = decode($request['industry_id']);
            if(Industry::where('name', $request['name'])->where('id','!=',$id)->count()){
                return ['result' => 1];
            }
    		$updateindustry = Industry::find($id);
            $updateindustry->name = $request['name'];
            $updateindustry->save();
    		return ['id' => $id, 'name' => $request['name'], 'result' => 2];
    	}

    	if(Industry::where('name', $request['name'])->count()){
    		return ['result' => 1];
    	}
    	$id = Industry::create(['name' => $request['name'], 'user_id' => Auth::guard('client')->user()->id])->id;
    	$count = Industry::count();
        return ['id' => $id, 'name' => $request['name'], 'count' => $count, 'hashid' => hashid($id)];
    }

    public function deleteindustry($id)
    {
        $industry_id = decode($id);
        $industry = Industry::find($industry_id);
        $industry->delete();
        return ['id' => $industry_id];
    }

    public function show($id)
    {
    	$id = decode($id);
        $sql = "customer_id = '".$id."'";
        $sqlcontact = "customer_id = '".$id."' and status = '1'";
    	$customer = $this->customerRepo->find($id);
        $contacts = $this->contactRepo->rawAll($sqlcontact);

        $salutationList = [''=>'--']+Salutation::orderBy('name')->pluck('name', 'name')->toArray();
        $activityList = [''=>'--']+ActivityType::where('deletestatus','0')->where('status','0')->orderBy('name')->pluck('name', 'id')->toArray();
        $serviceList = [''=>'--']+ActivityType::where('service','0')->where('deletestatus','0')->where('status','0')->pluck('name', 'id')->toArray();
        $activity_types = ActivityType::where('deletestatus','0')->where('status','0')->orderBy('name')->get();
        $services = Service::orderBy('name')->get();
        $activities = $this->activityRepo->rawAll($sql,'date_added','desc');
    	return view('Client::crm.customer.show', compact('customer','contacts','salutationList','activityList','activity_types','serviceList','services','activities'));
    }

    public function edit($id)
    {
        $id = decode($id);
        $customer = $this->customerRepo->find($id);
        $industryList = [''=>'--']+Industry::orderBy('name')->pluck('name', 'id')->toArray();
        $industries = Industry::orderBy('name')->get();
        $productList = [''=>'--']+Product::orderBy('name')->pluck('name', 'id')->toArray();
        $products = Product::orderBy('name')->get();

        $userList = [''=>'--']+User::orderBy('name')->pluck('name', 'id')->toArray();
        $productarray = [1,2,3,4,5];

        $salutationList = [''=>'--']+Salutation::orderBy('name')->pluck('name', 'name')->toArray();
        $activityList = [''=>'--']+ActivityType::where('deletestatus','0')->where('status','0')->orderBy('name')->pluck('name', 'id')->toArray();
        $serviceList = [''=>'--']+ActivityType::where('service','0')->where('deletestatus','0')->where('status','0')->pluck('name', 'id')->toArray();
        $activity_types = ActivityType::where('deletestatus','0')->where('status','0')->orderBy('name')->get();
        $services = Service::orderBy('name')->get();
        $emails = explode(',', $customer->email);
        return view('Client::crm.customer.edit', compact('customer','industryList','industries','userList','productList','products','productarray','activityList','activity_types','serviceList','services','emails','salutationList'));
    }

    public function search(Request $request)
    {
        $name = $request['searchtext'];
        $type = $request['searchtype'];
        if($type == 1){
            $sql = "name like '".addslashes($name)."%'";
        } else {
            $sql = "name like '%".addslashes($name)."%'";
        }

        if(isset($request['status']) && $request['status'] == 'dashboard'){
        	$customers = $this->customerRepo->rawWith(['latestactivity' => function($query){
	            $query->orderBy('created_at','desc');
	        },'latestactivity.activitytype'], $sql, 'name');
        	$statusname = $request['status'];
        	$activityList = [''=>'--']+ActivityType::where('deletestatus','0')->where('status','0')->orderBy('name')->pluck('name', 'id')->toArray();
	        $serviceList = [''=>'--']+ActivityType::where('service','0')->where('deletestatus','0')->where('status','0')->pluck('name', 'id')->toArray();
	        $activity_types = ActivityType::where('deletestatus','0')->where('status','0')->orderBy('name')->get();
	        $services = Service::orderBy('name')->get();
	    	return view('Client::crm.customer.index', compact('activityList','activity_types','serviceList','services','statusname','customers'))->with(['name' => $name]);
        }

        return $this->customerRepo->rawWith(['latestactivity' => function($query){
            $query->orderBy('created_at','desc');
        },'latestactivity.activitytype'], $sql, 'name');
    }

    public function contact($id)
    {
        $id = decode($id);
        $customer = $this->customerRepo->find($id);
        $salutationList = [''=>'--']+Salutation::orderBy('name')->pluck('name', 'name')->toArray();
        $sql = "customer_id = '".$id."' and status = '1'";
        $sqlresign = "customer_id = '".$id."' and status = '0'";
        $contacts = $this->contactRepo->rawAll($sql, 'fname');
        $resigns = $this->contactRepo->rawAll($sqlresign, 'fname');

        $activityList = [''=>'--']+ActivityType::orderBy('name')->pluck('name', 'id')->toArray();
        $serviceList = [''=>'--']+Service::orderBy('name')->pluck('name', 'id')->toArray();
        $activity_types = ActivityType::orderBy('name')->get();
        $services = Service::orderBy('name')->get();
        return view('Client::crm.customer.contact', compact('customer','salutationList','contacts','activityList','activity_types','serviceList','services','resigns'));
    }

    public function storecontact(Request $request)
    {
        $arr = json_decode($request['array_email']);
        $emails = '';
        if($request['array_email']!='""' && $request['array_email']!=''){
            foreach ($arr as $key => $value) {
                $emails .= $value->text.', ';
            }
        }

        $customer_id = decode($request['customer_id']);
        $makeRequest = [
            'customer_id' => $customer_id,
            'salutation' => $request['salutation'],
            'fname' => $request['fname'],
            'lname' => $request['lname'],
            'position' => $request['position'],
            'department' => $request['department'],
            'telephone' => $request['telephone'],
            'local' => $request['local'],
            'mobile_number' => $request['mobile_number'],
            'fax_number' => $request['fax_number'],
            'email' => ($emails) ? substr($emails, 0, -2) : '',
            'status' => $request['status'],
            'remarks' => $request['remarks']
        ];

        if($request['contact_id']){
            $this->contactRepo->update($makeRequest, $request['contact_id']);
            return 1;
        }

        $this->contactRepo->create($makeRequest);
        return 1;
    }

    public function editcontact($id)
    {
        $contact_id = decode($id);
        $contact = $this->contactRepo->find($contact_id);
        $emails = explode(',', $contact->email);
        return ['contact' => $contact, 'emails' => $emails];
    }

    public function deletecontact($id)
    {
        $contact_id = decode($id);
        // $this->contactRepo->delete($contact_id);
        $this->contactRepo->update(['status' => '0'], $contact_id);
        return 1;
    }

    public function activecontact($id)
    {
        $contact_id = decode($id);
        // $this->contactRepo->delete($contact_id);
        $this->contactRepo->update(['status' => '1'], $contact_id);
        return 1;
    }

    public function editindustry($id)
    {
        $industry_id = decode($id);
        return Industry::find($industry_id);
    }

    public function storeproduct(Request $request)
    {
        if($request['product_id']){
            $id = decode($request['product_id']);
            if(Product::where('name', $request['name'])->where('id','!=',$id)->count()){
                return ['result' => 1];
            }
            $updateproduct = Product::find($id);
            $updateproduct->name = $request['name'];
            $updateproduct->save();
            return ['id' => $id, 'name' => $request['name'], 'result' => 2];
        }

        if(Product::where('name', $request['name'])->count()){
            return ['result' => 1];
        }
        $id = Product::create(['name' => $request['name'], 'user_id' => Auth::guard('client')->user()->id])->id;
        $count = Product::count();
        return ['id' => $id, 'name' => $request['name'], 'count' => $count, 'hashid' => hashid($id)];
    }

    public function editproduct($id)
    {
        $product_id = decode($id);
        return Product::find($product_id);
    }

    public function deleteproduct($id)
    {
        $product_id = decode($id);
        $product = Product::find($product_id);
        $product->delete();
        return ['id' => $product_id];
    }

    public function userlist(Request $request)
    {
        $q = $request->get('q');
        $userlist = User::select('id','name')->where('status',1)->where('name','like','%'.$q.'%')->orderBy('name')->get();

        return $userlist->toJson();
    }

    public function multiactivity()
    {
        $activityList = [''=>'--']+ActivityType::orderBy('name')->pluck('name', 'id')->toArray();
        $serviceList = [''=>'--']+Service::orderBy('name')->pluck('name', 'id')->toArray();
        $activity_types = ActivityType::orderBy('name')->get();
        $services = Service::orderBy('name')->get();
        return view('Client::crm.activity.multiactivity', compact('activityList','serviceList','activity_types','services'));
    }

    public function customerlist(Request $request)
    {
        $q = $request->get('q');
        $customerlist = Customers::select('id','name','firstcontact')->where('name','like','%'.$q.'%')->orderBy('name')->get();

        return $customerlist->toJson();
    }

    public function checkname(Request $request)
    {
        $id = decode($request['id']);
        if($id){
            $sql = "name = '".$request['name']."' and id != '".$id."'";
            $dup = $this->customerRepo->rawAll($sql);
            return count($dup);
        } else {
            $sql = "name = '".$request['name']."'";
            $dup = $this->customerRepo->rawAll($sql);
            return count($dup);
        }
    }

    public function cleandirectory()
    {
        $directory = 'uploads/temp/'.\Auth::guard()->user()->id;
        \File::cleanDirectory($directory);
        return 1;
    }

    public function activitytypelist(Request $request)
    {
        $q = $request->get('q');
        $customerlist = ActivityType::select('id','name')->where('name','like','%'.$q.'%')->orderBy('name')->get();

        return $customerlist->toJson();
    }

    public function merge(Request $request)
    {
        $ids = $request['customer_ids'];
        $customers = $this->customerRepo->getWhereIn('id', $ids, 'name');

        $content = '';
        unset($content);
        foreach ($customers as $customer) {
            $customer_id = $customer->id;
            $contact = DB::connection('client')->table('customer_contacts')->where('customer_id',$customer_id)->count();
            $activity = DB::connection('client')->table('activities')->where('customer_id',$customer_id)->count();
            $invoice = DB::connection('client')->table('payments')->where('customer_id',$customer_id)->count();
            $billing = DB::connection('client')->table('recurring_billings')->where('customer_id',$customer_id)->count();

            $proposal = DB::connection('client')->table('proposals')->where('customer_id',$customer_id)->count();
            $contract = DB::connection('client')->table('contracts')->where('customer_id',$customer_id)->count();

            $content  = '<div class="box box-primary" id="div_'.$customer->id.'">';
                $content .= '<table class="table table-hover">';
                $content .= '<tr><td><b>Module</b></td><td style="text-align:center"><b>No of Records</b></td></tr>';
                $content .= '<tr><td>Contact</td><td style="text-align:center">'.$contact.'</td></tr>';
                $content .= '<tr><td>Activity</td><td style="text-align:center">'.$activity.'</td></tr>';
                $content .= '<tr><td>Invoice</td><td style="text-align:center">'.$invoice.'</td></tr>';
                $content .= '<tr><td>Recurring Billing</td><td style="text-align:center">'.$billing.'</td></tr>';
                $content .= '<tr><td>Proposal</td><td style="text-align:center">'.$proposal.'</td></tr>';
                $content .= '<tr><td>Contracts</td><td style="text-align:center">'.$contract.'</td></tr>';
                $content .= '</table>';
            $content .= '</div>';
        }

        return ['customers' => $customers, 'response' => 1, 'content' => $content];
    }

    public function mergeaction(Request $request)
    {
        $ids = explode(',', $request['customer_ids']);
        $ids = array_filter($ids);
        $ids = array_diff($ids, explode(',', $request['id']));

        mActivity::whereIn('customer_id', $ids)->update(['customer_id' => $request['id']]);
        mPayment::whereIn('customer_id', $ids)->update(['customer_id' => $request['id']]);
        mProposal::whereIn('customer_id', $ids)->update(['customer_id' => $request['id']]);
        mRecurringBilling::whereIn('customer_id', $ids)->update(['customer_id' => $request['id']]);
        mContract::whereIn('customer_id', $ids)->update(['customer_id' => $request['id']]);
        mEvent::whereIn('customer_id', $ids)->update(['customer_id' => $request['id']]);

        Customers::whereIn('id', $ids)->update(['status' => 'Inactive']);

        return 1;
    }

    public function hashid($id)
    {
        $id = hashid($id);
        return redirect()->to('client/crm/customer/'.$id);
    }
}
