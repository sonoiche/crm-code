<?php

namespace QxCMS\Modules\Client\Controllers\CRM;

use Carbon\Carbon;
use Datatables;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use QxCMS\Http\Controllers\Controller;
use QxCMS\Modules\Client\Models\CRM\ActivityType;
use QxCMS\Modules\Client\Models\CRM\Product;
use QxCMS\Modules\Client\Models\CRM\Salutation;
use QxCMS\Modules\Client\Models\CRM\Service;
use QxCMS\Modules\Client\Repositories\CRM\Customer\ActivityRepositoryEloquent as Activity;
use QxCMS\Modules\Client\Repositories\CRM\Customer\CustomerRepositoryEloquent as Customer;
use QxCMS\Modules\Client\Repositories\CRM\Customer\ProposalRepositoryEloquent as Proposal;
use QxCMS\Modules\Client\Repositories\CRM\Customer\ProposalApprovalRepositoryEloquent as ProposalApproval;
use QxCMS\Modules\Likod\Models\Clients\User;
use ZanySoft\Zip\Zip;

class ProposalController extends Controller
{
	protected $customerRepo;
    protected $proposalRepo;
    protected $activityRepo;
    protected $proposalappRepo;

    public function __construct(Customer $customerRepo, Proposal $proposalRepo, Activity $activityRepo, ProposalApproval $proposalappRepo)
    {
    	$this->customerRepo = $customerRepo;
    	$this->proposalRepo = $proposalRepo;
        $this->activityRepo = $activityRepo;
        $this->proposalappRepo = $proposalappRepo;
    }

    public function index($id)
    {
    	$id = decode($id);
    	$sql = "customer_id = '".$id."'";
    	$customer = $this->customerRepo->find($id);

        $salutationList = [''=>'--']+Salutation::orderBy('name')->pluck('name', 'name')->toArray();
    	$activityList = [''=>'--']+ActivityType::where('deletestatus','0')->where('status','0')->orderBy('name')->pluck('name', 'id')->toArray();
        $serviceList = [''=>'--']+ActivityType::where('service','0')->where('deletestatus','0')->where('status','0')->pluck('name', 'id')->toArray();
        $activity_types = ActivityType::where('deletestatus','0')->where('status','0')->orderBy('name')->get();
        $services = Service::orderBy('name')->get();
        $statusList = [''=>'--','Sent'=>'Sent','Won'=>'Won','Lost'=>'Lost','Cancelled'=>'Cancelled'];
        $chanceList = [''=>'--',0=>'0',25=>'25',50=>'50',75=>'75',100=>'100'];

        $productList = [''=>'--']+ActivityType::where('product','0')->where('deletestatus','0')->where('status','0')->orderBy('name')->pluck('name', 'id')->toArray();
        $products = ActivityType::where('product','0')->where('deletestatus','0')->where('status','0')->orderBy('name')->get();

        $directory = 'uploads/temp/'.\Auth::guard()->user()->id;
        File::cleanDirectory($directory);
        $proposals = $this->proposalRepo->rawAll($sql, 'created_at','desc');
        $productarray = [1,2,3,4,5];

    	return view('Client::crm.proposal.index', compact('customer','activityList','serviceList','activity_types','services','statusList','chanceList','productList','products','proposals','productarray','salutationList'));
    }

