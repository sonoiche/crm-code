<?php

namespace QxCMS\Modules\Client\Controllers\CRM;

use Carbon\Carbon;
use Datatables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use QxCMS\Http\Controllers\Controller;
use QxCMS\Modules\Client\Models\CRM\ActivityType;
use QxCMS\Modules\Client\Models\CRM\Product;
use QxCMS\Modules\Client\Models\CRM\Salutation;
use QxCMS\Modules\Client\Models\CRM\Service;
use QxCMS\Modules\Client\Repositories\CRM\Customer\ActivityRepositoryEloquent as Activity;
use QxCMS\Modules\Client\Repositories\CRM\Customer\ContractRepositoryEloquent as Contract;
use QxCMS\Modules\Client\Repositories\CRM\Customer\CustomerRepositoryEloquent as Customer;
use QxCMS\Modules\Likod\Models\Clients\User;
use ZanySoft\Zip\Zip;

class ContractController extends Controller
{
	protected $customerRepo;
    protected $contractRepo;
    protected $activityRepo;

    public function __construct(Customer $customerRepo, Contract $contractRepo, Activity $activityRepo)
    {
    	$this->customerRepo = $customerRepo;
    	$this->contractRepo = $contractRepo;
        $this->activityRepo = $activityRepo;
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
        $productList = [''=>'--']+Product::orderBy('name')->pluck('name', 'id')->toArray();
        $products = Product::orderBy('name')->get();
        $contracts = $this->contractRepo->rawAll($sql, 'created_at', 'desc');
        $productarray = [1,2,3,4,5];

    	return view('Client::crm.contract.index', compact('customer','activityList','serviceList','activity_types','services','productList','products','contracts','productarray','salutationList'));
    }

