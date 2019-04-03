@extends('Client::layouts')
@section('page-body')
<section class="content-header">
    <h1>
        Reports
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li>Reports</li>
        <li class="active">Contract Report</li>
    </ol>
</section>
<section class="content">
	<div class="col-md-12">
		<div class="box box-primary">
			<div class="box-header with-border">
				<div class="pull-left" id="header-report">
					Contract Report
				</div>
				<div class="pull-right" id="header-button" style="display: none">
					<a href="javascript:void(0)" id="filterbtn" class="btn btn-primary btn-sm">Generate New</a>
					<div class="btn-group">
						<button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown">
							Export &nbsp;&nbsp;
							<span class="caret"></span>
						</button>
					    <ul class="dropdown-menu dropdown-menu-right" id="menu3" aria-labelledby="drop6" style="min-width: 125px !important;">
					        <!-- <li><a href="javascript:void(0)" id="export-pdf"><i class="fa fa-file-pdf-o fa-fw"></i> To PDF</a></li> -->
					        <li><a href="" id="export-csv"><i class="fa fa-file-excel-o fa-fw"></i> To CSV</a></li>
					    </ul>
					</div>
				</div>
			</div>
			<div class="box-body" id="report-filter">
				{!! Form::open(['url'=>'client/crm/reports/contract','id'=>'reportForm','class'=>'role-form']) !!}
				<!-- <div class="row">
					<div class="col-lg-12">
						<div class="form-group">
							<label>User</label>
							<input type="text" class="form-control" name="user_id" id="user_id">
							<small class="error">Leave blank to generate all users</small>
						</div>
					</div>
				</div> -->
				<div class="row">
					<div class="col-lg-6">
						<div class="form-group">
							<label>Contract Date <i class="fa fa-asterisk error" style="font-size: 11px"></i></label>
							<div class="input-group">
	            				<input type="text" class="form-control" name="date_added" id="date_added" placeholder="Date Added">
	            				<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
	            			</div>
						</div>
					</div>
					<div class="col-lg-6">
						<div class="form-group">
							<label>User</label>
							<input type="text" class="form-control" name="user_id" id="user_id">
							<small class="error">Leave blank to generate all users</small>
						</div>
						<!-- <label>Product Type</label>
						{!! Form::select('product_id', $productList, '', ['class'=>'form-control','id'=>'product_type']) !!} -->
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<div class="text-center">
							<input type="hidden" name="csv">
							<input type="hidden" name="pdf">
							<button type="submit" class="btn btn-primary btn-md" id="reportbtn">Generate Report</button>
						</div>
					</div>
				</div>
				{!! Form::close() !!}
			</div>
			<div class="box-body" id="report-display" style="display: none">
				<div id="div-totalcount" style="padding: 5px 0px"></div>
				<table class="table table-hover table-striped" id="contract-table">
					<thead>
						<tr>
							<!-- <th style="width: 3%" class="text-center">#</th> -->
							<th class="table-sort" data-header="date_added" style="width: 10%">Date Added</th>
							<th class="table-sort" data-header="name" style="width: 15%">Company Name</th>
							<th class="table-sort" data-sort-method="none" style="width: 15%">Contract Name</th>
							<th class="table-sort" data-sort-method="none" style="width: 10%">Contract Type</th>
							<th class="table-sort" data-sort-method="none" style="width: 20%">Remarks</th>
							<th class="table-sort" data-sort-method="none" style="width: 10%">Price</th>
							<th class="table-sort" data-header="contract_date" style="width: 10%">Contract Date</th>
							<th class="table-sort" data-sort-method="none" style="width: 10%">Added By</th>
							<th class="table-sort" data-sort-method="none" style="width: 10%">File</th>
						</tr>
					</thead>
					<tbody id="table-contract"></tbody>
				</table>
				<input type="hidden" id="default-link">
			</div>
		</div>
	</div>
</section>
<div style="clear: both;"></div>
<div class="modal fade" id="multipledoc-modal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">Contract Multiple Documents</h4>
			</div>
			<div class="modal-body">
				<div id="div-contractfiles"></div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('page-css')
<link rel="stylesheet" type="text/css" href="{{ asset('vendor/tablesort/tablesort.css') }}">
@stop

@section('page-js')
<script type="text/javascript" src="{{ asset('vendor/tablesort/src/tablesort.js') }}"></script>
<script src="{{ asset('vendor/tablesort/src/sorts/tablesort.number.js') }}"></script>
<script src="{{ asset('vendor/tablesort/src/sorts/tablesort.date.js') }}"></script>
<script>
  new Tablesort(document.getElementById('contract-table'));
