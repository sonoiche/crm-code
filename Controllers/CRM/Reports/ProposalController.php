<?php

namespace QxCMS\Modules\Client\Controllers\CRM\Reports;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use QxCMS\Http\Controllers\Controller;
use QxCMS\Modules\Client\Models\CRM\ActivityType;
use QxCMS\Modules\Client\Models\CRM\Proposal as Proposals;
use QxCMS\Modules\Client\Repositories\CRM\Customer\ProposalRepositoryEloquent as Proposal;

class ProposalController extends Controller
{
    protected $proposalRepo;

    public function __construct(Proposal $proposalRepo)
    {
    	$this->proposalRepo = $proposalRepo;
    }

    public function index()
    {
    	$statusList = [''=>'All Status','Sent'=>'Sent','Won'=>'Won','Lost'=>'Lost','Cancelled'=>'Cancelled'];
    	return view('Client::crm.reports.proposal.index', compact('statusList'));
    }

    public function store(Request $request)
    {
    	$added_date_range = $request['date_added'];
        $added_date = explode('-', $added_date_range);
        $added_from = Carbon::parse($added_date[0])->format('Y-m-d');
        $added_to = Carbon::parse($added_date[1])->format('Y-m-d');
        $from_display = Carbon::parse($added_date[0])->format('d F Y');
        $to_display = Carbon::parse($added_date[1])->format('d F Y');

        $sqlstatus = '';
        if($request['status']){
	    	$status = $request['status'];
	    	$sqlstatus = "and proposals.status = '".$status."'";
	    } else {
	    	$status = '0x0';
	    }

        $sql = "date(proposals.created_at) >= '".$added_from."' and date(proposals.created_at) <= '".$added_to."' and proposals.deletestatus = '0' $sqlstatus";
	    // $proposals = $this->proposalRepo->rawWith(['customer'], $sql, 'created_at');
	    $proposals = Proposals::leftJoin('customers','proposals.customer_id','=','customers.id')
	    					  ->leftJoin(env('DB_DATABASE').'.client_users','proposals.user_id','=',env('DB_DATABASE').'.client_users.id')
	    					  ->select('proposals.created_at','customers.name','proposals.amount','proposals.status','proposals.remarks',env('DB_DATABASE').'.client_users.name as username','proposals.file','proposals.id')
	    					  ->whereRaw($sql)
	    					  ->orderBy('proposals.created_at','desc')
	    					  ->get();
	    $total = Proposals::selectRaw("SUM(amount) as total, amount, created_at, customer_id, file")->whereRaw($sql)->first();
	    $link = 'proposal/'.$added_from.'xxx'.$added_to.'xxx'.$status;
	    return ['proposals' => $proposals, 'from_display' => $from_display, 'to_display' => $to_display, 'link' => $link, 'totalamount' => number_format($total->total,2), 'totalcount' => count($proposals)];
    }