    public function store(Request $request)
    {
    	$customer_id = decode($request['customer_id']);
    	$files = \File::files('uploads/temp/'.Auth::guard()->user()->id);
        $path = 'uploads/customer/proposal';

    	if($request['id']){
    		$proposal = $this->proposalRepo->find($request['id']);
    		$attachfiles = $proposal->file;
    		foreach ($files as $key => $file) {
	            $attachfile = Carbon::now()->format('mdYhis').'-'.basename($file);
	            // if(\File::move($file, $attachfile)){
	            //     $attachfiles .= ($proposal->file!='') ? 'xnx'.$attachfile.'xnx' : $attachfile.'xnx';
	            // }
                Storage::disk('s3')->put($path.'/'.$attachfile, fopen($file, 'r+'), 'public');
                $attachfiles .= ($proposal->file!='') ? 'xnx'.$attachfile.'xnx' : $attachfile.'xnx';
	        }

    		$makeRequest = [
	        	'user_id' => Auth::guard('client')->user()->id,
	    		'customer_id' => $customer_id,
	            'name' => $request['name'],
	            'product_id' => $request['product_id'],
	            'amount' => $request['amount'],
	            'fyi' => $request['fyi'],
	            'status' => $request['status'],
	            'chance' => $request['chance'],
	            'remarks' => $request['remarks'],
	            'file' => $attachfiles
	    	];
    		$this->proposalRepo->update($makeRequest, $request['id']);
    	} else {
    		$newfiles = '';
	        foreach ($files as $key => $file) {
	            $newfile = Carbon::now()->format('mdYhis').'-'.basename($file);
	            // if(\File::move($file, $newfile)){
	            //     $newfiles .= $newfile.'xnx';
	            // }
                Storage::disk('s3')->put($path.'/'.$newfile, fopen($file, 'r+'), 'public');
                $newfiles .= $newfile.'xnx';
	        }

    		$makeRequest = [
	        	'user_id' => Auth::guard('client')->user()->id,
	    		'customer_id' => $customer_id,
	            'name' => $request['name'],
	            'product_id' => $request['product_id'],
	            'amount' => $request['amount'],
	            'fyi' => $request['fyi'],
	            'status' => $request['status'],
	            'chance' => $request['chance'],
	            'remarks' => $request['remarks'],
	            'file' => substr($newfiles, 0, -3),
                'date_submitted' => Carbon::now()->format('Y-m-d')
	    	];
        	$proposal_id = $this->proposalRepo->create($makeRequest);

            if($request['from_approval']){
                $pro = $this->proposalRepo->find($proposal_id);
                $pro_id = decode($request['pro_id']);
                $this->proposalappRepo->update(['status' => 'Inserted'], $pro_id);
                $prodocfile = (isset($proposal->file) && $proposal->file!='') ? $pro->file.'xnx'.$request['file'] : $request['file'];
                $this->proposalRepo->update(['file' => $prodocfile], $proposal_id);
            }

            $actRequest = [
                'user_id' => Auth::guard('client')->user()->id,
                'customer_id' => $customer_id,
                'activity_type' => '11',
                'service_id' => $request['product_id'],
                'due_date' => Carbon::now()->format('Y-m-d'),
                'assign_to' => Auth::guard('client')->user()->id,
                'fyi' => $request['fyi'],
                'remarks' => $request['remarks'],
                'attach_file' => substr($newfiles, 0, -3),
                'file_permission' => 'Everyone',
                'proposal_id' => $proposal_id,
                'date_added' => Carbon::now()->format('Y-m-d H:i:s')
            ];
            $this->activityRepo->create($actRequest);
            $proposal = $this->proposalRepo->findWith($proposal_id, ['customer']);
            $proposalname = $proposal->name;
            $customername = count($proposal->customer) ? $proposal->customer->name : '';
            $fyi = '';
            $fyi_email = '';
            if($proposal->fyi!=''){
                $resultfyis = DB::select("select id,email,name from ".env('DB_DATABASE').".client_users where id in (".$proposal->fyi.")");;
                foreach ($resultfyis as $resultfyi) {
                    $fyi .= $resultfyi->name.', ';
                    $fyi_email .= $resultfyi->email.',';
                }

                $details  = "<b>Remarks</b>: ".nl2br($request['remarks']).'<br><br>'."";
                $details .= "<b>Name</b>: ".$request['name']."<br/>";
                $details .= "<b>Amount</b>: ".$proposal->amount_display."<br/>";
                $details .= "<b>FYI</b>: ".substr($fyi, 0, -1)."<br/>";  
                if($proposal->file!=''){
                    $attachfile = $proposal->file;
                    $files = explode('xnx', $attachfile);
                    $attachfiles = array_filter($files);
                    $path = 'uploads/customer/proposal/';
                    $details .= 'Attachment(s) : ';
                    foreach ($attachfiles as $key => $attachfile) {
                        $details .= '<a href="http://quantumx-crm-bucket.s3.amazonaws.com/'.$path.basename($attachfile).'" target="_blank">'.$attachfile.'</a><br>';
                    }
                }
                $details .= '<br><br><b>From: '.Auth::user()->name.'</b>';
            }
            
            try {
                DB::connection('live-mysql')->getPdo();
            } catch (\Exception $e) {
                return ['response' => 1, 'customer_id' => $request['customer_id'], 'message' => 'Unfortunately, email could not be sent due to internet connection.'];
            }

            if($proposal->fyi){
                $fyi_email = $fyi_email.','.Auth::guard('client')->user()->email;
                $fyi_email = explode(',', $fyi_email);
                $ccemails = array_filter($fyi_email);

                foreach ($ccemails as $key => $value) {
                    DB::connection('live-mysql')->insert("insert into email_logs (email,subject,details,created_at,sender) values ('".$value."','Proposal: ".addslashes($proposalname)." - ".addslashes($customername)."','".addslashes($details)."','".Carbon::now()."','".Auth::user()->email."')");
                }
            }
    	}
        
        return ['response' => 1, 'customer_id' => $request['customer_id'], 'message' => ''];
    }

