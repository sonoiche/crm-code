<?php

namespace QxCMS\Modules\Client\Controllers\CRM;

use Illuminate\Http\Request;
use QxCMS\Http\Controllers\Controller;
use QxCMS\Modules\Client\Repositories\CRM\Supplier\SupplierRepositoryEloquent as Supplier;

class SupplierController extends Controller
{
    protected $supplierRepo;
    protected $auth;

    public function __construct(Supplier $supplierRepo)
    {
    	$this->supplierRepo = $supplierRepo;
    	$this->auth = auth();
    }

    public function create()
    {
    	return view('Client::crm.supplier.create');
    }

    public function checkname(Request $request)
    {
    	$id = decode($request['id']);
        if($id){
            $sql = "name = '".$request['name']."' and id != '".$id."'";
            $dup = $this->supplierRepo->rawAll($sql);
            return count($dup);
        } else {
            $sql = "name = '".$request['name']."'";
            $dup = $this->supplierRepo->rawAll($sql);
            return count($dup);
        }
    }

    public function store(Request $request)
    {
    	return $request->all();
    	// $makeRequest = [
    	// 	''
    	// ];
    }
}
