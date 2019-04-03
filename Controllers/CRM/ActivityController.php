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
use QxCMS\Modules\Client\Models\CRM\Activity as Activitys;
use QxCMS\Modules\Client\Models\CRM\ActivityType;
use QxCMS\Modules\Client\Models\CRM\Salutation;
use QxCMS\Modules\Client\Models\CRM\Service;
use QxCMS\Modules\Client\Repositories\CRM\Customer\ActivityRepositoryEloquent as Activity;
use QxCMS\Modules\Client\Repositories\CRM\Customer\CustomerRepositoryEloquent as Customer;
use QxCMS\Modules\Client\Repositories\CRM\Customer\ProposalRepositoryEloquent as Proposal;
use QxCMS\Modules\Likod\User;
use ZanySoft\Zip\Zip;

class ActivityController extends Controller
{
	protected $customerRepo;
	protected $activityRepo;
    protected $proposalrepo;
    protected $role;

	public function __construct(Customer $customerRepo, Activity $activityRepo, Proposal $proposalrepo)
	{
		$this->customerRepo = $customerRepo;
		$this->activityRepo = $activityRepo;
        $this->proposalrepo = $proposalrepo;
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
    	$activities = $this->activityRepo->rawAll($sql,'created_at','desc');
    	return view('Client::crm.activity.index', compact('customer','activityList','activity_types','serviceList','services','activities','salutationList'));
    }

    public function store(Request $request)
    {
    	$customer_id = decode($request['customer_id']);
    	$path = 'uploads/customer/activity';
        $files = \File::files('uploads/temp/'.Auth::guard()->user()->id);

    	if($request['activity_id']){
    		$id = decode($request['activity_id']);
            $activity = $this->activityRepo->find($id);
            
            $attachfiles = $activity->attach_file;
            foreach ($files as $key => $file) {
                $attachfile = Carbon::now()->format('mdYhis').'-'.basename($file);
                // if(\File::move($file, $attachfile)){
                //     $attachfiles .= ($activity->attach_file!='') ? 'xnx'.$attachfile.'xnx' : $attachfile.'xnx';
                // }
                Storage::disk('s3')->put($path.'/'.$attachfile, fopen($file, 'r+'), 'public');
                $attachfiles .= ($activity->attach_file!='') ? 'xnx'.$attachfile.'xnx' : $attachfile.'xnx';
            }

            $makeRequest = [
                'user_id' => Auth::guard('client')->user()->id,
                'customer_id' => $customer_id,
                'activity_type' => $request['activity_type'],
                'next_activity_type' => $request['next_activity_type'],
                'service_id' => $request['service_id'],
                'due_date' => $request['due_date'],
                'assign_to' => ($request['assign_to']!='') ? $request['assign_to'] : Auth::guard('client')->user()->id,
                'fyi' => $request['fyi'],
                'remarks' => $request['remarks'],
                'attach_file' => $attachfiles,
                'file_permission' => $request['file_permission']
            ];

    		$this->activityRepo->update($makeRequest, $id);
    		return ['result' => 1, 'customer_id' => $request['customer_id'], 'message' => ''];
    	}

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
            'activity_type' => $request['activity_type'],
            'next_activity_type' => $request['next_activity_type'],
            'service_id' => $request['service_id'],
            'due_date' => $request['due_date'],
            'assign_to' => ($request['assign_to']!='') ? $request['assign_to'] : Auth::guard('client')->user()->id,
            'fyi' => $request['fyi'],
            'remarks' => $request['remarks'],
            'attach_file' => substr($newfiles, 0, -3),
            'file_permission' => $request['file_permission'],
            'date_added' => Carbon::now()->format('Y-m-d H:i:s')
        ];

    	$activity_id = $this->activityRepo->create($makeRequest);
        
