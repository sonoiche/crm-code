@extends('Client::layouts')
@section('page-body')
<section class="content-header">
    <h1>
        Reports
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li>Reports</li>
        <li class="active">Lead Opportunity Report</li>
    </ol>
</section>
<section class="content">
	<div class="col-md-12">
		<div class="box box-primary">
			<div class="box-header with-border">
				<div class="pull-left" id="header-report">
					Lead Opportunity Report
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
			@if(!isset($_GET['report-type']) || (isset($_GET['report-type']) && $_GET['report-type'] == 'normal'))
			<div class="box-body" id="report-filter">
				{!! Form::open(['url'=>'client/crm/reports/proposal/leadstore','id'=>'reportForm','class'=>'role-form']) !!}
				<div class="row">
					<div class="col-lg-4">
						<div class="form-group">
							<label>Report Type</label><br>
							<div style="padding-top: 5px">
								<label class="radio-inline">
									<input type="radio" name="report_type" onclick="reporttype(1)" checked="checked"> Normal Report
								</label>
								<label class="radio-inline">
									<input type="radio" name="report_type" onclick="reporttype(2)"> With Product Summary
								</label>
							</div>
						</div>
					</div>
					<div class="col-lg-4">
						<div class="form-group">
							<label>Date Sent <i class="fa fa-asterisk error" style="font-size: 11px"></i></label>
							<div class="input-group">
	            				<input type="text" class="form-control" name="date_added" id="date_added" placeholder="Date Added">
	            				<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
	            			</div>
						</div>
					</div>
					<div class="col-lg-4">
						<div class="form-group">
							<label>Chances</label>
							{!! Form::select('chance', $chanceList, '', ['class'=>'form-control']) !!}
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-6">
						<div class="form-group">
							<label>Customer</label>
							<input type="text" class="form-control" name="customer_id" id="customer_id">
						</div>
					</div>
					<div class="col-lg-6">
						<div class="form-group">
							<label>User</label>
							<input type="text" class="form-control" name="user_id" id="user_id">
							<small class="error">Leave blank to generate all users</small>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<div class="text-center">
							<input type="hidden" name="csv">
							<input type="hidden" name="pdf">
							<input type="hidden" name="rtype" value="1">
							<button type="submit" class="btn btn-primary btn-md" id="reportbtn">Generate Report</button>
						</div>
					</div>
				</div>
				{!! Form::close() !!}
			</div>
			@endif
			@if(isset($_GET['report-type']) && $_GET['report-type'] == 'summary')
			<div class="box-body" id="report-filter-summary">
				{!! Form::open(['url'=>'client/crm/reports/proposal/leadstore','id'=>'reportForm2','class'=>'role-form']) !!}
				<div class="row">
					<div class="col-lg-6">
						<div class="form-group">
							<label>Report Type</label><br>
							<div style="padding-top: 5px">
								<label class="radio-inline">
									<input type="radio" name="report_type" onclick="reporttype(1)"> Normal Report
								</label>
								<label class="radio-inline">
									<input type="radio" name="report_type" onclick="reporttype(2)" checked="checked"> With Product Summary
								</label>
							</div>
						</div>
					</div>
					<div class="col-lg-6">
						<div class="form-group">
							<label>Date Sent <i class="fa fa-asterisk error" style="font-size: 11px"></i></label>
							<div class="input-group">
	            				<input type="text" class="form-control" name="date_added" id="date_added2" placeholder="Date Added">
	            				<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
	            			</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-6">
						<div class="form-group">
							<label>Product Type</label>
							{!! Form::select('product_id', $productList, '', ['class'=>'form-control','id'=>'product_type2']) !!}
						</div>
					</div>
					<div class="col-lg-6">
						<div class="form-group">
							<label>User</label>
							<input type="text" class="form-control" name="user_id" id="user_id2">
							<small class="error">Leave blank to generate all users</small>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<div class="text-center">
							<input type="hidden" name="csv">
							<input type="hidden" name="pdf">
							<input type="hidden" name="rtype" value="2">
							<button type="submit" class="btn btn-primary btn-md" id="reportbtn2">Generate Report</button>
						</div>
					</div>
				</div>
				{!! Form::close() !!}
			</div>
			@endif
			<div class="box-body" id="report-display" style="display: none">
				
			</div>
		</div>
	</div>
