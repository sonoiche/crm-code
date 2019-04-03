@extends('Client::layouts')
@section('page-body')
<section class="content-header">
    <h1>
        {{ $customer->name }}
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li>Customer</li>
        <li class="active">Invoice</li>
    </ol>
</section>
<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box box-primary">
				<div class="box-header with-border">
					<div class="pull-left">
						Customer Invoice
					</div>
					<div class="pull-right">
                        <a href="javascript:void(0)" class="btn btn-primary btn-sm" id="create-payment">Create New Invoice</a>
                        <a href="javascript:void(0)" class="btn btn-primary btn-sm" onclick="addtracker({{ $customer->id.','.$year }})">Create Payment Tracker</a>
					</div>
				</div>
				<div class="box-body">
					<div class="nav-tabs-custom">
						@include('Client::crm.customer.menu')
						<div class="tab-content">
					        <div class="tab-pane active" id="tab_1">
					            <div class="box box-primary">
									<div class="box-body">
										<table class="table table-hover table-striped" id="payment-table">
											<thead>
												<tr>
													<th style="width:2%">#</th>
													<th style="width:20%">Name</th>
                                                    <th style="width:10%">Amount</th>
													<th style="width:10%">Status</th>
													<th style="width:10%">Date Billed</th>
													<th style="width:10%">Due Date</th>
													<th style="width:10%">Date Paid</th>
													<th style="width:10%">Added By</th>
													<th style="width:10%"></th>
												</tr>
											</thead>
                                            <tbody>
                                                @foreach($payments as $key => $payment)
                                                <tr id="{{ $payment->id }}" {{ ($payment->deletestatus == 1) ? 'class=row-deleted' : '' }}>
                                                    <td>{{ $key+1 }}</td>
                                                    <td>{{ $payment->title }}</td>
                                                    <td>{{ $payment->totalamount }}</td>
                                                    <td id="td-status-{{ $payment->id }}">{{ $payment->status }}</td>
                                                    <td>{{ $payment->latest_date_bill }}</td>
                                                    <td>{{ $payment->latest_due_date }}</td>
                                                    <td>{{ $payment->latest_date_paid }}</td>
                                                    <td>{{ count($payment->user) ? $payment->user->name : '' }}</td>
                                                    <td>
                                                        {{ s3exist($payment->dlfile) }}
                                                        <div class="btn-group">
                                                            <button type="button" class="btn btn-primary btn-xs dropdown-toggle" data-toggle="dropdown">
                                                                Action &nbsp;&nbsp;
                                                                <span class="caret"></span>
                                                            </button>
                                                            <ul class="dropdown-menu" id="menu3" aria-labelledby="drop6" style="min-width: 125px !important;">
                                                                <li><a href="javascript:void(0)" onclick="viewor('{{ hashid($payment->id) }} ')"><i class="fa fa-search fa-fw"></i> View OR</a></li>
                                                                <li><a href="javascript:void(0)" onclick="editpaymentpaid('{{ hashid($payment->id) }} ')"><i class="fa fa-money fa-fw"></i> Payment</a></li>
                                                                <li><a href="javascript:void(0)" onclick="editpayment('{{ hashid($payment->id) }} ')"><i class="fa fa-pencil fa-fw"></i> Edit</a></li>
                                                                <li><a href="javascript:void(0)" onclick="deletepayment('{{ hashid($payment->id) }} ')"><i class="fa fa-ban fa-fw"></i> Cancel</a></li>
                                                            </ul>
                                                        </div>
                                                        @if($payment->count_file == 1)
                                                            &nbsp;&nbsp;<a href="{{ url($payment->dlfile) }}" class="btn btn-primary btn-xs" target="_blank"><i class="fa fa-download fa-fw"></i></a>
                                                        @elseif($payment->count_file > 1)
                                                            &nbsp;&nbsp;<a href="javascript:void(0)" onclick="multipledoc('{{ hashid($payment->id) }}')" class="btn btn-primary btn-xs" target="_blank"><i class="fa fa-download fa-fw"></i></a>
                                                        @else
                                                            &nbsp;&nbsp;<a href="javascript:void(0)" class="btn btn-default btn-xs"><i class="fa fa-download fa-fw"></i></a>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
