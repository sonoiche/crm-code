<?php

namespace QxCMS\Modules\Client\Controllers\CRM;

use Carbon\Carbon;
use Datatables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use QxCMS\Http\Controllers\Controller;
use QxCMS\Modules\Client\Models\CRM\Activity as Activitys;
use QxCMS\Modules\Client\Models\CRM\ActivityType;
use QxCMS\Modules\Client\Models\CRM\PaymentOR;
use QxCMS\Modules\Client\Models\CRM\Salutation;
use QxCMS\Modules\Client\Models\CRM\Service;
use QxCMS\Modules\Client\Repositories\CRM\Customer\ActivityRepositoryEloquent as Activity;
use QxCMS\Modules\Client\Repositories\CRM\Customer\CustomerRepositoryEloquent as Customer;
use QxCMS\Modules\Client\Repositories\CRM\Customer\PaymentRepositoryEloquent as Payment;
use QxCMS\Modules\Client\Repositories\CRM\Tracker\TrackerRepositoryEloquent as Tracker;
use ZanySoft\Zip\Zip;

class PaymentController extends Controller
{
	protected $customerRepo;
	protected $activityRepo;
    protected $paymentRepo;
    protected $trackerRepo;

    public function __construct(Customer $customerRepo, Activity $activityRepo, Payment $paymentRepo, Tracker $trackerRepo)
    {
    	$this->paymentRepo = $paymentRepo;
    	$this->customerRepo = $customerRepo;
		$this->activityRepo = $activityRepo;
        $this->trackerRepo = $trackerRepo;
    }

    public function index($id)
    {
    	$id = decode($id);
    	$sql = "customer_id = '".$id."'";
    	$customer = $this->customerRepo->find($id);
        $salutationList = [''=>'--']+Salutation::orderBy('name')->pluck('name', 'name')->toArray();
    	$activityList = [''=>'--']+ActivityType::where('deletestatus','0')->where('status','0')->orderBy('name')->pluck('name', 'id')->toArray();
    	$serviceList = [''=>'--']+ActivityType::where('invoice','0')->where('deletestatus','0')->where('status','0')->orderBy('name')->pluck('name', 'id')->toArray();
    	$activity_types = ActivityType::where('deletestatus','0')->where('status','0')->orderBy('name')->get();
    	$services = ActivityType::where('invoice','0')->where('deletestatus','0')->where('status','0')->orderBy('name')->get();
    	$statusList = [''=>'--','Unpaid'=>'Unpaid','Paid'=>'Paid','Cancelled'=>'Cancelled'];
    	$payments = $this->paymentRepo->rawWith(['paymentor'], $sql, 'id','desc');

        $from = date('Y', strtotime("-5 year"));
        $to = date('Y', strtotime("+2 year"));
        $year = date('Y');

    	return view('Client::crm.payment.index', compact('customer','activityList','activity_types','serviceList','services','statusList','payments','from','to','year','salutationList'));
    }

    public function store(Request $request)
    {
    	if($request['id']){
    		$makeRequest = [
    			'payment_id' => $request['id'],
	    		'due_date' => $request['due_date'],
	    		'date_bill' => $request['date_bill'],
	    		'date_paid' => $request['date_paid'],
	    		'amount' => str_replace([',',' '], ['',''], $request['amount']),
	    		'pr_number' => $request['pr_number'],
                'or_number' => $request['or_number']
    		];
    		PaymentOR::create($makeRequest);
    		if($request['addnother']){
	    		return $payment->id;
	    	}
	    	return 1;
    	}

    	$path = 'uploads/customer/payments';
    	$files = \File::files('uploads/temp/'.Auth::guard()->user()->id);

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
    		'customer_id' => decode($request['customer_id']),
    		'title' => $request['title'],
    		'service_id' => $request['service_id'],
    		'status' => $request['status'],
    		'cert' => ($request['cert'] == 'on') ? '1' : '',
    		'details' => $request['details'],
    		'attachment' => substr($newfiles, 0, -3),
    		'file_permission' => $request['file_permission'],
            'date_added' => Carbon::now()->format('Y-m-d H:i:s'),
            'fyi' => $request['fyi']
    	];