    public function store(Request $request)
    {
    	$path = 'uploads/customer/contract';
    	$customer_id = decode($request['customer_id']);
        $files = \File::files('uploads/temp/'.Auth::guard()->user()->id);
    	if($request['id']){
    		$id = $request['id'];
    		$contract = $this->contractRepo->find($id);
    		
            $attachfiles = $contract->attach_file;
            foreach ($files as $key => $file) {
                $attachfile = Carbon::now()->format('mdYhis').'-'.basename($file);
                // if(\File::move($file, $attachfile)){
                //     $attachfiles .= ($contract->attach_file!='') ? 'xnx'.$attachfile.'xnx' : $attachfile.'xnx';
                // }
                Storage::disk('s3')->put($path.'/'.$attachfile, fopen($file, 'r+'), 'public');
                $attachfiles .= ($contract->attach_file!='') ? 'xnx'.$attachfile.'xnx' : $attachfile.'xnx';
            }

	    	$makeRequest = [
	    		'customer_id' => $customer_id,
	    		'user_id' => Auth::guard('client')->user()->id,
	    		'product_id' => $request['product_id'],
	    		'amount' => $request['amount'],
	    		'complete_date' => $request['complete_date'],
	    		'contract_date' => $request['contract_date'],
	    		'attach_file' => $attachfiles,
	    		'remarks' => $request['remarks'],
	    		'fyi' => $request['fyi'],
	    		'name' => $request['name'],
                'contract_type' => $request['contract_type']
	    	];
	    	$this->contractRepo->update($makeRequest, $id);
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
	    		'customer_id' => $customer_id,
	    		'user_id' => Auth::guard('client')->user()->id,
	    		'product_id' => $request['product_id'],
	    		'amount' => $request['amount'],
	    		'complete_date' => $request['complete_date'],
	    		'contract_date' => $request['contract_date'],
	    		'attach_file' => substr($newfiles, 0, -3),
	    		'remarks' => $request['remarks'],
	    		'fyi' => $request['fyi'],
	    		'name' => $request['name'],
                'date_added' => Carbon::now()->format('Y-m-d H:i:s'),
                'contract_type' => $request['contract_type']
	    	];
    		$contract_id = $this->contractRepo->create($makeRequest);

            $pathx = 'uploads/customer/activity';
            $filesx = \File::files('uploads/temp/'.Auth::guard()->user()->id);

            $newfilesx = '';
            foreach ($files as $key => $file) {
                $newfile = Carbon::now()->format('mdYhis').'-'.basename($file);
                // if(\File::move($file, $newfile)){
                //     $newfiles .= $newfile.'xnx';
                // }
                Storage::disk('s3')->put($pathx.'/'.$newfile, fopen($file, 'r+'), 'public');
                $newfilesx .= $newfile.'xnx';
            }

            $actRequest = [
                'user_id' => Auth::guard('client')->user()->id,
                'customer_id' => decode($request['customer_id']),
                'activity_type' => '24',
                'due_date' => $request['contract_date'],
                'assign_to' => Auth::guard('client')->user()->id,
                'remarks' => 'Create new contract '.$request['remarks'],
                'attach_file' => substr($newfilesx, 0, -3),
                'file_permission' => 'Everyone',
                'date_added' => Carbon::now()->format('Y-m-d H:i:s')
            ];
            $this->activityRepo->create($actRequest);
            $contract = $this->contractRepo->findWith($contract_id, ['customer']);
            $customername = count($contract->customer) ? $contract->customer->name : '';
            $fyi = '';
            $fyi_email = '';
            if($contract->fyi!=''){
                $resultfyis = DB::select("select id,email,name from ".env('DB_DATABASE').".client_users where id in (".$contract->fyi.")");;
                foreach ($resultfyis as $resultfyi) {
                    $fyi .= $resultfyi->name.', ';
                    $fyi_email .= $resultfyi->email.',';
                }

                $details  = '<b>Customer: </b>'.$customername.'<br><br>';
                if($contract->attach_file!=''){
                    $attachfile = str_replace($file, '', $contract->attach_file);
                    $files = explode('xnx', $attachfile);
                    $attachfiles = array_filter($files);
                    $path = 'uploads/customer/contract/';
                    $details .= 'Please be informed that the following contract was uploaded.<br>';
                    foreach ($attachfiles as $key => $attachfile) {
                        $details .= '<a href="http://quantumx-crm-bucket.s3.amazonaws.com/'.$path.basename($attachfile).'" target="_blank">'.$attachfile.'</a><br>';
                    }
                }
                $details .= '<br>Contract Name : '.addslashes($contract->name).'<br>';
                if($contract->contract_date!=''){
                    $details .= 'Contract Date : '.$contract->contract_date.'<br>';
                }
                if($contract->amount!=''){
                    $details .= 'Contract Amount : '.$contract->amount_display.'<br><br>';
                }
                if($contract->remarks!=''){
                    $details .= '<b>Remarks</b><br>';
                    $details .= $contract->remarks;
                }
                $details .= '<br><br><b>From: '.Auth::user()->name.'</b>';
            }
            
            try {
                DB::connection('live-mysql')->getPdo();
            } catch (\Exception $e) {
                return ['response' => 1, 'customer_id' => $request['customer_id'], 'message' => 'Unfortunately, email could not be sent due to internet connection.'];
            }

            if($contract->fyi){
                $fyi_email = $fyi_email.','.Auth::guard('client')->user()->email;
                $fyi_email = explode(',', $fyi_email);
                $ccemails = array_filter($fyi_email);

                foreach ($ccemails as $key => $value) {
                    DB::connection('live-mysql')->insert("insert into email_logs (email,subject,details,created_at,sender) values ('".$value."','Contract: ".addslashes($contract->name)." - ".addslashes($customername)."','".addslashes($details)."','".Carbon::now()."','".Auth::user()->email."')");
                }
            }
	    }