    public function show($datas)
    {
    	$data = explode('xxx', $datas);
        $added_from = Carbon::parse($data[0])->format('Y-m-d');
        $added_to = Carbon::parse($data[1])->format('Y-m-d');
        $from_display = Carbon::parse($data[0])->format('d F Y');
        $to_display = Carbon::parse($data[1])->format('d F Y');

        $sqlstatus = '';
        if($data[2] != '0x0'){
	    	$status = $data[2];
	    	$sqlstatus = "and proposals.status = '".$status."'";
	    }

	    if(isset($data[4]) && $data[4] == 'ascending'){
            $sort = $data[5];
            $sortorder = 'asc';
        } else if(isset($data[4]) && $data[4] == 'descending'){
            $sort = $data[5];
            $sortorder = 'desc';
        } else {
            $sort = 'proposals.created_at';
            $sortorder = 'asc';
        }

        $sql = "date(proposals.created_at) >= '".$added_from."' and date(proposals.created_at) <= '".$added_to."' and proposals.deletestatus = '0' $sqlstatus";
	    // $proposals = $this->proposalRepo->rawWith(['customer'], $sql, 'created_at');
	    $proposals = Proposals::leftJoin('customers','proposals.customer_id','=','customers.id')
	    					  ->leftJoin(env('DB_DATABASE').'.client_users','proposals.user_id','=',env('DB_DATABASE').'.client_users.id')
	    					  ->select('proposals.created_at','customers.name','proposals.amount','proposals.status','proposals.remarks',env('DB_DATABASE').'.client_users.name as username','proposals.file','proposals.id')
	    					  ->whereRaw($sql)
	    					  ->orderBy($sort, $sortorder)
	    					  ->get();
	    $total = Proposals::selectRaw("SUM(amount) as total, amount, created_at, customer_id, file")->whereRaw($sql)->first();

        if($data[3] == 'csv'){
	    	Excel::create('Proposal Report '.$from_display.' - '.$to_display, function($excel) use ($proposals, $from_display, $to_display, $total) {
	            $excel->sheet('New sheet', function($sheet) use ($proposals, $from_display, $to_display, $total) {
	                $sheet->loadView('Client::crm.reports.proposal.excel', compact('proposals','from_display','to_display','total'));
	            });
	        })->download('csv');
	    }

	    if($data[4] == 'pdf'){
	    	Excel::create('Proposal Report '.$from_display.' - '.$to_display, function($excel) use ($proposals, $from_display, $to_display, $total) {
	            $excel->sheet('New sheet', function($sheet) use ($proposals, $from_display, $to_display, $total) {
	                $sheet->setOrientation('landscape')
	                	  ->setAllBorders('thin')
	                	  ->setWidth(array(
						    'A'  =>  5,
						    'B'  =>  10,
						    'C'  =>  15,
						    'D'  =>  10,
						    'E'  =>  10,
						    'F'  =>  20,
						    'G'  =>  15
						  ))->loadView('Client::crm.reports.proposal.excel', compact('proposals','from_display','to_display'));
	            });
	        })->download('pdf');
	    }
    }

    public function lead()
    {
    	$productList = [''=>'All Product']+ActivityType::where('product','0')->where('deletestatus','0')->where('status','0')->orderBy('name')->pluck('name', 'id')->toArray();
    	$chanceList = [''=>'--',0=>'0',25=>'25',50=>'50',75=>'75',100=>'100'];
    	return view('Client::crm.reports.proposal.lead', compact('productList','chanceList'));
    }

