<?php

namespace QxCMS\Modules\Client\Controllers\CRM;

use Carbon\Carbon;
use Datatables;
use File;
use Illuminate\Http\Request;
use QxCMS\Http\Controllers\Controller;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use QxCMS\Modules\Likod\Models\Clients\User;
use QxCMS\Modules\Client\Models\CRM\ActivityType;
use QxCMS\Modules\Client\Models\CRM\ProposalVersion;
use QxCMS\Modules\Client\Repositories\CRM\Customer\ProposalApprovalRepositoryEloquent as Proposal;
use QxCMS\Modules\Client\Repositories\CRM\Customer\ProposalVersionRepositoryEloquent as Version;
use QxCMS\Modules\Client\Models\CRM\Customer as Customer;

class ProposalApprovalController extends Controller
{
	protected $proposalRepo;
	protected $versionRepo;

	public function __construct(Proposal $proposalRepo, Version $versionRepo)
    {
    	$this->proposalRepo = $proposalRepo;
    	$this->versionRepo = $versionRepo;
    }

    public function index()
    {
    	$activityList = [''=>'--']+ActivityType::where('deletestatus','0')->where('status','0')->orderBy('name')->pluck('name', 'id')->toArray();
        $serviceList = [''=>'--']+ActivityType::where('service','0')->where('deletestatus','0')->where('status','0')->pluck('name', 'id')->toArray();
        $activity_types = ActivityType::where('deletestatus','0')->where('status','0')->orderBy('name')->get();
        $statusList = [''=>'--','Sent'=>'Sent','Won'=>'Won','Lost'=>'Lost','Cancelled'=>'Cancelled'];
        $chanceList = [''=>'--',0=>'0',25=>'25',50=>'50',75=>'75',100=>'100'];
        $userList = [''=>'--']+User::where('status',1)->whereIn('role_id',['1','2','4','5'])->orderBy('name')->pluck('name', 'id')->toArray();

        $productList = [''=>'--']+ActivityType::where('product','0')->where('deletestatus','0')->where('status','0')->orderBy('name')->pluck('name', 'id')->toArray();
        $products = ActivityType::where('product','0')->where('deletestatus','0')->where('status','0')->orderBy('name')->get();

        $directory = 'uploads/temp/'.Auth::guard()->user()->id;
        File::cleanDirectory($directory);
        $productarray = [1,2,3,4,5];

    	return view('Client::crm.proposal.approval.index', compact('activityList','serviceList','activity_types','statusList','chanceList','productList','products','productarray','userList'));
    }