<input type="hidden" id="customer_id" value="{{ $customer->id }}">
@include('Client::crm.payment.modal-payment')
@include('Client::crm.activity.modal-activity')
@include('Client::crm.customer.contact-modal')
@endsection

@section('page-css')
<style type="text/css">
.stepwizard-step p {
    margin-top: 0px;
    color:#666;
}
.stepwizard-row {
    display: table-row;
}
.stepwizard {
    display: table;
    width: 100%;
    position: relative;
}
.stepwizard-step button[disabled] {
    /*opacity: 1 !important;
    filter: alpha(opacity=100) !important;*/
}
.stepwizard .btn.disabled, .stepwizard .btn[disabled], .stepwizard fieldset[disabled] .btn {
    opacity:1 !important;
    color:#bbb;
}
.stepwizard-row:before {
    top: 14px;
    bottom: 0;
    position: absolute;
    content:" ";
    width: 100%;
    height: 1px;
    background-color: #ccc;
    z-index: 0;
}
.stepwizard-step {
    display: table-cell;
    text-align: center;
    position: relative;
}
.btn-circle {
    width: 30px;
    height: 30px;
    text-align: center;
    padding: 6px 0;
    font-size: 12px;
    line-height: 1.428571429;
    border-radius: 15px;
}
.row-deleted {
    background: #f2dede !important;
}
.error {
    color: #dd4b39 !important;
    border-color: #dd4b39;
}
#edit-or-number-error, #edit-amount-error, #edit-date-paid-error {
    color: #fff !important;
}
.row-deleted {
    background: #f2dede !important;
}
</style>
{!! Html::style('vendor/tagthis/jquery-tag-this.css') !!}
@endsection

@section('page-js')
{!! Html::script('vendor/tagthis/jquery-tag-this.js') !!}
{!! Html::script('vendor/tagthis/main.js') !!}
@include('Client::crm.payment.payment-js')
@include('Client::crm.activity.activity-js')
@include('Client::crm.customer.contact-js')
<script type="text/javascript">
var base_url = "{{ url('/') }}";
$("#uploaded, #create-uploaded").plupload({
    // General settings
    runtimes : 'html5,flash,silverlight,html4',
    url : base_url+ '/client/crm/customer/payment/uploadMultiple',

    // User can upload no more then 20 files in one go (sets multiple_queues to false)
    max_file_count: 20,
    
    chunk_size: '10mb',

    // Resize images on clientside if we can
    /*resize : {
        width : 200, 
        height : 200, 
        quality : 90,
        crop: true // crop to exact dimensions
    },*/
    
    filters : {
        // Maximum file size
        max_file_size : '1000mb',
        // Specify what files to browse for
        mime_types: [
            {title : "Image files", extensions : "jpg,gif,png,doc,docx,xls,xlsx,txt,odt,ods,csv,pub,pdf,jpeg"},
            {title : "Zip files", extensions : "zip,rar"}
        ]
    },

    // Rename files by clicking on their titles
    rename: true,
    
    // Sort files
    sortable: true,

    // Enable ability to drag'n'drop files onto the widget (currently only HTML5 supports that)
    dragdrop: true,

    // Views to activate
    // views: {
    //  list: true,
    //  thumbs: false, // Show thumbs
    //  active: 'thumbs'
    // },

    // Flash settings
    flash_swf_url : 'vendor/multiupload/Moxie.swf',

    // Silverlight settings
    silverlight_xap_url : 'vendor/multiupload/Moxie.xap'
});

$(document).ready(function() {
    $('#payment-table').DataTable({
        "pagingType": "input"
    });
});
</script>
@endsection