</script>
{!! Html::script('vendor/tokeninput/jquery.tokeninput.js') !!}
<script type="text/javascript">
$(document).ready(function() {
	var url = "{{ url('/') }}";
	$("#user_id").tokenInput(url+ "/client/crm/customer/userlist", {
        theme: "facebook",
        preventDuplicates: true,
        hintText: "Type in user's name"
    });

    $('.table-sort').click(function() {
    	var sort = $(this).attr('aria-sort');
    	var data = $(this).attr('data-header');
    	$('#export-csv').removeAttr('href');
    	$('#export-csv').attr('href', $('#default-link').val() + 'xxx'+sort+'xxx'+data);
    });

	$('#date_added').daterangepicker(
	{
		ranges: {
			'Today': [moment(), moment()],
			'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
			'Last 7 Days': [moment().subtract(6, 'days'), moment()],
			'Last 30 Days': [moment().subtract(29, 'days'), moment()],
			'This Month': [moment().startOf('month'), moment().endOf('month')],
			'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
		},
		showDropdowns: true,
		startDate: moment().subtract(29, 'days'),
		endDate: moment()
		},
		function (start, end) {
			$('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
		}
	);

    $('#reportForm').validate({
    	ignore: "",
        rules: {
            date_added: { required : true }
        },
        messages : {
            date_added: "Please provide date added."
        },
        errorElement: 'label',
        errorClass: 'error',
        errorPlacement: function(error, element) {
            if(element.parent('.input-group').length) {
                error.insertAfter(element.parent());
            } else {
                error.insertAfter(element);
            }
        },
        submitHandler: function () {
        	$('#reportbtn').attr('disabled', true);
            $('#reportbtn').html('<i class="fa fa-spinner fa-spin"></i> Generating');
            var data = $('#reportForm').serializeArray();
            $.ajax({
                url: "{{ url('client/crm/reports/contract') }}",
                type: 'POST',
                dataType: 'json',
                data: data,
                success: function(result){
                	var sort = $('#sort-order').val();
                    $('#reportbtn').removeAttr('disabled');
                    $('#reportbtn').html('Generate');
                    $('#report-filter').slideUp('400');
                    $('#report-display').fadeIn('400');
                    $('#header-report').html('<h4>Company Contract Report '+result.from_display+' - '+result.to_display+'</h4>');
                    $('#header-button').show();
                    $('#export-csv').attr('href', result.link+'xxxcsv');
                    $('#default-link').val(result.link+'xxxcsv');
                    // $('#export-pdf').attr('href', result.link+'xxxpdf');
                    $('#div-totalcount').html('Total of '+result.totalcount+' records were found.');
                    var datas = result.contracts;
                    var base_url = $('body').attr('data-url');
                    if(datas.length != 0){ 
	            		$.each(datas, function(index, data) {
	            			if(data.count_file > 1){
	            				var attachment = '<a href="javascript:void(0)" onclick="multipledoc(\''+data.hashid+'\')" class="btn btn-primary btn-xs" target="_blank"><i class="fa fa-download fa-fw"></i></a>';
	            			} else if(data.attach_file!='' && data.attach_file!='0'){
                				var attachment = '<a href="'+data.dlfile+'" class="btn btn-primary btn-xs" target="_blank"><i class="fa fa-download fa-fw"></i></a>';
                			} else {
                				var attachment = '';
                			}
	            			var i = index+1;
	                    	$('#table-contract').append(
	                    		'<tr>'+
	                    			// '<td class="text-center">'+i+'</td>'+
	                    			'<td>'+data.created_at+'</td>'+
	                    			'<td><a href="'+((data.customer != null) ? base_url+'/client/crm/customer/'+data.customer.hashid : '#')+'/contract" target="_blank">'+((data.customer != null) ? data.customer.name : '')+'</a></td>'+
	                    			'<td>'+data.name+'</td>'+
	                    			'<td>'+data.contract_type+'</td>'+
	                    			'<td id="td-'+data.id+'"><a href="javascript:void(0)" onclick="editremarks('+data.id+',\''+((data.remarks != '') ? data.remarks_format : '')+'\')">'+((data.remarks != '') ? data.remarks : 'Update Remarks')+'</a></td>'+
	                    			'<td style="text-align:right">'+data.amount_display+'</td>'+
	                    			'<td>'+data.contract_date+'</td>'+
	                    			'<td>'+((data.user != null) ? data.user.name : '')+'</td>'+
	                    			'<td>'+attachment+'</td>'+
	                    		'</tr>'
	                    	);
	                    });
	            	} else {
	            		$('#table-contract').append(
                    		'<tr>'+
                    			'<td class="text-center" colspan="9">No records found.</td>'+
                    		'</tr>'
                    	);
	            	}
                },
	            error: function(XMLHttpRequest, textStatus, errorThrown) {
	            	console.log(XMLHttpRequest.status);
			        if (XMLHttpRequest.status == 500) {
			            swal({
	                        title: 'Error!',
	                        text: 'System experiencing an Internal Server Error.',
	                        type: 'error',
	                        showConfirmButton: false
	                    })
	                    location.reload();
			        } else if (XMLHttpRequest.readyState == 0) {
			            swal({
	                        title: 'Error!',
	                        text: 'Network error (i.e. connection refused, access denied due to CORS, etc.)',
	                        type: 'error',
	                        showConfirmButton: false
	                    })
	                    location.reload();
			        } else {
			            swal({
	                        title: 'Error!',
	                        text: 'Something went wrong, please try again.',
	                        type: 'error',
	                        showConfirmButton: false
	                    })
	                    location.reload();
			        }
			    }
            });
        }
    })

    $('#filterbtn').click(function() {
    	$('#header-button').hide();
        $('#report-display').slideUp('400');
    	$('#report-filter').fadeIn('400');
    	$('#header-report').html('Contract Report');
    	$('#table-contract').empty();
    	$("[name='csv']").val('');
        $("[name='pdf']").val('');
        $('.role-form').attr('id', 'reportForm');
    });

});

function editremarks(id,remarks){
	$('#td-'+id).html('<textarea name="remarks" id="remarks" data-id="'+id+'" class="form-control" style="resize:none">'+remarks+'</textarea>');

	$('#remarks').keypress(function (e) {
        if (e.which == 13) {
        	$('#td-'+id).html('<i class="fa fa-spinner fa-spin"></i> Saving..');
            var id = $(this).attr('data-id');
            var remarks = $(this).val();

            $.ajax({
            	url: "{{ url('client/crm/customer/contract/updateremarks') }}",
            	type: 'POST',
            	dataType: 'json',
            	data: {id: id, remarks: remarks},
            	success: function(result){
            		$('#td-'+id).html('<a href="javascript:void(0)" onclick="editremarks('+id+',\''+remarks+'\')">'+((remarks != '') ? remarks : 'Update Remarks')+'</a>');
            	},
	            error: function(XMLHttpRequest, textStatus, errorThrown) {
	            	console.log(XMLHttpRequest.status);
			        if (XMLHttpRequest.status == 500) {
			            swal({
	                        title: 'Error!',
	                        text: 'System experiencing an Internal Server Error.',
	                        type: 'error',
	                        showConfirmButton: false
	                    })
	                    location.reload();
			        } else if (XMLHttpRequest.readyState == 0) {
			            swal({
	                        title: 'Error!',
	                        text: 'Network error (i.e. connection refused, access denied due to CORS, etc.)',
	                        type: 'error',
	                        showConfirmButton: false
	                    })
	                    location.reload();
			        } else {
			            swal({
	                        title: 'Error!',
	                        text: 'Something went wrong, please try again.',
	                        type: 'error',
	                        showConfirmButton: false
	                    })
	                    location.reload();
			        }
			    }
            });
        }
    });
}

function multipledoc(id){
	var base_url = $('body').attr('data-url');
    $('#div-contractfiles').empty();
    $.ajax({
        url: base_url+"/client/crm/customer/"+id+"/contractdoc",
        type: 'GET',
        dataType: 'json',
        success: function(result){
            var datas = result.files;
            var a = 1;
            $('#multipledoc-modal').modal();
            for(i=0; i<result.arrcount; ++i){
                $('#div-contractfiles').append(
                '<div class="btn-group" role="group" style="padding: 3px 0px;">'+
                    '<a href="http://quantumx-crm-bucket.s3.amazonaws.com/uploads/customer/contract/'+datas[i]+'" class="btn btn-primary btn-xs file-'+i+'" target="_blank"><i class="fa fa-download fa-fw"></i> '+datas[i]+'</a>&nbsp;'+
                '</div>'
                );
                a++;
            }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
        	console.log(XMLHttpRequest.status);
	        if (XMLHttpRequest.status == 500) {
	            swal({
                    title: 'Error!',
                    text: 'System experiencing an Internal Server Error.',
                    type: 'error',
                    showConfirmButton: false
                })
                location.reload();
	        } else if (XMLHttpRequest.readyState == 0) {
	            swal({
                    title: 'Error!',
                    text: 'Network error (i.e. connection refused, access denied due to CORS, etc.)',
                    type: 'error',
                    showConfirmButton: false
                })
                location.reload();
	        } else {
	            swal({
                    title: 'Error!',
                    text: 'Something went wrong, please try again.',
                    type: 'error',
                    showConfirmButton: false
                })
                location.reload();
	        }
	    }
    });
}
</script>
@endsection