@extends('Client::layouts')
@section('page-body')
<section class="content-header">
    <h1>
        Reports
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li>Reports</li>
        <li class="active">Proposal Approval Report</li>
    </ol>
</section>
<section class="content">
	<div class="col-md-12">
		<div class="box box-primary">
			<div class="box-header with-border">
				<div class="pull-left" id="header-report">
					Proposal Approval Report
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
				{!! Form::open(['url'=>'client/crm/reports/proposalapproval','id'=>'reportForm','class'=>'role-form']) !!}
				<div class="row">
					<div class="col-lg-6">
						<div class="form-group">
							<label>Date Added <i class="fa fa-asterisk error" style="font-size: 11px"></i></label>
							<div class="input-group">
	            				<input type="text" class="form-control" name="date_added" id="date_added" placeholder="Date Added">
	            				<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
	            			</div>
						</div>
					</div>
					<div class="col-lg-6">
						<div class="form-group">
							<label>Product</label>
							{!! Form::select('product_id', $productList, '', ['class'=>'form-control']) !!}
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-6">
						<div class="form-group">
							<label>Status</label>
							{!! Form::select('status', $statusList, '', ['class'=>'form-control']) !!}
						</div>
					</div>
					<div class="col-lg-6">
						<div class="form-group">
							<label>Chances</label>
							{!! Form::select('chances', $chanceList, '', ['class'=>'form-control']) !!}
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-6">
						<div class="form-group">
							<label>Approver</label>
							{!! Form::select('approver', $userList, '', ['class'=>'form-control']) !!}
						</div>
					</div>
					<div class="col-lg-6">
						<div class="form-group">
							<label>Proposal Status</label>
							<div style="margin-top: 7px;">
								<label class="checkbox-inline">
									<input type="checkbox" name="proposal_status[]" value="Pending" checked> Pending
								</label>
								<label class="checkbox-inline">
									<input type="checkbox" name="proposal_status[]" value="Approved" checked> Approved
								</label>
								<label class="checkbox-inline">
									<input type="checkbox" name="proposal_status[]" value="For Revision" checked> For Revision
								</label>
								<div id="status_validate"></div>
							</div>
						</div>
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
				<table class="table table-hover table-striped" id="proposal-table">
					<thead>
						<tr>
							<th class="table-sort" data-sort-method="none" style="width:10%">Proposal</th>
							<th class="table-sort" data-header="name" style="width:15%">Customer</th>
							<th class="table-sort" data-sort-method="none" style="width:10%">Product</th>
							<th class="table-sort" data-header="date_added" style="width:10%">Date</th>
							<th class="table-sort" data-sort-method="none" style="width:10%">Status</th>
							<th class="table-sort" data-sort-method="none" style="width:10%">Chance</th>
							<th class="table-sort" data-sort-method="none" style="width:10%">Approver</th>
						</tr>
					</thead>
					<tbody id="table-proposal"></tbody>
				</table>
				<input type="hidden" id="default-link">
			</div>
		</div>
	</div>
</section>
<div style="clear: both;"></div>
@endsection

@section('page-css')
<link rel="stylesheet" type="text/css" href="{{ asset('vendor/tablesort/tablesort.css') }}">
@stop

@section('page-js')
<script type="text/javascript" src="{{ asset('vendor/tablesort/src/tablesort.js') }}"></script>
<script src="{{ asset('vendor/tablesort/src/sorts/tablesort.number.js') }}"></script>
<script src="{{ asset('vendor/tablesort/src/sorts/tablesort.date.js') }}"></script>
<script>
  new Tablesort(document.getElementById('proposal-table'));
</script>
{!! Html::script('vendor/tokeninput/jquery.tokeninput.js') !!}
<script type="text/javascript">
$(document).ready(function() {
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
            date_added: { required : true },
            'proposal_status[]': {
                required: true
            }
        },
        messages : {
            date_added: "Please provide date added.",
            'proposal_status[]': "Please provide proposal status."
        },
        errorElement: 'label',
        errorClass: 'error',
        errorPlacement: function(error, element) {
            if(element.parent('.input-group').length) {
                error.insertAfter(element.parent());
            } else {
                error.insertAfter(element);
            }
            var name = $(element).attr("name");
            if(name == 'proposal_status[]'){
	            error.appendTo($("#status_validate"));
	        }
        },
        submitHandler: function () {
        	$('#reportbtn').attr('disabled', true);
            $('#reportbtn').html('<i class="fa fa-spinner fa-spin"></i> Generating');
            var data = $('#reportForm').serializeArray();
            $.ajax({
                url: "{{ url('client/crm/reports/proposalapproval') }}",
                type: 'POST',
                dataType: 'json',
                data: data,
                success: function(result){
                    $('#reportbtn').removeAttr('disabled');
                    $('#reportbtn').html('Generate');
                    $('#report-filter').slideUp('400');
                    $('#report-display').fadeIn('400');
                    $('#header-report').html('<h4>Proposal Approval Report '+result.from_display+' - '+result.to_display+'</h4>');
                    $('#header-button').show();
                    $('#export-csv').attr('href', result.link+'xxxcsv');
                    $('#default-link').val(result.link+'xxxcsv');
                    // $('#export-pdf').attr('href', result.link+'xxxpdf');
                    $('#div-totalcount').html('Total of '+result.totalcount+' records were found.');
                    var datas = result.proposals;         
            		$.each(datas, function(index, data) {
            			var i = index+1;
                    	$('#table-proposal').append(
                    		'<tr>'+
                    			// '<td class="text-center">'+i+'</td>'+
                    			'<td>'+data.name+'</td>'+
                    			'<td>'+((data.customer!=null) ? data.customer.name : '')+'</td>'+
                    			'<td>'+((data.activitytype!=null) ? data.activitytype.name : '')+'</td>'+
                    			'<td>'+data.created_at_display+'</td>'+
                    			'<td>'+((data.proversionfirst!=null) ? data.proversionfirst.status : '')+'</td>'+
                    			'<td>'+((data.proversionfirst!=null) ? data.proversionfirst.chances : '')+'%</td>'+
                    			'<td>'+((data.userapprover != null) ? data.userapprover.name : '')+'</td>'+
                    		'</tr>'
                    	);
                    });
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
    	$('#header-report').html('Proposal Report');
    	$('#table-proposal').empty();
    	$("[name='csv']").val('');
        $("[name='pdf']").val('');
        $('.role-form').attr('id', 'reportForm');
    });

});
</script>
@endsection