    public function store(Request $request)
    {
    	$files = \File::files('uploads/temp/'.Auth::guard()->user()->id);
        $path = 'uploads/customer/proposal';

        $newfiles = '';
        foreach ($files as $key => $file) {
            $newfile = Carbon::now()->format('mdYhis').'-'.basename($file);
            Storage::disk('s3')->put($path.'/'.$newfile, fopen($file, 'r+'), 'public');
            $newfiles .= $newfile.'xnx';
        }

    	$makeRequest = [
    		'name' => $request['name'],
    		'product_id' => $request['product_type'],
    		'customer_id' => $request['customer_id'],
    		'amount' => $request['amount'],
    		'link' => $request['link'],
    		'fyi' => $request['fyi'],
    		'approver' => $request['approver'],
    		'remarks' => $request['remarks'],
    		'requestor' => Auth::guard('client')->user()->id,
            'pro_chance' => $request['chance'],
            'pro_status' => 'Pending'
    	];

    	if($request['id']){
    		$id = decode($request['id']);
    		$version = ProposalVersion::where('proposal_id', $id)->orderBy('id','desc')->first();
            $this->proposalRepo->update($makeRequest, $id);
            $this->versionRepo->create([
                'requestor_remarks' => $request['remarks'],
                'status' => 'Pending',
                'version' => $version->version + 1,
                'chances' => $request['chance'],
                'doc_file' => (substr($newfiles, 0, -3) == '0') ? $version->doc_file : substr($newfiles, 0, -3),
                'proposal_id' => $id,
                'amount' => $request['amount']
            ]);
            $proposal = $this->proposalRepo->findWith($id, ['userrequestor','userapprover','customer']);
            $version = ProposalVersion::where('proposal_id',$id)->orderBy('id','desc')->first();
    	} else {
	    	$id = $this->proposalRepo->create($makeRequest);
	    	$this->versionRepo->create([
	            'requestor_remarks' => $request['remarks'],
	            'status' => 'Pending',
	            'version' => 1,
                'chances' => $request['chance'],
	            'doc_file' => substr($newfiles, 0, -3),
	            'proposal_id' => $id,
	            'amount' => $request['amount']
	        ]);

	        $proposal = $this->proposalRepo->findWith($id, ['userrequestor','userapprover','customer']);
	        $version = ProposalVersion::where('proposal_id',$id)->orderBy('id','desc')->first();
    	}

        $approver = DB::table(env('DB_DATABASE').".client_users")->select('id','email','name')->where('id', $proposal->userapprover->id)->first();
        $fyi = '';
        $fyi_email = '';
        if($proposal->fyi){
            $resultfyis = DB::select("select id,email,name from ".env('DB_DATABASE').".client_users where id in (".$proposal->fyi.")");
            foreach ($resultfyis as $resultfyi) {
                $fyi .= $resultfyi->name.', ';
                $fyi_email .= $resultfyi->email.',';
            }
        }
        $proposalname = $proposal->name;
        $customername = $proposal->customer->name;
        $details  = $proposal->name.' for your approval.<br>';
        $details .= 'Customer Name: '.$proposal->customer->name.'<br>';
        $details .= 'Proposal Name: '.$proposal->name.'<br>';
        $details .= 'Requestor: '.$proposal->userrequestor->name.'<br>';
        $details .= 'Approver: '.$proposal->userapprover->name.'<br>';
        $details .= 'FYI: '.substr($fyi, 0, -1).'<br>';
        $details .= 'Date Uploaded: '.$proposal->created_at_display.'<br>';
        if($version->doc_file!=''){
            $files = explode('xnx', $version->doc_file);
            $attachfiles = array_filter($files);
            $path = 'uploads/customer/proposal/';
            $details .= 'Attachment(s) : ';
            $docfiles = '';
            foreach ($attachfiles as $key => $attachfile) {
                $docfiles .= '<a href="http://quantumx-crm-bucket.s3.amazonaws.com/'.$path.basename($attachfile).'" target="_blank">'.$attachfile.'</a>, ';
            }
            $details .= substr($docfiles, 0 ,-2).'<br>';
        }
        $details .= 'Remarks: '.$request['remarks'].'<br><br>';
        $details .= '<a href="'.url('client/crm/proposalapproval').'">Click here to approve the proposal</a>';
        $details .= '<br><br><b>From: '.Auth::guard('client')->user()->name.'</b>';
        $subject = 'Proposal: '.$proposal->name.' - '.$customername;

        try {
            DB::connection('live-mysql')->getPdo();
        } catch (\Exception $e) {
            return ['response' => 1, 'message' => 'Unfortunately, email could not be sent due to internet connection.'];
        }

        DB::connection('live-mysql')->insert("insert into email_logs (email,subject,details,created_at,sender) values ('".$approver->email."','Proposal: ".addslashes($proposalname)." - ".addslashes($customername)."','".addslashes($details)."','".Carbon::now()."','".Auth::guard('client')->user()->email."')");
        if($proposal->fyi){
            $fyi_email = $fyi_email.','.Auth::guard('client')->user()->email;
            $fyi_email = explode(',', $fyi_email);
            $ccemails = array_filter($fyi_email);

            foreach ($ccemails as $key => $value) {
                DB::connection('live-mysql')->insert("insert into email_logs (email,subject,details,created_at,sender) values ('".$value."','Proposal: ".addslashes($proposalname)." - ".addslashes($customername)."','".addslashes($details)."','".Carbon::now()."','".Auth::guard('client')->user()->email."')");
            }
        }

    	// return $request->all();
    	return ['response' => 1, 'message' => ''];
    }

    public function show($id)
    {
        $id = decode($id);
        $proposal = $this->proposalRepo->findWith($id, ['customer','activitytype','userapprover','userrequestor']);
        $sql = "proposal_id = '".$proposal->id."' order by id desc";
        $version = $this->versionRepo->rawByField($sql);
        $link = '<a href="'.$proposal->link.'" class="btn btn-primary btn-xs"><i class="fa fa-link"></i> &nbsp;Google Drive</a>';

        $files = array_filter(explode('xnx', $version->doc_file));
        $filex = [];
        foreach ($files as $value) {
            $filex[] = $value;
        }

        $divaction = ($proposal->approver == Auth::guard('client')->user()->id) ? 1 : 0;

        return ['response' => 1, 'proposal' => $proposal, 'version' => $version, 'files' => $filex, 'arrcount' => count($filex), 'link' => $link, 'divaction' => $divaction];
    }