    	$payment = $this->paymentRepo->create($makeRequest);
        PaymentOR::create([
            'payment_id' => $payment->id,
            'due_date' => $request['due_date'],
            'date_bill' => $request['date_bill'],
            'date_paid' => $request['date_paid'],
            'amount' => str_replace([',',' '], ['',''], $request['amount']),
            'pr_number' => $request['pr_number'],
            'or_number' => $request['or_number']
        ]);

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

        $fyi = '';
        $fyi_email = '';
        $pay = $this->paymentRepo->find($payment->id);
        $invoicename = $pay->title;
        $customername = count($pay->customer) ? $pay->customer->name : '';
        if($pay->fyi!=''){
            $resultfyis = DB::select("select id,email,name from ".env('DB_DATABASE').".client_users where id in (".$pay->fyi.")");;
            foreach ($resultfyis as $resultfyi) {
                $fyi .= $resultfyi->name.', ';
                $fyi_email .= $resultfyi->email.',';
            }

            $details  = "<b>Remarks</b>: ".nl2br($request['details']).'<br><br>'."";
            $details .= "<b>Title</b>: ".$pay->title."<br/>";
            $details .= "<b>Amount</b>: ".$pay->totalamount."<br/>";
            $details .= "<b>FYI</b>: ".substr($fyi, 0, -1)."<br/>";  
            if($pay->attachment!=''){
                $attachfile = str_replace($file, '', $pay->attachment);
                $files = explode('xnx', $attachfile);
                $attachfiles = array_filter($files);
                $path = 'uploads/customer/payments/';
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

        if($pay->fyi){
            $fyi_email = $fyi_email.','.Auth::guard('client')->user()->email;
            $fyi_email = explode(',', $fyi_email);
            $ccemails = array_filter($fyi_email);

            foreach ($ccemails as $key => $value) {
                DB::connection('live-mysql')->insert("insert into email_logs (email,subject,details,created_at,sender) values ('".$value."','Invoice: ".addslashes($invoicename)." - ".addslashes($customername)."','".addslashes($details)."','".Carbon::now()."','".Auth::user()->email."')");
            }
        }

        $actRequest = [
            'user_id' => Auth::guard('client')->user()->id,
            'customer_id' => decode($request['customer_id']),
            'activity_type' => '68',
            'assign_to' => Auth::guard('client')->user()->id,
            'remarks' => 'Create new invoice '.$request['details'],
            'attach_file' => substr($newfilesx, 0, -3),
            'file_permission' => 'Everyone',
            'date_added' => Carbon::now()->format('Y-m-d H:i:s')
        ];
        $this->activityRepo->create($actRequest);

    	if($request['addnother']){
    		return $payment->id;
    	}

    	return ['response' => 1, 'message' => ''];
    }

    public function edit($id)
    {
    	$id = decode($id);
    	$payment = $this->paymentRepo->findWith($id, ['paymentorfirst']);
        $files = array_filter(explode('xnx', $payment->attachment));
        $filex = [];
        foreach ($files as $value) {
            $filex[] = $value;
        }

        $user = DB::table(env('DB_DATABASE').'.client_users')->find($payment->fyi);
        if($payment->fyi){
            $users = DB::select("select id,name from ".env('DB_DATABASE').".client_users where id in (".$payment->fyi.")");
        } else {
            $users = [];
        }

        return ['payment' => $payment, 'files' => $filex, 'arrcount' => count($filex), 'users' => $users];
    }

    public function removefile(Request $request)
    {
        $path = 'uploads/customer/payments';
        $id = $request['id'];
        $file = $request['file'];
        $payment = $this->paymentRepo->find($id);
        $attachfile = str_replace($file, '', $payment->attachment);
        $files = explode('xnx', $attachfile);
        $attachfiles = array_filter($files);
        $newfiles = '';
        foreach ($attachfiles as $key => $attachfile) {
            $newfiles .= $attachfile.'xnx';
        }

        $directory = 'uploads/temp/'.\Auth::guard()->user()->id;
        File::cleanDirectory($directory);

        // File::delete($file);
        Storage::disk('s3')->delete($path.'/'.$file);

        $this->paymentRepo->update(['attachment' => substr($newfiles, 0, -3)], $id);
        return 1;
    }

    public function updatepayment(Request $request)
    {
    	$id = decode($request['id']);
    	$payment = $this->paymentRepo->find($id);
    	$path = 'uploads/customer/payments';
    	$files = \File::files('uploads/temp/'.Auth::guard()->user()->id);

        $attachfiles = $payment->attachment;
        foreach ($files as $key => $file) {
            $attachfile = Carbon::now()->format('mdYhis').'-'.basename($file);
            // if(\File::move($file, $attachfile)){
            //     $attachfiles .= ($payment->attachment!='') ? 'xnx'.$attachfile.'xnx' : $attachfile.'xnx';
            // }
            Storage::disk('s3')->put($path.'/'.$attachfile, fopen($file, 'r+'), 'public');
            $attachfiles .= ($payment->attachment!='') ? 'xnx'.$attachfile.'xnx' : $attachfile.'xnx';
        }

    	$makeRequest = [
    		'title' => $request['title'],
    		'service_id' => $request['service_id'],
    		'status' => $request['status'],
    		'cert' => ($request['cert'] == 'on') ? '1' : '',
    		'details' => $request['details'],
    		'attachment' => $attachfiles,
    		'file_permission' => $request['file_permission'],
            'fyi' => $request['fyi']
    	];
    	$this->paymentRepo->update($makeRequest, $id);
        $chk = PaymentOR::where('payment_id', $id)->count();
        if($chk != '0'){
            PaymentOR::where('payment_id', $id)->limit(1)->update([
                'date_paid' => ($request['date_paid']) ? Carbon::parse($request['date_paid'])->format('Y-m-d') : '',
                'due_date' => ($request['due_date']) ? Carbon::parse($request['due_date'])->format('Y-m-d') : '',
                'amount' => $request['amount'],
                'pr_number' => $request['pr_number'], 
                'or_number' => $request['or_number'],
                'tracker_id' => ((strpos($payment->title, 'Iris Payment') !== false) && $payment->title == $payment->details) ? 1 : ''
            ]);
        } else {
            PaymentOR::create([
                'date_paid' => ($request['date_paid']) ? Carbon::parse($request['date_paid'])->format('Y-m-d') : '',
                'due_date' => ($request['due_date']) ? Carbon::parse($request['due_date'])->format('Y-m-d') : '',
                'amount' => $request['amount'],
                'pr_number' => $request['pr_number'],
                'or_number' => $request['or_number'],
                'payment_id' => $id,
                'date_bill' => Carbon::now()->format('Y-m-d'),
                'tracker_id' => ((strpos($payment->title, 'Iris Payment') !== false) && $payment->title == $payment->details) ? 1 : ''
            ]);
        }

        if($request['status'] == 'Paid'){
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
                'customer_id' => $request['customer_id'],
                'activity_type' => '68',
                'assign_to' => Auth::guard('client')->user()->id,
                'remarks' => 'Payment Paid : '.$request['details'],
                'attach_file' => substr($newfilesx, 0, -3),
                'file_permission' => 'Everyone',
                'date_added' => Carbon::now()->format('Y-m-d H:i:s')
            ];
            $this->activityRepo->create($actRequest);

            $fyi = '';
            $fyi_email = '';
            $pay = $this->paymentRepo->findWith($id, ['customer','service']);
            $invoicename = count($pay->service) ? $pay->service->name : '';
            $customername = count($pay->customer) ? $pay->customer->name : '';
            if($pay->fyi!=''){
                $resultfyis = DB::select("select id,email,name from ".env('DB_DATABASE').".client_users where id in (".$pay->fyi.")");;
                foreach ($resultfyis as $resultfyi) {
                    $fyi .= $resultfyi->name.', ';
                    $fyi_email .= $resultfyi->email.',';
                }

                $details  = "<b>Remarks</b>: ".nl2br($request['details']).'<br><br>'."";
                $details .= "<b>Title</b>: ".$pay->title."<br/>";
                $details .= "<b>Amount</b>: ".$pay->totalamount."<br/>";
                $details .= "<b>FYI</b>: ".substr($fyi, 0, -1)."<br/>";  
                if($pay->attachment!=''){
                    $attachfile = $pay->attachment;
                    $files = explode('xnx', $attachfile);
                    $attachfiles = array_filter($files);
                    $path = 'uploads/customer/payments/';
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

            if($pay->fyi){
                $fyi_email = $fyi_email.','.Auth::guard('client')->user()->email;
                $fyi_email = explode(',', $fyi_email);
                $ccemails = array_filter($fyi_email);

                foreach ($ccemails as $key => $value) {
                    DB::connection('live-mysql')->insert("insert into email_logs (email,subject,details,created_at,sender) values ('".$value."','Payment Made: ".addslashes($invoicename)." - ".addslashes($customername)."','".addslashes($details)."','".Carbon::now()."','".Auth::user()->email."')");
                }
            }
        }

    	return ['response' => 1, 'message' => ''];
    }

    public function getornumber($id)
    {
    	$id = decode($id);
    	$invoice = $this->paymentRepo->find($id);
    	$payments = PaymentOR::where('payment_id', $id)->orderBy('date_paid')->get();
    	return ['payments' => $payments, 'invoice' => $invoice];
    }

    public function editornumber($id)
    {
    	return PaymentOR::find($id);
    }

    public function storeornumber(Request $request)
    {
    	$payment_id = decode($request['payment_id']);
        $payment = $this->paymentRepo->find($payment_id);
    	$makeRequest = [
    		'payment_id' => $payment_id,
            'customer_id' => $payment->customer_id,
    		'due_date' => Carbon::now()->format('Y-m-d'),
    		'date_bill' => Carbon::now()->format('Y-m-d'),
    		'date_paid' => $request['date_paid'],
    		'amount' => str_replace([',',' '], ['',''], $request['amount']),
            'or_number' => $request['or_number'],
    		'pr_number' => $request['pr_number'],
            'tracker_id' => ((strpos($payment->title, 'Iris Payment') !== false) && $payment->title == $payment->details) ? 1 : ''
    	];

    	if($request['id']){
    		$payment = PaymentOR::find($request['id']);
    		$payment->fill($makeRequest);
    		$payment->save();

            if($payment->tracker_id!='0'){
                $this->trackerRepo->update([
                    'or_number' => $request['or_number'],
                    'amount' => str_replace([',',' '], ['',''], $request['amount']),
                    'payment_date' => $request['date_paid']
                ], $payment->tracker_id);
            }

    		return ['response' => 2, 'payment' => $payment];
    	}

    	$payment = PaymentOR::create($makeRequest);
    	return ['response' => 1, 'payment' => $payment];
    }

    public function deletepayment($id)
    {
        $payment_id = decode($id);
        $this->paymentRepo->update(['deletestatus' => '1', 'status' => 'Cancelled'], $payment_id);
        return ['id' => $payment_id, 'response' => 1];
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
        $payment = $this->paymentRepo->find($id);
        $files = array_filter(explode('xnx', $payment->attachment));
        $filex = [];
        foreach ($files as $value) {
            $filex[] = $value;
        }

        $path = 'uploads/compress/';
        $time = date('Ymdhis');
        $zip = Zip::create($path.str_slug($payment->title).'-'.$time.'.zip');
        $zip->add($filex);
        $zip->close();
        return response()->download($path.str_slug($payment->title).'-'.$time.'.zip', str_slug($payment->title).'-'.$time.'.zip', ['Content-Type: application/octet-stream']);
    }

    public function getpayment(Request $request)
    {
        $customer_id = $request['customer_id'];
        $payments = $this->paymentRepo->datatablesIndex($customer_id);
        return Datatables::of($payments)
        ->editColumn('service',function($payments){
            if(count($payments->service)){
                return $payments->service->name;
            }
            return '';
        })
        ->editColumn('user',function($payments){
            if(count($payments->user)){
                return $payments->user->name;
            }
            return '';
        })
        ->editColumn('date_bill',function($payments){
            if(count($payments->paymentor)){
                return $payments->paymentor[0]->date_bill;
            }
            return '';
        })
        ->setRowId('id')
        ->setRowClass(function ($payments) {
            $paymentclass = ($payments->deletestatus == 1) ? 'row-deleted' : '';
            return $paymentclass;
        })
        ->addColumn('action', function ($payments) {
            $html_out  = '';
            $html_out .= '
            <div class="btn-group">
                <button type="button" class="btn btn-primary btn-xs dropdown-toggle" data-toggle="dropdown">
                    Action &nbsp;&nbsp;
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" id="menu3" aria-labelledby="drop6" style="min-width: 125px !important;">
                    <li><a href="javascript:void(0)" onclick="viewor(\''.hashid($payments->id).'\')"><i class="fa fa-search fa-fw"></i> View OR</a></li>
                    <li><a href="javascript:void(0)" onclick="editpaymentpaid(\''.hashid($payments->id).'\')"><i class="fa fa-money fa-fw"></i> Payment</a></li>
                    <li><a href="javascript:void(0)" onclick="editpayment(\''.hashid($payments->id).'\')"><i class="fa fa-pencil fa-fw"></i> Edit</a></li>
                    <li><a href="javascript:void(0)" onclick="deletepayment(\''.hashid($payments->id).'\')"><i class="fa fa-ban fa-fw"></i> Cancel</a></li>
                </ul>
            </div>';
            if($payments->count_file == 1){
                $html_out .= '&nbsp;&nbsp;<a href="'.url($payments->dlfile).'" class="btn btn-primary btn-xs" target="_blank"><i class="fa fa-download fa-fw"></i></a>';
            } elseif($payments->count_file > 1){
                $html_out .= '&nbsp;&nbsp;<a href="'.url('client/crm/customer',hashid($payments->id)).'/zippayment" class="btn btn-primary btn-xs" target="_blank"><i class="fa fa-download fa-fw"></i></a>';
            } else {
                $html_out .= '&nbsp;&nbsp;<a href="javascript:void(0)" class="btn btn-default btn-xs"><i class="fa fa-download fa-fw"></i></a>';
            }
            return $html_out;
        })->make(true);
    }

    public function multipledoc($id)
    {
        $id = decode($id);
        $payment = $this->paymentRepo->find($id);

        $files = array_filter(explode('xnx', $payment->attachment));
        $filex = [];
        foreach ($files as $value) {
            $filex[] = $value;
        }

        return ['payment' => $payment, 'files' => $filex, 'arrcount' => count($filex)];
    }

    public function deleteor($id)
    {
        $or = PaymentOR::find($id);
        $or->delete();

        return $id;
    }

    public function saveornumber(Request $request)
    {
        $id = $request['id'];
        $or = PaymentOR::find($id);
        $or->fill(['or_number' => $request['or_number']]);
        $or->save();

        return 1;
    }

    public function saveprnumber(Request $request)
    {
        $id = $request['id'];
        $pr = PaymentOR::find($id);
        $pr->fill(['pr_number' => $request['pr_number']]);
        $pr->save();

        return 1;
    }

    public function updatepr(Request $request)
    {
        $id = $request['id'];
        $pr = PaymentOR::find($id);
        $pr->fill(['pr_number' => $request['pr_number']]);
        $pr->save();

        return 1;
    }

    public function updateor(Request $request)
    {
        $id = $request['id'];
        $or = PaymentOR::find($id);
        $or->fill(['or_number' => $request['or_number']]);
        $or->save();

        return 1;
    }
}