    public function edit($id)
    {
    	$id = decode($id);
    	$proposal = $this->proposalRepo->find($id);
    	$files = array_filter(explode('xnx', $proposal->file));
        $filex = [];
        foreach ($files as $value) {
            $filex[] = $value;
        }
        if($proposal->fyi){
        	$user = User::select('id','name')->whereRaw("id in (".$proposal->fyi.")")->get();
        } else {
            $user = [];
        }
    	return ['proposal' => $proposal, 'files' => $filex, 'user' => $user, 'arrcount' => count($filex)];
    }

    public function removefile(Request $request)
    {
    	$id = $request['id'];
    	$file = $request['file'];
    	$proposal = $this->proposalRepo->find($id);
    	$attachfile = str_replace($file, '', $proposal->file);
    	$files = explode('xnx', $attachfile);
    	$attachfiles = array_filter($files);
    	$newfiles = '';
        $path = 'uploads/customer/proposal';
    	foreach ($attachfiles as $key => $attachfile) {
    		$newfiles .= $attachfile.'xnx';
    	}

        $directory = 'uploads/temp/'.\Auth::guard()->user()->id;
        File::cleanDirectory($directory);

    	// File::delete($file);
        Storage::disk('s3')->delete($path.'/'.$file);

    	$this->proposalRepo->update(['file' => substr($newfiles, 0, -3)], $id);
    	return 1;
    }

    public function destroy($id)
    {
    	$id = decode($id);
    	$this->proposalRepo->update(['deletestatus' => '1'], $id);
    	return $id;
    }