    public function storelead(Request $request)
    {
    	$added_date_range = $request['date_added'];
        $added_date = explode('-', $added_date_range);
        $added_from = Carbon::parse($added_date[0])->format('Y-m-d');
        $added_to = Carbon::parse($added_date[1])->format('Y-m-d');
        $from_display = Carbon::parse($added_date[0])->format('d F Y');
        $to_display = Carbon::parse($added_date[1])->format('d F Y');

        if($request['rtype'] == 1){
	        $sqlcustomer = '';
	        $sqlchances = '';
	        $sqluser = '';

	        if($request['chance']){
	        	$chances = $request['chance'];
	        	$sqlchances = "and chance = '".$chances."'";
	        } else {
	        	$arrchances = ['0','25','50','75','100'];
	        	$chances = '0x0';
	        }

	        if($request['customer_id']){
	        	$customer = $request['customer_id'];
	        	$sqlcustomer = "and customer_id = '".$customer."'";
	        } else {
	        	$customer = '0x0';
	        }

	        if($request['user_id']){
	        	$user = $request['user_id'];
	        	$sqluser = "and user_id in (".$user.")";
	        } else {
	        	$user = '0x0';
	        }

	        $link = 'showlead/'.$added_from.'xxx'.$added_to.'xxx'.$customer.'xxx'.$chances.'xxx'.$user;

	        if(isset($chances) && $chances!='0x0'){
	        	$sql = "date(created_at) >= '".$added_from."' and date(created_at) <= '".$added_to."' and deletestatus = '0' $sqlcustomer $sqlchances $sqluser";
	        	$proposals = $this->proposalRepo->rawWith(['customer'], $sql, 'created_at');
	        	$table = '<h3>Chances : '.$chances.' %</h3>';
		        $table .= '<table class="table table-hover table-striped">';
					$table .= '<thead>';
						$table .= '<tr>';
							$table .= '<th style="width:3%" class="text-center">#</th>';
							$table .= '<th style="width:15%">Client</th>';
							$table .= '<th style="width:10%">Date Sent</th>';
							$table .= '<th style="width:10%">Type</th>';
							$table .= '<th style="width:10%">Status</th>';
							$table .= '<th style="width:10%">Added By</th>';
							$table .= '<th style="width:10%">Estimated Revenue</th>';
							$table .= '<th style="width:15%">Remarks</th>';
						$table .= '</tr>';
					$table .= '</thead>';
					$table .= '<tbody>';
						if(count($proposals)){
							$totalamount = '0.00';
							foreach ($proposals as $key => $proposal) {
								$counter = $key+1;
								$customername = (count($proposal->customer)) ? $proposal->customer->name : '';
								$type = (count($proposal->product)) ? $proposal->product->name : '';
								$username = (count($proposal->user)) ? $proposal->user->name : '';
								$totalamount += $proposal->amount;
								$table .= '<tr>';
									$table .= '<td class="text-center">'.$counter.'</td>';
									$table .= '<td>'.$customername.'</td>';
									$table .= '<td>'.$proposal->created_at.'</td>';
									$table .= '<td>'.$type.'</td>';
									$table .= '<td>'.$proposal->status.'</td>';
									$table .= '<td>'.$username.'</td>';
									$table .= '<td style="text-align:right">'.$proposal->amount_display.'</td>';
									$table .= '<td>'.$proposal->remarks_display.'</td>';
								$table .= '</tr>';
							}
						} else {
							$table .= '<tr>';
								$table .= '<td class="text-center" colspan="8">No Records Found.</td>';
							$table .= '</tr>';
						}
					$table .= '</tbody>';
				$table .= '</table>';
				$table .= '<div style="text-align:right"><b>Total : '.number_format($totalamount,2).'</b></div>';
			} else {
				$table = '';
				foreach ($arrchances as $chance) {
					$sql = "date(created_at) >= '".$added_from."' and date(created_at) <= '".$added_to."' and deletestatus = '0' and chance = '".$chance."' $sqlcustomer $sqluser";
	        		$proposals = $this->proposalRepo->rawWith(['customer'], $sql, 'created_at');
					$totalamount = '0.00';
					$table .= '<h3>Chances : '.$chance.' %</h3>';
			        $table .= '<table class="table table-hover table-striped">';
						$table .= '<thead>';
							$table .= '<tr>';
								$table .= '<th style="width:3%" class="text-center">#</th>';
								$table .= '<th style="width:15%">Client</th>';
								$table .= '<th style="width:10%">Date Sent</th>';
								$table .= '<th style="width:10%">Product</th>';
								$table .= '<th style="width:10%">Status</th>';
								$table .= '<th style="width:10%">User</th>';
								$table .= '<th style="width:10%">Estimated Revenue</th>';
								$table .= '<th style="width:15%">Remarks</th>';
							$table .= '</tr>';
						$table .= '</thead>';
						$table .= '<tbody>';
							if(count($proposals)){
								foreach ($proposals as $key => $proposal) {
									$counter = $key+1;
									$customername = (count($proposal->customer)) ? $proposal->customer->name : '';
									$type = (count($proposal->product)) ? $proposal->product->name : '';
									$username = (count($proposal->user)) ? $proposal->user->name : '';
									$totalamount += $proposal->amount;
									$table .= '<tr>';
										$table .= '<td class="text-center">'.$counter.'</td>';
										$table .= '<td>'.$customername.'</td>';
										$table .= '<td>'.$proposal->created_at.'</td>';
										$table .= '<td>'.$type.'</td>';
										$table .= '<td>'.$proposal->status.'</td>';
										$table .= '<td>'.$username.'</td>';
										$table .= '<td style="text-align:right">'.$proposal->amount_display.'</td>';
										$table .= '<td>'.$proposal->remarks_display.'</td>';
									$table .= '</tr>';
								}
							} else {
								$table .= '<tr>';
									$table .= '<td class="text-center" colspan="8">No Records Found.</td>';
								$table .= '</tr>';
							}
						$table .= '</tbody>';
					$table .= '</table>';
					$table .= '<div style="text-align:right"><b>Total : '.number_format($totalamount,2).'</b></div>';
				}
			}

		    return ['table' => $table, 'from_display' => $from_display, 'to_display' => $to_display, 'link' => $link];
    	}

    	if($request['rtype'] == 2){
    		$sqluser = '';
    		$sqlproduct = '';

    		if($request['product_id']){
    			$product = $request['product_id'];
    			$sqlproduct = "and product_id = '".$product."'";
    		} else {
    			$product = '0x0';
    			$sqlproduct = "and product_id != '0'";
    		}

    		if($request['user_id']){
	        	$user = $request['user_id'];
	        	$sqluser = "and user_id in (".$user.")";
	        } else {
	        	$user = '0x0';
	        }

	        $link = 'showsummarylead/'.$added_from.'xxx'.$added_to.'xxx'.$product.'xxx'.$user;
	        $sqlproposal = "and product_id in (select id from activity_types where deletestatus = '0' and status = '0' and product = '0')";
	        $sql = "date(created_at) >= '".$added_from."' and date(created_at) <= '".$added_to."' and deletestatus = '0' $sqlproduct $sqluser $sqlproposal";
	        $proposals = $this->proposalRepo->rawWith(['customer'], $sql, 'created_at');
	        $products = ActivityType::where('product','0')->where('deletestatus','0')->where('status','0')->withCount(['proposal' => function($query) use ($sql){
	        	$query->whereRaw($sql);
	        }])->where('name','!=','')->orderBy('name')->get();

	        $table = '<div class="row">';
	        	foreach ($products as $product) {
				$table .= '<div class="col-md-3 col-sm-6 col-xs-12">';
					$table .= '<div style="text-align:left">';
					$table .= '<h4>'.$product->name.' : '.$product->proposal_count.'</h4>';
					$table .= '</div>';
				$table .= '</div>';
	        	}
			$table .= '</div>';
			$table .= '<div class="row">';
			$table .= '<div class="col-md-12">';
				$table .= '<table class="table table-hover table-striped">';
					$table .= '<thead>';
						$table .= '<tr>';
							$table .= '<th style="width:3%" class="text-center">#</th>';
							$table .= '<th style="width:15%">Client</th>';
							$table .= '<th style="width:10%">Date Sent</th>';
							$table .= '<th style="width:10%">Type</th>';
							$table .= '<th style="width:10%">Status</th>';
							$table .= '<th style="width:10%">Added By</th>';
							$table .= '<th style="width:10%">Estimated Revenue</th>';
							$table .= '<th style="width:15%">Remarks</th>';
						$table .= '</tr>';
					$table .= '</thead>';
					$table .= '<tbody>';
						if(count($proposals)){
							$totalamount = '0.00';
							foreach ($proposals as $key => $proposal) {
								$counter = $key+1;
								$customername = (count($proposal->customer)) ? $proposal->customer->name : '';
								$type = (count($proposal->product)) ? $proposal->product->name : '';
								$username = (count($proposal->user)) ? $proposal->user->name : '';
								$totalamount += $proposal->amount;
								$table .= '<tr>';
									$table .= '<td class="text-center">'.$counter.'</td>';
									$table .= '<td>'.$customername.'</td>';
									$table .= '<td>'.$proposal->created_at.'</td>';
									$table .= '<td>'.$type.'</td>';
									$table .= '<td>'.$proposal->status.'</td>';
									$table .= '<td>'.$username.'</td>';
									$table .= '<td style="text-align:right">'.$proposal->amount_display.'</td>';
									$table .= '<td>'.$proposal->remarks_display.'</td>';
								$table .= '</tr>';
							}
						} else {
							$table .= '<tr>';
								$table .= '<td class="text-center" colspan="8">No Records Found.</td>';
							$table .= '</tr>';
						}
					$table .= '</tbody>';
				$table .= '</table>';
				$table .= '<div style="text-align:right"><b>Total : '.number_format($totalamount,2).'</b></div>';
			$table .= "</div>";
			$table .= '</div';
			return ['table' => $table, 'from_display' => $from_display, 'to_display' => $to_display, 'link' => $link];
    	}
    }

