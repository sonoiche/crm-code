<?php

namespace QxCMS\Modules\Client\Controllers\CRM\Reports;

use Carbon\Carbon;
use Illuminate\Http\Request;
use QxCMS\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use QxCMS\Modules\Client\Models\CRM\Product;
use QxCMS\Modules\Client\Repositories\CRM\Customer\ContractRepositoryEloquent as Contract;

class ContractController extends Controller
{
    protected $contractRepo;

    public function __construct(Contract $contractRepo)
    {
    	$this->contractRepo = $contractRepo;
    }

    public function index()
    {
    	$productList = [''=>'All Product']+Product::orderBy('name')->pluck('name', 'id')->toArray();
    	return view('Client::crm.reports.contract.index', compact('productList'));
    }

    public function store(Request $request)
    {
        $added_date_range = $request['date_added'];
        $added_date = explode('-', $added_date_range);
        $added_from = Carbon::parse($added_date[0])->format('Y-m-d');
        $added_to = Carbon::parse($added_date[1])->format('Y-m-d');
        $from_display = Carbon::parse($added_date[0])->format('d F Y');
        $to_display = Carbon::parse($added_date[1])->format('d F Y');

        $sqluser = '';
        $sqlproduct = '';

        if($request['product_id']){
            $product = $request['product_id'];
            $sqlproduct = "and product_id = '".$product."'";
        } else {
            $product = '0x0';
        }

        if($request['user_id']){
            $user = $request['user_id'];
            $sqluser = "and user_id in (".$user.")";
        } else {
            $user = '0x0';
        }

        $sql = "contract_date >= '".$added_from."' and contract_date <= '".$added_to."' and deletestatus = '0' $sqlproduct $sqluser";
        $contracts = $this->contractRepo->rawWith(['product','user','customer'], $sql, 'contract_date');
        $link = 'contract/'.$added_from.'xxx'.$added_to.'xxx'.$product.'xxx'.$user;
        return ['contracts' => $contracts, 'from_display' => $from_display, 'to_display' => $to_display, 'link' => $link, 'totalcount' => count($contracts)];
    }

    public function show($datas)
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

        if(isset($data[5]) && $data[5] == 'ascending'){
            $sort = $data[6];
            $sortorder = 'asc';
        } else if(isset($data[5]) && $data[5] == 'descending'){
            $sort = $data[6];
            $sortorder = 'desc';
        } else {
            $sort = 'date_added';
            $sortorder = 'asc';
        }

        $sql = "contract_date >= '".$added_from."' and contract_date <= '".$added_to."' and deletestatus = '0' $sqlproduct $sqluser";
        $contracts = $this->contractRepo->rawWith(['product','user'], $sql, $sort, $sortorder);

        if($data[4] == 'csv'){
            Excel::create('Company Contract Report '.$from_display.' - '.$to_display, function($excel) use ($contracts, $from_display, $to_display) {
                $excel->sheet('New sheet', function($sheet) use ($contracts, $from_display, $to_display) {
                    $sheet->loadView('Client::crm.reports.contract.excel', compact('contracts'));
                });
            })->download('csv');
        }

        if($data[4] == 'pdf'){
            Excel::create('Company Contract Report '.$from_display.' - '.$to_display, function($excel) use ($contracts, $from_display, $to_display) {
                $excel->sheet('New sheet', function($sheet) use ($contracts, $from_display, $to_display) {
                    $sheet->setOrientation('landscape')
                          ->setAllBorders('thin')
                          ->setWidth(array(
                            'A'  =>  5,
                            'B'  =>  15,
                            'C'  =>  10,
                            'D'  =>  20,
                            'E'  =>  10,
                            'F'  =>  10,
                            'G'  =>  10
                          ))->loadView('Client::crm.reports.contract.excel', compact('contracts'));
                });
            })->download('pdf');
        }
    }
}