        $activity = $this->activityRepo->findWith($activity_id, ['activitytype','nextactivitytype','assign','customer']);
        $customername = count($activity->customer) ? $activity->customer->name : '';
        $activityname = count($activity->activitytype) ? $activity->activitytype->name : '';
        $nextactivityname = count($activity->nextactivitytype) ? $activity->nextactivitytype->name : '';
        $assignto = count($activity->assign) ? $activity->assign->name : '';
        $assign_email = count($activity->assign) ? $activity->assign->email : '';
        $fyi = '';
        $fyi_email = '';
        if($activity->assign_to || $activity->fyi){
            if($activity->fyi){
                $resultfyis = DB::select("select id,email,name from ".env('DB_DATABASE').".client_users where id in (".$activity->fyi.")");
                foreach ($resultfyis as $resultfyi) {
                    $fyi .= $resultfyi->name.', ';
                    $fyi_email .= $resultfyi->email.',';
                }
            }

            $details  = "<b>Remarks</b>: ".nl2br($request['remarks']).'<br><br>'."";
            $details .= "<b>Activity</b>: ".$activityname."<br/>";
            $details .= "<b>Next Activity</b>: ".$nextactivityname."<br/>";  
            $details .= "<b>Due Date</b>: ".$activity->due_date_display."<br/><br/>";  
            $details .= "<b>Assign To</b>: ".$assignto."<br>";  
            $details .= "<b>FYI</b>: ".substr($fyi, 0, -2)."<br/>";  
            if($activity->attach_file!=''){
                $files = explode('xnx', $activity->attach_file);
                $attachfiles = array_filter($files);
                $path = 'uploads/customer/activity/';
                $details .= 'Attachment(s) : ';
                foreach ($attachfiles as $key => $attachfile) {
                    $details .= '<a href="http://quantumx-crm-bucket.s3.amazonaws.com/'.$path.basename($attachfile).'" target="_blank">'.$attachfile.'</a><br>';
                }
            }
            $details .= '<br><br><b>From: '.Auth::guard('client')->user()->name.'</b>';
        }
        
        try {
            DB::connection('live-mysql')->getPdo();
        } catch (\Exception $e) {
            return ['result' => 1, 'customer_id' => $request['customer_id'], 'message' => 'Unfortunately, email could not be sent due to internet connection.'];
        }

        if($activity->assign_to || $activity->fyi){
            if($activity->assign_to != ''){
                $fyi_email = $fyi_email.','.Auth::guard('client')->user()->email;
            }
            $assign_email = explode(',', $assign_email);
            $fyi_email = explode(',', $fyi_email);
            $ccemails = array_merge($assign_email,$fyi_email);
            $ccemails = array_filter($ccemails);

            foreach ($ccemails as $key => $value) {
                DB::connection('live-mysql')->insert("insert into email_logs (email,subject,details,created_at,sender) values ('".$value."','Activity: ".addslashes($activityname)." - ".addslashes($customername)."','".addslashes($details)."','".Carbon::now()."','".Auth::user()->email."')");
            }
        }