    public function showlead($datas)
    {
    	$data = explode('xxx', $datas);
        $added_from = Carbon::parse($data[0])->format('Y-m-d');
        $added_to = Carbon::parse($data[1])->format('Y-m-d');
        $from_display = Carbon::parse($data[0])->format('d F Y');
        $to_display = Carbon::parse($data[1])->format('d F Y');

        $sqlcustomer = '';
        $sqlchances = '';
        $sqluser = '';

        if($data[2]!='0x0'){
        	$customer = $data[2];
        	$sqlcustomer = "and customer_id = '".$customer."'";
        }

        if($data[3]!='0x0'){
        	$chances = $data[3];
        	$sqlchances = "and chance = '".$chances."'";
        } else {
        	$arrchances = ['0','25','50','75','100'];
        }

        if($data[4]!='0x0'){
        	$user = $data[4];
        	$sqluser = "and user_id in (".$user.")";
        }

        if(isset($chances)){
        	$sql = "date(created_at) >= '".$added_from."' and date(created_at) <= '".$added_to."' and deletestatus = '0' $sqlcustomer $sqlchances $sqluser";
        	$proposals = $this->proposalRepo->rawWith(['customer'], $sql, 'created_at');
        	$table = '<h3>Chances : '.$chances.' %</h3>';
	        $table .= '<table class="table table-hover table-striped">';
				$table .= '<thead>';
					$table .= '<tr>';
						$table .= '<th style="width:3%" class="text-center">#</th>';
						$table .= '<th style="width:15%">Client</th>';
						$table .= '<th style="width:10%">Date Sent</th>';
						$table .= '<th style="width:10%">Type</th>';
						$table .= '<th style="width:10%">Status</th>';
						$table .= '<th style="width:10%">User</th>';
						$table .= '<th style="width:10%">Estimated Revenue</th>';
						$table .= '<th style="width:15%">Remarks</th>';
					$table .= '</tr>';
				$table .= '</thead>';
				$table .= '<tbody>';
					if(count($proposals)){
						foreach ($proposals as $key => $proposal) {
							$counter = $key+1;
							$customername = (count($proposal->customer)) ? $proposal->customer->name : '';
							$type = (count($proposal->product)) ? $proposal->product->name : '';
							$username = (count($proposal->user)) ? $proposal->user->name : '';
							$table .= '<tr>';
								$table .= '<td class="text-center">'.$counter.'</td>';
								$table .= '<td>'.$customername.'</td>';
								$table .= '<td>'.$proposal->created_at.'</td>';
								$table .= '<td>'.$type.'</td>';
								$table .= '<td>'.$proposal->status.'</td>';
								$table .= '<td>'.$username.'</td>';
								$table .= '<td style="text-align:right">'.$proposal->amount_display.'</td>';
								$table .= '<td>'.$proposal->remarks_display.'</td>';
							$table .= '</tr>';
						}
					} else {
						$table .= '<tr>';
							$table .= '<td class="text-center" colspan="8">No Records Found.</td>';
						$table .= '</tr>';
					}
				$table .= '</tbody>';
			$table .= '</table>';
		} else {
			$table = '';
			foreach ($arrchances as $chance) {
				$sql = "date(created_at) >= '".$added_from."' and date(created_at) <= '".$added_to."' and deletestatus = '0' and chance = '".$chance."' $sqlcustomer $sqluser";
        		$proposals = $this->proposalRepo->rawWith(['customer'], $sql, 'created_at');
				$table .= '<h3>Chances : '.$chance.' %</h3>';
		        $table .= '<table class="table table-hover table-striped">';
					$table .= '<thead>';
						$table .= '<tr>';
							$table .= '<th style="width:3%" class="text-center">#</th>';
							$table .= '<th style="width:15%">Client</th>';
							$table .= '<th style="width:10%">Date Sent</th>';
							$table .= '<th style="width:10%">Product</th>';
							$table .= '<th style="width:10%">Status</th>';
							$table .= '<th style="width:10%">User</th>';
							$table .= '<th style="width:10%">Estimated Revenue</th>';
							$table .= '<th style="width:15%">Remarks</th>';
						$table .= '</tr>';
					$table .= '</thead>';
					$table .= '<tbody>';
						if(count($proposals)){
							foreach ($proposals as $key => $proposal) {
								$counter = $key+1;
								$customername = (count($proposal->customer)) ? $proposal->customer->name : '';
								$type = (count($proposal->product)) ? $proposal->product->name : '';
								$username = (count($proposal->user)) ? $proposal->user->name : '';
								$table .= '<tr>';
									$table .= '<td class="text-center">'.$counter.'</td>';
									$table .= '<td>'.$customername.'</td>';
									$table .= '<td>'.$proposal->created_at.'</td>';
									$table .= '<td>'.$type.'</td>';
									$table .= '<td>'.$proposal->status.'</td>';
									$table .= '<td>'.$username.'</td>';
									$table .= '<td style="text-align:right">'.$proposal->amount_display.'</td>';
									$table .= '<td>'.$proposal->remarks_display.'</td>';
								$table .= '</tr>';
							}
						} else {
							$table .= '<tr>';
								$table .= '<td class="text-center" colspan="8">No Records Found.</td>';
							$table .= '</tr>';
						}
					$table .= '</tbody>';
				$table .= '</table>';
			}
		}

		if($data[5] == 'csv'){
	    	Excel::create('Lead Opportunity Report '.$from_display.' - '.$to_display, function($excel) use ($proposals, $from_display, $to_display, $table) {
	            $excel->sheet('New sheet', function($sheet) use ($proposals, $from_display, $to_display, $table) {
	                $sheet->loadView('Client::crm.reports.proposal.excellead', compact('table'));
	            });
	        })->download('csv');
	    }

	    if($data[5] == 'pdf'){
	    	Excel::create('Lead Opportunity Report '.$from_display.' - '.$to_display, function($excel) use ($proposals, $from_display, $to_display, $table) {
	            $excel->sheet('New sheet', function($sheet) use ($proposals, $from_display, $to_display, $table) {
	                $sheet->setOrientation('landscape')
	                	  ->setAllBorders('thin')
	                	  ->setWidth(array(
						    'A'  =>  5,
						    'B'  =>  15,
						    'C'  =>  10,
						    'D'  =>  10,
						    'E'  =>  10,
						    'F'  =>  10,
						    'G'  =>  10,
						    'H'  =>  15
						  ))->loadView('Client::crm.reports.proposal.excellead', compact('table'));
	            });
	        })->download('pdf');
	    }
    }