    public function uploadMultiple(Request $request)
    {
    	// 5 minutes execution time
        @set_time_limit(5 * 60);

        // Uncomment this one to fake upload time
        // usleep(5000);

        // Settings
        // $targetDir = "document_".$valid_user."/";
        $targetDir = 'uploads/temp/'.Auth::guard()->user()->id;
        $cleanupTargetDir = true; // Remove old files
        $maxFileAge = 5 * 3600; // Temp file age in seconds


        // Create target dir
        if (!file_exists($targetDir)) {
            @mkdir($targetDir);
        }

        // Get a file name
        if (isset($_REQUEST["name"])) {
            $fileName = $_REQUEST["name"];
        } elseif (!empty($_FILES)) {
            $fileName = $_FILES["file"]["name"];
        } else {
            $fileName = uniqid("file_");
        }

        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

        // Chunking might be enabled
        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;


        // Remove old temp files    
        if ($cleanupTargetDir) {
            if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
            }

            while (($file = readdir($dir)) !== false) {
                $tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

                // If temp file is current file proceed to the next
                if ($tmpfilePath == "{$filePath}.part") {
                    continue;
                }

                // Remove temp file if it is older than the max age and is not the current file
                if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge)) {
                    @unlink($tmpfilePath);
                }
            }
            closedir($dir);
        }   


        // Open temp file
        if (!$out = @fopen("{$filePath}.part", $chunks ? "ab" : "wb")) {
            die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
        }

        if (!empty($_FILES)) {
            if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
            }

            // Read binary input stream and append it to temp file
            if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        } else {    
            if (!$in = @fopen("php://input", "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        }

        while ($buff = fread($in, 4096)) {
            fwrite($out, $buff);
        }

        @fclose($out);
        @fclose($in);

        // Check if file has been uploaded
        if (!$chunks || $chunk == $chunks - 1) {
            // Strip the temp .part suffix off 
            rename("{$filePath}.part", $filePath);
        }

        // Return Success JSON-RPC response
        die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
    }

    public function downloadfile($id)
    {
        $id = decode($id);
        $proposal = $this->proposalRepo->find($id);
        $files = array_filter(explode('xnx', $proposal->file));
        $filex = [];
        foreach ($files as $value) {
            $filex[] = $value;
        }

        $path = 'uploads/compress/';
        $time = date('Ymdhis');
        $zip = Zip::create($path.str_slug($proposal->name).'-'.$time.'.zip');
        $zip->add($filex);
        $zip->close();
        return response()->download($path.str_slug($proposal->name).'-'.$time.'.zip', str_slug($proposal->name).'-'.$time.'.zip', ['Content-Type: application/octet-stream']);
    }

    public function getproposal(Request $request)
    {
        $customer_id = $request['customer_id'];
        $proposals = $this->proposalRepo->datatablesIndex($customer_id);
        return Datatables::of($proposals)
        ->editColumn('amount',function($proposals){
            if(count($proposals->amount)){
                return number_format($proposals->amount,2);
            }
            return '';
        })
        ->editColumn('user',function($proposals){
            if(count($proposals->user)){
                return $proposals->user->name;
            }
            return '';
        })
        ->editColumn('remarks',function($proposals){
            return linkreplace($proposals->remarks);
        })
        ->setRowId('id')
        ->setRowClass(function ($proposals) {
            $proposalclass = ($proposals->deletestatus == 1) ? 'row-deleted' : '';
            return $proposalclass;
        })
        ->addColumn('action', function ($proposals) {
            $html_out  = '';
            $html_out .= '
            <div class="btn-group">
                <button type="button" class="btn btn-primary btn-xs dropdown-toggle" data-toggle="dropdown">
                    Action &nbsp;&nbsp;
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-right" id="menu3" aria-labelledby="drop6" style="min-width: 125px !important;">
                    <li><a href="javascript:void(0)" onclick="editproposal(\''.hashid($proposals->id).'\')"><i class="fa fa-pencil fa-fw"></i> Edit</a></li>
                    <li><a href="javascript:void(0)" onclick="deleteproposal(\''.hashid($proposals->id).'\')"><i class="fa fa-ban fa-fw"></i> Cancel</a></li>
                </ul>
            </div>';
            if($proposals->count_file == 1){
                $html_out .= '&nbsp;&nbsp;<a href="'.url($proposals->dlfile).'" class="btn btn-primary btn-xs" target="_blank"><i class="fa fa-download fa-fw"></i></a>';
            } elseif($proposals->count_file > 1){
                $html_out .= '&nbsp;&nbsp;<a href="javascript:void(0)" onclick="multipledoc(\''.hashid($proposals->id).'\')" class="btn btn-primary btn-xs" target="_blank"><i class="fa fa-download fa-fw"></i></a>';
            } else {
                $html_out .= '&nbsp;&nbsp;<a href="javascript:void(0)" class="btn btn-default btn-xs"><i class="fa fa-download fa-fw"></i></a>';
            }
            return $html_out;
        })->make(true);
    }

    public function multipledoc($id)
    {
        $id = decode($id);
        $proposal = $this->proposalRepo->find($id);
        $files = array_filter(explode('xnx', $proposal->file));
        $filex = [];
        foreach ($files as $value) {
            $filex[] = $value;
        }
        return ['proposal' => $proposal, 'files' => $filex, 'arrcount' => count($filex)];
    }
}