    public function getproposalapproval()
    {
    	// $sql = "id!=''";
    	// return $this->proposalRepo->rawWith(['requestor','approver','customer'],$sql);
    	$proposals = $this->proposalRepo->datatablesIndex(Auth::guard('client')->user()->id);
        return Datatables::of($proposals)
        ->editColumn('name', function($proposals){
        	$customername = (count($proposals->customer)) ? $proposals->customer->name : '';
            return $proposals->name.'<br><small>'.$customername.'</small>';
        })
        ->editColumn('activitytype',function($proposals){
            if(count($proposals->activitytype)){
                return $proposals->activitytype->name;
            }
            return '';
        })
        ->editColumn('customer',function($proposals){
            if(count($proposals->customer)){
                return $proposals->customer->name;
            }
            return '';
        })
        ->editColumn('userrequestor',function($proposals){
            if(count($proposals->userrequestor)){
                return $proposals->userrequestor->name;
            }
            return '';
        })
        ->editColumn('userapprover',function($proposals){
            if(count($proposals->userapprover)){
                return $proposals->userapprover->name;
            }
            return '';
        })
        ->editColumn('status', function($proposals){
        	return (count($proposals->proversion) && $proposals->proversion[0]['status'] == 'Approved') ? $proposals->proversion[0]['status'].'<br><b>'.$proposals->proversion[0]['created_at_display'].'</b>' : '';
        })
        ->editColumn('created_at',function($proposals){
            return Carbon::parse($proposals->created_at)->format('M d, Y');
        })
        ->editColumn('versioncount',function($proposals){
            if(count($proposals->proversion)){
                return '<div style="text-align:center"><a href="javascript:void(0)" onclick="viewversion('.$proposals->id.')">'.count($proposals->proversion).'</a></div>';
            }
            return '';
        })
        ->addColumn('action', function ($proposals) {
            $html_out  = '';
            if($proposals->status != 'Inserted'){
                if(count($proposals->proversion) && $proposals->requestor == Auth::user()->id && $proposals->proversion[0]->status != 'Approved'){
    				$html_out .= '<a href="javascript:void(0)" onclick="editproposal(\''.$proposals->hashid.'\')" class="btn btn-success btn-xs" data-toggle="tooltip" title="Revise Proposal"><span class="fa fa-pencil"></span></a>&nbsp;&nbsp;';
                }
    			if(count($proposals->proversion) && ($proposals->approver == Auth::user()->id || Auth::user()->access_id == 11 || Auth::user()->access_id == 1) && $proposals->proversion[0]->status != 'Approved'){
    				$html_out .= '<a href="javascript:void(0)" onclick="viewproposal(\''.$proposals->hashid.'\')" class="btn btn-primary btn-xs" data-toggle="tooltip" title="Review Proposal"><span class="fa fa-search"></span></a>&nbsp;&nbsp;';
    			}
    			if(count($proposals->proversion) && $proposals->requestor == Auth::user()->id && $proposals->proversion[0]->status == 'Approved'){
    				$html_out .= '<a href="javascript:void(0)" onclick="moveproposal(\''.$proposals->hashid.'\')" class="btn btn-warning btn-xs" data-toggle="tooltip" title="Insert to Customer"><span class="fa fa-share"></span></a>';
    			}
            } else {
                $html_out .= '<a href="'.url('client/crm/customer', $proposals->customer->hashid).'/proposal" class="btn btn-primary btn-xs">View</a>';
            }
			return $html_out;
        })->orderColumn('created_at', 'proposals.created_at $1, proposals.created_at desc')->make(true);
    }

    public function info($id)
    {
    	$proposal = $this->proposalRepo->findWith($id, ['customer','activitytype']);
    	$versions = ProposalVersion::where('proposal_id',$id)->orderBy('id','desc')->get();
    	return ['response' => 1, 'proposal' => $proposal, 'versions' => $versions];
    }

    public function edit($id)
    {
        $id = decode($id);
        $proposal = $this->proposalRepo->find($id);
        $sql = "proposal_id = '".$proposal->id."' order by id desc";
        if($proposal->customer_id){
            $customer = Customer::select('id','name','firstcontact')->whereRaw("id = '".$proposal->customer_id."'")->get();
        } else {
            $customer = [];
        }
        $version = $this->versionRepo->rawByField($sql);
        if($proposal->fyi){
            $user = User::select('id','name')->whereRaw("id in (".$proposal->fyi.")")->get();
        } else {
            $user = [];
        }

        $files = array_filter(explode('xnx', $version->doc_file));
        $filex = [];
        foreach ($files as $value) {
            $filex[] = $value;
        }

        return ['response' => 1, 'proposal' => $proposal, 'customer' => $customer, 'version' => $version, 'user' => $user, 'files' => $filex, 'arrcount' => count($filex)];
    }

