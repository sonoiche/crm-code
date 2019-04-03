<?php

namespace QxCMS\Modules\Client\Controllers\CRM\Reports;

use Carbon\Carbon;
use Datatables;
use File;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use QxCMS\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use QxCMS\Modules\Likod\Models\Clients\User;
use QxCMS\Modules\Client\Models\CRM\ActivityType;
use QxCMS\Modules\Client\Models\CRM\ProposalApproval;
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
        $statusList = [''=>'--','Sent'=>'Sent','Won'=>'Won','Lost'=>'Lost','Cancelled'=>'Cancelled'];
        $chanceList = [''=>'--',0=>'0',25=>'25',50=>'50',75=>'75',100=>'100'];
        $userList = [''=>'--']+User::where('status',1)->whereIn('role_id',['1','4','5'])->orderBy('name')->pluck('name', 'id')->toArray();
        $productList = [''=>'--']+ActivityType::where('product','0')->where('deletestatus','0')->where('status','0')->orderBy('name')->pluck('name', 'id')->toArray();

        $directory = 'uploads/temp/'.Auth::guard()->user()->id;
        File::cleanDirectory($directory);
        $productarray = [1,2,3,4,5];

    	return view('Client::crm.reports.proposal.approval.index', compact('statusList','chanceList','productList','productarray','userList'));
    }

    public function store(Request $request)
    {
    	$added_date_range = $request['date_added'];
        $added_date = explode('-', $added_date_range);
        $added_from = Carbon::parse($added_date[0])->format('Y-m-d');
        $added_to = Carbon::parse($added_date[1])->format('Y-m-d');
        $from_display = Carbon::parse($added_date[0])->format('d F Y');
        $to_display = Carbon::parse($added_date[1])->format('d F Y');

        if($_POST['product_id']){
            $product = $_POST['product_id'];
            $sql_product = "and product_id = '".$product."'";
        } else {
            $product = '0x0';
            $sql_product = '';
        }

        if($_POST['status']){
            $status = $_POST['status'];
            $sql_status = "and proposal_approval.status = '".$status."'";
        } else {
            $status = '0x0';
            $sql_status = '';
        }

        if($_POST['chances']){
            $chance = $_POST['chances'];
            $sql_chance = "and pro_chance = '".$chance."'";
        } else {
            $sql_chance = '';
            $chance = '0x0';
        }

        if($_POST['approver']){
            $approver = $_POST['approver'];
            $sql_approver = "and approver = '".$approver."'";
        } else {
            $sql_approver = '';
            $approver = '0x0';
        }

        $sql_proposal = '';
        if(isset($_POST['proposal_status'])){
            $proposalstatus = $_POST['proposal_status'];
            $sql_proposal = "and pro_status in ('".implode("','", $_POST['proposal_status'])."')";
        } else {
            $proposalstatus = '0x0';
        }

        $sql = "date(proposal_approval.created_at) >= '".$added_from."' and date(proposal_approval.created_at) <= '".$added_to."' $sql_product $sql_status $sql_chance $sql_proposal $sql_approver";
        $proposals = $this->proposalRepo->rawWith(['proversionfirst','customer','activitytype','userapprover'], $sql);
        
        $link = 'proposalapproval/'.$added_from.'xxx'.$added_to.'xxx'.$product.'xxx'.$status.'xxx'.$chance.'xxx'.implode(',', $proposalstatus).'xxx'.$approver;
        return ['proposals' => $proposals, 'from_display' => $from_display, 'to_display' => $to_display, 'link' => $link, 'totalcount' => count($proposals)];
    }

    public function show($datas)
    {
        $data = explode('xxx', $datas);
        $added_from = Carbon::parse($data[0])->format('Y-m-d');
        $added_to = Carbon::parse($data[1])->format('Y-m-d');
        $from_display = Carbon::parse($data[0])->format('d F Y');
        $to_display = Carbon::parse($data[1])->format('d F Y');

        $sqlproduct = '';
        if($data[2] != '0x0'){
            $product = $data[2];
            $sqlproduct = "and proposal_approval.product_id = '".$product."'";
        }

        $sqlstatus = '';
        if($data[3] != '0x0'){
            $status = $data[3];
            $sqlstatus = "and proposal_approval.status = '".$status."'";
        }

        $sqlchance = '';
        if($data[4] != '0x0'){
            $chance = $data[4];
            $sqlchance = "and proposal_approval.pro_chance = '".$chance."'";
        }

        $sqlproposalstatus = '';
        if($data[5] != '0x0'){
            $proposalstatus = $data[5];
            $sqlproposalstatus = "and proposal_approval.pro_status in ('".str_replace(",","','",$proposalstatus)."')";
        }

        $sqlapprover = '';
        if($data[6] != '0x0'){
            $approver = $data[6];
            $sqlapprover = "and proposal_approval.approver = '".$approver."'";
        }

        if(isset($data[8]) && $data[8] == 'ascending'){
            $sort = $data[9];
            $sortorder = 'asc';
        } else if(isset($data[8]) && $data[8] == 'descending'){
            $sort = $data[9];
            $sortorder = 'desc';
        } else {
            $sort = 'proposal_approval.created_at';
            $sortorder = 'asc';
        }

        $sql = "date(proposal_approval.created_at) >= '".$added_from."' and date(proposal_approval.created_at) <= '".$added_to."' $sqlproduct $sqlstatus $sqlchance $sqlproposalstatus $sqlapprover";
        $proposals = $this->proposalRepo->rawWith(['proversionfirst','customer','activitytype','userapprover'], $sql);

        if($data[7] == 'csv'){
            Excel::create('Proposal Approval Report '.$from_display.' - '.$to_display, function($excel) use ($proposals, $from_display, $to_display) {
                $excel->sheet('New sheet', function($sheet) use ($proposals, $from_display, $to_display) {
                    $sheet->loadView('Client::crm.reports.proposal.approval.excel', compact('proposals','from_display','to_display'));
                });
            })->download('csv');
        }
    }
}