    	return ['response' => 1, 'customer_id' => $request['customer_id'], 'message' => ''];
    }

    public function edit($id)
    {
    	$id = decode($id);
    	$contract = $this->contractRepo->find($id);
        if(ContainsNumbers($contract->fyi)){
        	$user = User::select('id','name')->whereRaw("id in (".$contract->fyi.")")->get();
        } else if($contract->fyi!='') {
            $username = str_replace(",", "','", $contract->fyi);
            $user = User::select('id','name')->whereRaw("username in ('".$username."')")->get();
        } else {
            $user = [];
        }

        $files = array_filter(explode('xnx', $contract->attach_file));
        $filex = [];
        foreach ($files as $value) {
            $filex[] = $value;
        }

    	return ['contract' => $contract, 'user' => $user, 'files' => $filex, 'arrcount' => count($filex)];
    }

    public function removefile(Request $request)
    {
        $id = $request['id'];
        $file = $request['file'];
        $contract = $this->contractRepo->find($id);
        $attachfile = str_replace($file, '', $contract->attach_file);
        $files = explode('xnx', $attachfile);
        $attachfiles = array_filter($files);
        $newfiles = '';
        $path = 'uploads/customer/contract';
        foreach ($attachfiles as $key => $attachfile) {
            $newfiles .= $attachfile.'xnx';
        }

        $directory = 'uploads/temp/'.\Auth::guard()->user()->id;
        File::cleanDirectory($directory);

        // File::delete($file);
        Storage::disk('s3')->delete($path.'/'.$file);

        $this->contractRepo->update(['attach_file' => substr($newfiles, 0, -3)], $id);
        return 1;
    }

    public function destroy($id)
    {
    	$id = decode($id);
        $this->contractRepo->update(['deletestatus' => '1'], $id);
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
        $contract = $this->contractRepo->find($id);
        $files = array_filter(explode('xnx', $contract->attach_file));
        $filex = [];
        foreach ($files as $value) {
            $filex[] = $value;
        }

        $path = 'uploads/compress/';
        $time = date('Ymdhis');
        $zip = Zip::create($path.str_slug($contract->name).'-'.$time.'.zip');
        $zip->add($filex);
        $zip->close();
        return response()->download($path.str_slug($contract->name).'-'.$time.'.zip', str_slug($contract->name).'-'.$time.'.zip', ['Content-Type: application/octet-stream']);
    }

    public function getcontract(Request $request)
    {
        $customer_id = $request['customer_id'];
        $contracts = $this->contractRepo->datatablesIndex($customer_id);
        return Datatables::of($contracts)
        ->editColumn('amount',function($contracts){
            if(count($contracts->amount)){
                return number_format($contracts->amount,2);
            }
            return '';
        })
        ->editColumn('product',function($contracts){
            if($contracts->contract_type){
                return $contracts->contract_type;
            }
            return '';
        })
        ->editColumn('user',function($contracts){
            if(count($contracts->user)){
                return $contracts->user->name;
            }
            return '';
        })
        ->editColumn('remarks',function($contracts){
            return linkreplace($contracts->remarks);
        })
        ->setRowId('id')
        ->setRowClass(function ($contracts) {
            $contractclass = ($contracts->deletestatus == 1) ? 'row-deleted' : '';
            return $contractclass;
        })
        ->editColumn('date_added', function($contracts){
            return Carbon::parse($contracts->date_added)->format('M d, Y');
        })
        ->addColumn('action', function ($contracts) {
            $html_out  = '';
            $html_out .= '
            <div class="btn-group">
                <button type="button" class="btn btn-primary btn-xs dropdown-toggle" data-toggle="dropdown">
                    Action &nbsp;&nbsp;
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" id="menu3" aria-labelledby="drop6">
                    <li><a href="javascript:void(0)" onclick="editcontract(\''.hashid($contracts->id).'\')"><i class="fa fa-pencil fa-fw"></i> Edit</a></li>
                    <li><a href="javascript:void(0)" onclick="deletecontract(\''.hashid($contracts->id).'\')"><i class="fa fa-ban fa-fw"></i> Cancel</a></li>
                </ul>
            </div>';
            if($contracts->count_file == 1){
                $html_out .= '&nbsp;&nbsp;<a href="'.url($contracts->dlfile).'" class="btn btn-primary btn-xs" target="_blank"><i class="fa fa-download fa-fw"></i></a>';
            } elseif($contracts->count_file > 1){
                $html_out .= '&nbsp;&nbsp;<a href="javascript:void(0)" onclick="multipledoc(\''.hashid($contracts->id).'\')" class="btn btn-primary btn-xs" target="_blank"><i class="fa fa-download fa-fw"></i></a>';
            } else {
                $html_out .= '&nbsp;&nbsp;<a href="javascript:void(0)" class="btn btn-default btn-xs"><i class="fa fa-download fa-fw"></i></a>';
            }
            return $html_out;
        })->orderColumn('date_added', 'contracts.date_added $1, contracts.id desc')->make(true);
    }

    public function multipledoc($id)
    {
        $id = decode($id);
        $contract = $this->contractRepo->find($id);

        $files = array_filter(explode('xnx', $contract->attach_file));
        $filex = [];
        foreach ($files as $value) {
            $filex[] = $value;
        }

        return ['contract' => $contract, 'files' => $filex, 'arrcount' => count($filex)];
    }

    public function updateremarks(Request $request)
    {
        $this->contractRepo->update(['remarks' => $request['remarks']], $request['id']);
        return 1;
    }
}