    public function approve(Request $request)
    {
        $id = decode($request['id']);
        $makeRequest = [
            'status' => $request['status'],
            'approver_remarks' => $request['approver_remarks']
        ];

        $this->versionRepo->update($makeRequest, $id);
        $version = $this->versionRepo->find($id);
        $proposal = $this->proposalRepo->findWith($version->proposal_id, ['customer','userrequestor','userapprover']);
        $this->proposalRepo->update(['pro_status' => $request['status']], $proposal->id);

        $customername = (count($proposal->customer)) ? $proposal->customer->name : '';
        $requestor = (count($proposal->userrequestor)) ? $proposal->userrequestor->name : '';
        $approver = (count($proposal->userapprover)) ? $proposal->userapprover->name : '';

        $fyi = '';
        $fyi_email = '';
        if($proposal->fyi){
            $resultfyis = DB::select("select id,email,name from ".env('DB_DATABASE').".client_users where id in (".$proposal->fyi.")");
            foreach ($resultfyis as $resultfyi) {
                $fyi .= $resultfyi->name.', ';
                $fyi_email .= $resultfyi->email.',';
            }
        }

        $proposalstatus = ($request['status'] == 'For Revision') ? $proposal->name.' is for revision.' : $proposal->name.' has been '.$request['status'].'.';
        $details  = $proposalstatus.'<br>';
        $details .= 'Remarks: '.$version->approver_remarks.'<br><br>';
        $details .= 'Customer Name: '.$customername.'<br>';
        $details .= 'Proposal Name: '.$proposal->name.'<br>';
        $details .= 'Requestor: '.$requestor.'<br>';
        $details .= 'Approver: '.$approver.'<br>';
        $details .= 'FYI: '.$fyi.'<br>';
        $details .= 'Date Uploaded: '.$proposal->created_at_display.'<br>';
        $details .= 'Date Approved: '.$version->created_at_display.'<br>';
        if($version->doc_file!=''){
            $files = explode('xnx', $version->doc_file);
            $attachfiles = array_filter($files);
            $path = 'uploads/customer/proposal/';
            $details .= 'Attachment(s) : ';
            $docfiles = '';
            foreach ($attachfiles as $key => $attachfile) {
                $docfiles .= '<a href="http://quantumx-crm-bucket.s3.amazonaws.com/'.$path.basename($attachfile).'" target="_blank">'.$attachfile.'</a>, ';
            }
            $details .= substr($docfiles, 0 ,-2);
        }
        $subject = 'Proposal: '.$proposal->name.' - '.$customername;

        try {
            DB::connection('live-mysql')->getPdo();
        } catch (\Exception $e) {
            return ['response' => 1, 'message' => 'Unfortunately, email could not be sent due to internet connection.'];
        }

        DB::connection('live-mysql')->insert("insert into email_logs (email,subject,details,created_at,sender) values ('".$proposal->userrequestor->email."','".$subject."','".addslashes($details)."','".Carbon::now()."','".Auth::guard('client')->user()->email."')");
        if($proposal->fyi){
            $fyi_email = $fyi_email.','.Auth::guard('client')->user()->email;
            $fyi_email = explode(',', $fyi_email);
            $ccemails = array_filter($fyi_email);

            foreach ($ccemails as $key => $value) {
                DB::connection('live-mysql')->insert("insert into email_logs (email,subject,details,created_at,sender) values ('".$value."','".$subject."','".addslashes($details)."','".Carbon::now()."','".Auth::guard('client')->user()->email."')");
            }
        }

        // return $request->all();
        return ['response' => 1, 'message' => ''];
    }

    public function moveproposal($id)
    {
        $id = decode($id);
        $proposal = $this->proposalRepo->findWith($id, ['customer','activitytype','userapprover','userrequestor']);
        if($proposal->fyi){
            $user = User::select('id','name')->whereRaw("id in (".$proposal->fyi.")")->get();
        } else {
            $user = [];
        }
        $version = ProposalVersion::where('proposal_id',$id)->orderBy('id','desc')->first();
        $customer_id = hashid($proposal->customer_id);

        $files = array_filter(explode('xnx', $version->doc_file));
        $filex = [];
        foreach ($files as $value) {
            $filex[] = $value;
        }

        return ['response' => 1, 'proposal' => $proposal, 'user' => $user, 'version' => $version, 'customer_id' => $customer_id, 'files' => $filex, 'arrcount' => count($filex)];
    }
}