    	return ['result' => 1, 'customer_id' => $request['customer_id'], 'message' => ''];
    }

    public function storeactivitytype(Request $request)
    {
        if($request['activity_id']){
            $id = decode($request['activity_id']);
            if(ActivityType::where('name', $request['name'])->where('id','!=',$id)->where('deletestatus','0')->count()){
                return ['result' => '1'];
            }
            $updateactivity = ActivityType::find($id);
            $updateactivity->name = $request['name'];
            $updateactivity->save();
            return ['id' => $id, 'name' => $request['name'], 'result' => '2', 'hashid' => hashid($id)];
        }

        if(ActivityType::where('name', $request['name'])->where('deletestatus','0')->count()){
            return ['result' => '1'];
        }
        $id = ActivityType::create(['name' => $request['name'], 'user_id' => Auth::user()->id])->id;
        $count = ActivityType::count();
        return ['id' => $id, 'name' => $request['name'], 'count' => $count, 'hashid' => hashid($id)];
    }

    public function editactivity($id)
    {
    	$activity_id = decode($id);
        return ActivityType::find($activity_id);
    }

    public function deleteactivity($id)
    {
    	$activity_id = decode($id);
        $activity = ActivityType::find($activity_id);
        $activity->fill(['deletestatus' => '1']);
        $activity->save();
        return ['id' => $activity_id];
    }

    public function storeservice(Request $request)
    {
        if($request['service_id']){
            $id = decode($request['service_id']);
            if(Service::where('name', $request['name'])->where('id','!=',$id)->count()){
                return ['result' => '1'];
            }
            $updateservice = Service::find($id);
            $updateservice->name = $request['name'];
            $updateservice->save();
            return ['id' => $id, 'name' => $request['name'], 'result' => '2'];
        }

        if(Service::where('name', $request['name'])->count()){
            return ['result' => '1'];
        }
        $id = Service::create(['name' => $request['name'], 'user_id' => Auth::user()->id])->id;
        $count = Service::count();
        return ['id' => $id, 'name' => $request['name'], 'count' => $count, 'hashid' => hashid($id)];
    }

    public function editservice($id)
    {
    	$servicey_id = decode($id);
        return Service::find($servicey_id);
    }

    public function deleteservice($id)
    {
    	$service_id = decode($id);
        $service = Service::find($service_id);
        $service->delete();
        return ['id' => $service_id];
    }

    public function edit($id)
    {
    	$activity_id = decode($id);
    	$activity = $this->activityRepo->find($activity_id);
        $user = DB::table(env('DB_DATABASE').'.client_users')->find($activity->assign_to);
        if($activity->fyi){
            $users = DB::select("select id,name from ".env('DB_DATABASE').".client_users where id in (".$activity->fyi.")");
        } else {
            $users = [];
        }
        
        $files = array_filter(explode('xnx', $activity->attach_file));
        $filex = [];
        foreach ($files as $value) {
            $filex[] = $value;
        }

        return ['activity' => $activity, 'user' => $user, 'users' => $users, 'files' => $filex, 'arrcount' => count($filex)];
    }

    public function deleteact($id)
    {
        $activity_id = decode($id);
        // $activity = $this->activityRepo->find($activity_id);
        // File::delete($activity->attach_file);
        // $this->activityRepo->delete($activity_id);
        $this->activityRepo->update(['deletestatus' => '1'], $activity_id);
        return $activity_id;
    }

    public function removefile(Request $request)
    {
        $id = $request['id'];
        $file = $request['file'];
        $activity = $this->activityRepo->find($id);
        $attachfile = str_replace($file, '', $activity->attach_file);
        $files = explode('xnx', $attachfile);
        $attachfiles = array_filter($files);
        $newfiles = '';
        $path = 'uploads/customer/activity';
        foreach ($attachfiles as $key => $attachfile) {
            $newfiles .= $attachfile.'xnx';
        }

        $directory = 'uploads/temp/'.\Auth::guard()->user()->id;
        File::cleanDirectory($directory);

        // File::delete($file);
        Storage::disk('s3')->delete($path.'/'.$file);

        $this->activityRepo->update(['attach_file' => substr($newfiles, 0, -3)], $id);
        return 1;
    }

    public function storemultipleactivity(Request $request)
    {
        $ids = explode(',', $request['customer_ids']);
        foreach ($ids as $customer_id) {
            $makeRequest = [
                'user_id' => Auth::guard('client')->user()->id,
                'customer_id' => $customer_id,
                'activity_type' => $request['activity_type'],
                'next_activity_type' => $request['next_activity_type'],
                'service_id' => $request['service_id'],
                'due_date' => ($request['due_date']!='') ? Carbon::parse($request['due_date'])->format('Y-m-d') : '',
                'assign_to' => ($request['assign_to']!='') ? $request['assign_to'] : Auth::guard('client')->user()->id,
                'fyi' => $request['fyi'],
                'remarks' => $request['remarks'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'date_added' => Carbon::now()->format('Y-m-d H:i:s')
            ];
            Activitys::insert($makeRequest);
        }

        return 1;
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
        $activity = $this->activityRepo->findWith($id, ['activitytype']);
        $files = array_filter(explode('xnx', $activity->attach_file));
        $filex = [];
        foreach ($files as $value) {
            $filex[] = $value;
        }

        $name = (count($activity->activitytype)) ? str_slug($activity->activitytype->name) : 'Activity';
        $path = 'uploads/compress/';
        $time = date('Ymdhis');
        $zip = Zip::create($path.$name.'-'.$time.'.zip');
        $zip->add($filex);
        $zip->close();
        return response()->download($path.$name.'-'.$time.'.zip', $name.'-'.$time.'.zip', ['Content-Type: application/octet-stream']);
    }

    public function getactivity(Request $request)
    {
        $customer_id = $request['customer_id'];
        $activities = $this->activityRepo->datatablesIndex($customer_id);
        return Datatables::of($activities)
        ->editColumn('activity_type',function($activities){
            if(count($activities->activitytype)){
                return $activities->activitytype->name;
            }
            return '';
        })
        ->editColumn('nextactivitytype',function($activities){
            if(count($activities->nextactivitytype)){
                return $activities->nextactivitytype->name;
            }
            return '';
        })
        ->editColumn('remarks',function($activities){
            return linkreplace($activities->remarks);
        })
        ->setRowId('id')
        ->setRowClass(function ($activities) {
            $activityclass = ($activities->deletestatus == 1) ? 'row-deleted' : '';
            return $activityclass;
        })
        ->addColumn('action', function ($activities) {
            $html_out  = '';
            $html_out .= '
            <div class="btn-group">
                <button type="button" class="btn btn-primary btn-xs dropdown-toggle" data-toggle="dropdown">
                    Action &nbsp;&nbsp;
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" id="menu3" aria-labelledby="drop6" style="min-width: 125px !important;">
                    <li><a href="javascript:void(0)" onclick="editact(\''.hashid($activities->id).'\')"><i class="fa fa-pencil fa-fw"></i> Edit</a></li>
                    <li><a href="javascript:void(0)" onclick="deleteact(\''.hashid($activities->id).'\')"><i class="fa fa-ban fa-fw"></i> Cancel</a></li>
                </ul>
            </div>';
            if($activities->file_permission == 'Only Me' && Auth::guard('client')->user()->id == $activities->user_id){
                if($activities->activitytype->id == 11){
                    if(count($activities->proposal) && $activities->proposal->file!=''){
                        $html_out .= '&nbsp;&nbsp;<a href="'.url($activities->proposal->dlfile).'" class="btn btn-primary btn-xs" target="_blank"><i class="fa fa-download fa-fw"></i></a>';
                    } elseif($activities->count_file == 1){
                        $html_out .= '&nbsp;&nbsp;<a href="'.url($activities->dlfile).'" class="btn btn-primary btn-xs" target="_blank"><i class="fa fa-download fa-fw"></i></a>';
                    } elseif($activities->count_file > 1){
                        $html_out .= '&nbsp;&nbsp;<a href="javascript:void(0)" onclick="activitydoc(\''.hashid($activities->id).'\',1)" class="btn btn-primary btn-xs" target="_blank"><i class="fa fa-download fa-fw"></i></a>';
                    } elseif(count($activities->proposal) && $activities->proposal->count_file > 1){
                        $html_out .= '&nbsp;&nbsp;<a href="javascript:void(0)" onclick="activitydoc(\''.hashid($activities->proposal->id).'\',2)" class="btn btn-primary btn-xs" target="_blank"><i class="fa fa-download fa-fw"></i></a>';
                    } else {
                        $html_out .= '&nbsp;&nbsp;<a href="javascript:void(0)" class="btn btn-default btn-xs"><i class="fa fa-download fa-fw"></i></a>';
                    }
                } else {
                    if($activities->count_file == 1){
                        $html_out .= '&nbsp;&nbsp;<a href="'.url($activities->dlfile).'" class="btn btn-primary btn-xs" target="_blank"><i class="fa fa-download fa-fw"></i></a>';
                    } elseif($activities->count_file > 1){
                        $html_out .= '&nbsp;&nbsp;<a href="javascript:void(0)" onclick="activitydoc(\''.hashid($activities->id).'\',1)" class="btn btn-primary btn-xs" target="_blank"><i class="fa fa-download fa-fw"></i></a>';
                    } else{
                        $html_out .= '&nbsp;&nbsp;<a href="javascript:void(0)" class="btn btn-default btn-xs"><i class="fa fa-download fa-fw"></i></a>';
                    }
                }
            }
            if($activities->file_permission == 'Everyone'){
                if(count($activities->activitytype)){
                    if($activities->activitytype->id == 11){
                        if(count($activities->proposal) && $activities->proposal->count_file == 1){
                            $html_out .= '&nbsp;&nbsp;<a href="'.url($activities->proposal->dlfile).'" class="btn btn-primary btn-xs" target="_blank"><i class="fa fa-download fa-fw"></i></a>';
                        } elseif($activities->count_file == 1){
                            $html_out .= '&nbsp;&nbsp;<a href="'.url($activities->dlfile).'" class="btn btn-primary btn-xs" target="_blank"><i class="fa fa-download fa-fw"></i></a>';
                        } elseif($activities->count_file > 1){
                            $html_out .= '&nbsp;&nbsp;<a href="javascript:void(0)" onclick="activitydoc(\''.hashid($activities->id).'\',1)" class="btn btn-primary btn-xs" target="_blank"><i class="fa fa-download fa-fw"></i></a>';
                        } elseif(count($activities->proposal) && $activities->proposal->count_file > 1){
                            $html_out .= '&nbsp;&nbsp;<a href="javascript:void(0)" onclick="activitydoc(\''.hashid($activities->proposal->id).'\',2)" class="btn btn-primary btn-xs" target="_blank"><i class="fa fa-download fa-fw"></i></a>';
                        } else {
                            $html_out .= '&nbsp;&nbsp;<a href="javascript:void(0)" class="btn btn-default btn-xs"><i class="fa fa-download fa-fw"></i></a>';
                        }
                    } else {
                        if($activities->count_file == 1){
                            $html_out .= '&nbsp;&nbsp;<a href="'.url($activities->dlfile).'" class="btn btn-primary btn-xs" target="_blank"><i class="fa fa-download fa-fw"></i></a>';
                        } elseif($activities->count_file > 1){
                            $html_out .= '&nbsp;&nbsp;<a href="javascript:void(0)" onclick="activitydoc(\''.hashid($activities->id).'\',1)" class="btn btn-primary btn-xs" target="_blank"><i class="fa fa-download fa-fw"></i></a>';
                        } else {
                            $html_out .= '&nbsp;&nbsp;<a href="javascript:void(0)" class="btn btn-default btn-xs"><i class="fa fa-download fa-fw"></i></a>';
                        }
                    }
                }
            }
            if($activities->file_permission == ''){
                if(count($activities->activitytype)){
                    if($activities->activitytype->id == 11){
                        if(count($activities->proposal) && $activities->proposal->count_file == 1){
                            $html_out .= '&nbsp;&nbsp;<a href="'.url($activities->proposal->dlfile).'" class="btn btn-primary btn-xs" target="_blank"><i class="fa fa-download fa-fw"></i></a>';
                        } elseif($activities->count_file == 1){
                            $html_out .= '&nbsp;&nbsp;<a href="'.url($activities->dlfile).'" class="btn btn-primary btn-xs" target="_blank"><i class="fa fa-download fa-fw"></i></a>';
                        } elseif($activities->count_file > 1){
                            $html_out .= '&nbsp;&nbsp;<a href="javascript:void(0)" onclick="activitydoc(\''.hashid($activities->id).'\',1)" class="btn btn-primary btn-xs" target="_blank"><i class="fa fa-download fa-fw"></i></a>';
                        } elseif(count($activities->proposal) && $activities->proposal->count_file > 1){
                            $html_out .= '&nbsp;&nbsp;<a href="javascript:void(0)" onclick="activitydoc(\''.hashid($activities->proposal->id).'\',2)" class="btn btn-primary btn-xs" target="_blank"><i class="fa fa-download fa-fw"></i></a>';
                        } else {
                            $html_out .= '&nbsp;&nbsp;<a href="javascript:void(0)" class="btn btn-default btn-xs"><i class="fa fa-download fa-fw"></i></a>';
                        }
                    } else {
                        if($activities->count_file == 1){
                            $html_out .= '&nbsp;&nbsp;<a href="'.url($activities->dlfile).'" class="btn btn-primary btn-xs" target="_blank"><i class="fa fa-download fa-fw"></i></a>';
                        } elseif($activities->count_file > 1){
                            $html_out .= '&nbsp;&nbsp;<a href="javascript:void(0)" onclick="activitydoc(\''.hashid($activities->id).'\',1)" class="btn btn-primary btn-xs" target="_blank"><i class="fa fa-download fa-fw"></i></a>';
                        } else {
                            $html_out .= '';
                        }
                    }
                }
            }
            return $html_out;
        })->orderColumn('date_added', 'activities.date_added $1, activities.id desc')->make(true);
    }

    public function getactivityoverview(Request $request)
    {
        $customer_id = $request['customer_id'];
        $activities = $this->activityRepo->datatablesIndex($customer_id);
        return Datatables::of($activities)
        ->editColumn('customer',function($activities){
            if(count($activities->customer)){
                return $activities->customer->name;
            }
            return '';
        })
        ->editColumn('activity_type',function($activities){
            if(count($activities->activitytype)){
                return $activities->activitytype->name;
            }
            return '';
        })
        ->editColumn('assign',function($activities){
            if(count($activities->assign)){
                return $activities->assign->name;
            }
            return '';
        })
        ->make(true);
    }

    public function multipledoc($id)
    {
        $id = decode($id);
        $activity = $this->activityRepo->find($id);

        $files = array_filter(explode('xnx', $activity->attach_file));
        $filex = [];
        foreach ($files as $value) {
            $filex[] = $value;
        }

        return ['activity' => $activity, 'files' => $filex, 'arrcount' => count($filex)];
    }

    public function getreminders(Request $request)
    {
        $assign_to = $request['assign_to'];
        $date_type = $request['date_type'];
        $activities = $this->activityRepo->datatablesIndex2($assign_to, $date_type);
        return Datatables::of($activities)
        ->editColumn('customer',function($activities){
            if(count($activities->customer)){
                return $activities->customer->name;
            }
            return '';
        })
        ->editColumn('activity_type',function($activities){
            if(count($activities->activitytype)){
                return $activities->activitytype->name;
            }
            return '';
        })
        ->editColumn('nextactivitytype',function($activities){
            if(count($activities->nextactivitytype)){
                return $activities->nextactivitytype->name;
            }
            return '';
        })
        ->editColumn('assign',function($activities){
            if(count($activities->assign)){
                return $activities->assign->name;
            }
            return '';
        })
        ->addColumn('action', function ($activities) {
            $html_out  = '';
            if(count($activities->customer)){
            $html_out .= '<a href="javascript:void(0)" onclick="createactivity(\''.$activities->customer->hashid.'\')" class="btn btn-primary btn-sm" data-toggle="tooltip" data-placement="top" title="Add New Activity"><i class="fa fa-plus-square"></i></a>&nbsp;';
            }
            $html_out .= '<a href="javascript:void(0)" id="btn-'.$activities->id.'" onclick="donetask('.$activities->id.')" class="btn btn-sm btn-success">Done</a>';
            return $html_out;
        })
        ->setRowId(function ($activities) {
            return 'list-'.$activities->id;
        })
        ->make(true);
    }
}