</section>
<div style="clear: both;"></div>
@endsection

@section('page-js')
{!! Html::script('vendor/tokeninput/jquery.tokeninput.js') !!}
<script type="text/javascript">
$(document).ready(function() {
	var url = "{{ url('/') }}";
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

	$('#date_added2').daterangepicker(
	{
		ranges: {
			'Today': [moment(), moment()],
			'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
			'Last 7 Days': [moment().subtract(6, 'days'), moment()],
			'Last 30 Days': [moment().subtract(29, 'days'), moment()],
			'This Month': [moment().startOf('month'), moment().endOf('month')],
			'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
		},
		startDate: moment().subtract(29, 'days'),
		endDate: moment(),
		opens: 'left'
		},
		function (start, end) {
			$('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
		}
	);

	if($("[name='rtype']").val() == 1){
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
	            $('#report-display').html('');
	            var data = $('#reportForm').serializeArray();
	            $.ajax({
	                url: "{{ url('client/crm/reports/lead') }}",
	                type: 'POST',
	                dataType: 'json',
	                data: data,
	                success: function(result){
	                    $('#reportbtn').removeAttr('disabled');
	                    $('#reportbtn').html('Generate Report');
	                    $('#report-filter, #report-filter-summary').slideUp('400');
	                    $('#report-display').fadeIn('400');
	                    $('#header-report').html('<h4>Lead Opportunity Report '+result.from_display+' - '+result.to_display+'</h4>');
	                    $('#header-button').show();
	                    $('#export-csv').attr('href', result.link+'xxxcsv');
	                    // $('#export-pdf').attr('href', result.link+'xxxpdf');
	                    $('#report-display').html(result.table);
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
	}

	if($("[name='rtype']").val() == 2){
	    $('#reportForm2').validate({
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
	        	$('#reportbtn2').attr('disabled', true);
	            $('#reportbtn2').html('<i class="fa fa-spinner fa-spin"></i> Generating');
	            $('#report-display').html('');
	            var data = $('#reportForm2').serializeArray();
	            $.ajax({
	                url: "{{ url('client/crm/reports/lead') }}",
	                type: 'POST',
	                dataType: 'json',
	                data: data,
	                success: function(result){
	                    $('#reportbtn2').removeAttr('disabled');
	                    $('#reportbtn2').html('Generate Report');
	                    $('#report-filter, #report-filter-summary').slideUp('400');
	                    $('#report-display').fadeIn('400');
	                    $('#header-report').html('<h4>Lead Opportunity Report with Product Summary '+result.from_display+' - '+result.to_display+'</h4>');
	                    $('#header-button').show();
	                    $('#export-csv').attr('href', result.link+'xxxcsv');
	                    // $('#export-pdf').attr('href', result.link+'xxxpdf');
	                    $('#report-display').html(result.table);
	                    
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
	}

    $("#customer_id").tokenInput(url+ "/client/crm/customer/customerlist", {
        tokenLimit: 1,
        hintText: "Type in customer name."
    });

    $("#user_id").tokenInput(url+ "/client/crm/customer/userlist", {
        theme: "facebook",
        preventDuplicates: true,
        hintText: "Type in user's name"
    });

    $("#user_id2").tokenInput(url+ "/client/crm/customer/userlist", {
        theme: "facebook",
        preventDuplicates: true,
        hintText: "Type in user's name"
    });

    $('#filterbtn').click(function() {
    	$('#header-button').hide();
        $('#report-display').slideUp('400');
    	$('#report-filter').fadeIn('400');
    	$('#report-filter-summary').fadeIn('400');
    	$('#header-report').html('Proposal Report');
    	$('#table-proposal').empty();
    	$("[name='csv']").val('');
        $("[name='pdf']").val('');
        // $('.role-form').attr('id', 'reportForm');
    });

    // $('#chknormal').attr('checked');

});

function reporttype(type){
	var url = "{{ url('/') }}";
	if(type == 1){
		window.location.href=url+'/client/crm/reports/lead?report-type=normal';
	} else {
		window.location.href=url+'/client/crm/reports/lead?report-type=summary';
	}
}
</script>
@endsection