    public function showsummarylead($datas)
    {
    	$data = explode('xxx', $datas);
        $added_from = Carbon::parse($data[0])->format('Y-m-d');
        $added_to = Carbon::parse($data[1])->format('Y-m-d');
        $from_display = Carbon::parse($data[0])->format('d F Y');
        $to_display = Carbon::parse($data[1])->format('d F Y');

		$sqlproduct = '';
        $sqluser = '';

        if($data[2]!='0x0'){
        	$product = $data[2];
        	$sqlproduct = "and product_id = '".$product."'";
        }

        if($data[3]!='0x0'){
        	$user = $data[3];
        	$sqluser = "and user_id in (".$user.")";
        }

        $sql = "date(created_at) >= '".$added_from."' and date(created_at) <= '".$added_to."' and deletestatus = '0' $sqlproduct $sqluser";
        $proposals = $this->proposalRepo->rawWith(['customer'], $sql, 'created_at');
        $products = ActivityType::where('product','0')->withCount(['proposal' => function($query) use ($sql){
        	$query->whereRaw($sql);
        }])->orderBy('name')->get();

        $table = '<table class="table table-hover table-striped">';
			$table .= '<thead>';
				$table .= '<tr>';
					$table .= '<th style="width:3%" class="text-center">#</th>';
					$table .= '<th style="width:15%">Client</th>';
					$table .= '<th style="width:10%">Date Sent</th>';
					$table .= '<th style="width:10%">Type</th>';
					$table .= '<th style="width:10%">Status</th>';
					$table .= '<th style="width:10%">User</th>';
					$table .= '<th style="width:10%">Estimated Revenue</th>';
					$table .= '<th style="width:15%">Remarks</th>';
				$table .= '</tr>';
			$table .= '</thead>';
			$table .= '<tbody>';
				if(count($proposals)){
					foreach ($proposals as $key => $proposal) {
						$counter = $key+1;
						$customername = (count($proposal->customer)) ? $proposal->customer->name : '';
						$type = (count($proposal->product)) ? $proposal->product->name : '';
						$username = (count($proposal->user)) ? $proposal->user->name : '';
						$table .= '<tr>';
							$table .= '<td class="text-center">'.$counter.'</td>';
							$table .= '<td>'.$customername.'</td>';
							$table .= '<td>'.$proposal->created_at.'</td>';
							$table .= '<td>'.$type.'</td>';
							$table .= '<td>'.$proposal->status.'</td>';
							$table .= '<td>'.$username.'</td>';
							$table .= '<td style="text-align:right">'.$proposal->amount_display.'</td>';
							$table .= '<td>'.$proposal->remarks_display.'</td>';
						$table .= '</tr>';
					}
				} else {
					$table .= '<tr>';
						$table .= '<td class="text-center" colspan="8">No Records Found.</td>';
					$table .= '</tr>';
				}
			$table .= '</tbody>';
		$table .= '</table>';

		if($data[4] == 'csv'){
	    	Excel::create('Lead Opportunity Report with Product Summary '.$from_display.' - '.$to_display, function($excel) use ($proposals, $from_display, $to_display, $table) {
	            $excel->sheet('New sheet', function($sheet) use ($proposals, $from_display, $to_display, $table) {
	                $sheet->loadView('Client::crm.reports.proposal.excelleadsummary', compact('table'));
	            });
	        })->download('csv');
	    }

	    if($data[4] == 'pdf'){
	    	Excel::create('Lead Opportunity Report with Product Summary '.$from_display.' - '.$to_display, function($excel) use ($proposals, $from_display, $to_display, $table) {
	            $excel->sheet('New sheet', function($sheet) use ($proposals, $from_display, $to_display, $table) {
	                $sheet->setOrientation('landscape')
	                	  ->setAllBorders('thin')
	                	  ->setWidth(array(
						    'A'  =>  5,
						    'B'  =>  15,
						    'C'  =>  10,
						    'D'  =>  10,
						    'E'  =>  10,
						    'F'  =>  10,
						    'G'  =>  10,
						    'H'  =>  15
						  ))->loadView('Client::crm.reports.proposal.excelleadsummary', compact('table'));
	            });
	        })->download('pdf');
	    }
    }
}
