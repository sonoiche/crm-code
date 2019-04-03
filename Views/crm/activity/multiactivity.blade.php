@extends('Client::layouts')
@section('page-body')
<section class="content-header">
    <h1>Add Activity to Multiple Customers</h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li>Customer</li>
        <li class="active">Activity</li>
    </ol>
</section>
<section class="content">
	<div class="col-md-12">
		<div class="box box-primary">
			<div class="box-header with-border">
				<div class="pull-left">
					Create New Activity
				</div>
				<div class="pull-right">
					<a href="{{ url('client/crm/customer') }}" class="btn btn-primary btn-sm">Search Customer</a>
				</div>
			</div>
			<div class="box-body">
				<form id="activitymultipleForm" enctype="multipart/form-data">
					<div class="form-group row">
						<label class="col-sm-3 form-control-label" style="margin-top: 6px;">Customer <i class="fa fa-asterisk error" style="font-size: 11px"></i></label>
						<div class="col-sm-6" id="div-assignto">
							<input type="text" class="form-control" name="customer_ids" id="customer_ids" placeholder="Customer">
						</div>
					</div>
					<div class="form-group row">
	            		<label class="col-sm-3" style="margin-top: 6px;">Activity <i class="fa fa-asterisk error" style="font-size: 11px"></i></label>
	            		<div class="col-sm-6">
		            		<div class="input-group">
			            		{!! Form::select('activity_type', $activityList, '', ['class'=>'form-control','id'=>'activity_type']) !!}
			            		<div class="input-group-btn">
			            			<a href="javascript:void(0)" class="btn btn-primary btn-md modalactivity">Add Activity</a>
			            		</div>
		            		</div>
		            	</div>
	            	</div>
	            	<div class="form-group row">
	            		<label class="col-sm-3" style="margin-top: 6px;">Next Activity</label>
	            		<div class="col-sm-6">
		            		<div class="input-group">
			            		{!! Form::select('next_activity_type', $activityList, '', ['class'=>'form-control','id'=>'next_activity_type']) !!}
			            		<div class="input-group-btn">
			            			<a href="javascript:void(0)" class="btn btn-primary btn-md modalactivity">Add Activity</a>
			            		</div>
		            		</div>
		            	</div>
	            	</div>
	            	<!-- <div class="form-group row">
	            		<label class="col-sm-3" style="margin-top: 6px;">Services</label>
	            		<div class="col-sm-6">
		            		<div class="input-group">
			            		{!! Form::select('service_id', $serviceList, '', ['class'=>'form-control','id'=>'service_id_form']) !!}
			            		<div class="input-group-btn">
			            			<a href="javascript:void(0)" class="btn btn-primary btn-md" id="modalaservice">Add Service</a>
			            		</div>
		            		</div>
		            	</div>
	            	</div> -->
	            	<div class="form-group row">
	            		<label class="col-sm-3" style="margin-top: 6px;">Due Date</label>
	            		<div class="col-sm-6">
	            			<div class="input-group">
	            				<input type="text" class="form-control" name="due_date" id="due_date" placeholder="Due Date">
	            				<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
	            			</div>
	            		</div>
	            	</div>
					<div class="form-group row">
						<label class="col-sm-3 form-control-label" style="margin-top: 6px;">Assigned To</label>
						<div class="col-sm-6" id="div-assignto">
							<input type="text" class="form-control" name="assign_to" id="assignto" placeholder="Assign To">
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3 form-control-label" style="margin-top: 6px;">FYI</label>
						<div class="col-sm-6" id="div-fyi">
							<input type="text" class="form-control" name="fyi" id="fyi" placeholder="FYI">
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3 form-control-label" style="margin-top: 6px;">Remarks <i class="fa fa-asterisk error" style="font-size: 11px"></i></label>
						<div class="col-sm-6">
							<textarea class="form-control" name="remarks" style="resize:none; height: 80px"></textarea>
						</div>
					</div>
					<div class="form-group row">
						<div class="col-sm-offset-3 col-sm-9">
							<button type="submit" class="btn btn-primary" id="addactivitybtn">Save Changes</button>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal" id="clean-directory">Close</button>
			</div>
		</div>
	</div>
</section>
<div style="clear: both;"></div>
<div class="modal fade" id="modal-activity-type">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
					<span class="sr-only">Close</span>
				</button>
				<h4 class="modal-title">Activity Type</h4>
			</div>
			<div class="modal-body">
				<form id="activity-typeForm">
					{!! csrf_field() !!}
					<input type="hidden" name="activity_id" id="activity_id">
					<div class="form-group">
						<div class="input-group">
							<input type="text" name="name" id="activity_name" class="form-control" placeholder="Activity Name">
							<div class="input-group-btn">
								<button type="submit" class="btn btn-primary">Save Changes</button>
							</div>
						</div>
						<label id="name-duplicate" class="error" for="name" style="display: none"></label>
					</div>
				</form>
				<table class="table table-striped">
					<thead>
						<tr>
							<th style="width: 5%; text-align: center">#</th>
							<th style="width: 50%">Name</th>
							<th style="width: 10%">Action</th>
						</tr>
					</thead>
					<tbody>
						@foreach($activity_types as $key => $activity_type)
						<tr id="activity-row-{{ hashid($activity_type->id) }}">
							<td style="text-align: center">{{ $key+1 }}</td>
							<td>{{ $activity_type->name }}</td>
							<td>
								<a href="javascript:void(0)" onclick="editactivity('{{ hashid($activity_type->id) }}')" class="btn btn-success btn-xs"><i class="fa fa-pencil"></i></a>
								<!-- <a href="javascript:void(0)" onclick="deleteactivity('{{ hashid($activity_type->id) }}')" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></a> -->
							</td>
						</tr>
						@endforeach
					</tbody>
				</table>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="modal-service">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
					<span class="sr-only">Close</span>
				</button>
				<h4 class="modal-title">Service Type</h4>
			</div>
			<div class="modal-body">
				<form id="serviceForm">
					{!! csrf_field() !!}
					<input type="hidden" name="service_id" id="service_id">
					<div class="form-group">
						<div class="input-group">
							<input type="text" name="name" id="service_name" class="form-control" placeholder="Service Name">
							<div class="input-group-btn">
								<button type="submit" class="btn btn-primary">Save Changes</button>
							</div>
						</div>
						<label id="name-duplicate" class="error" for="name" style="display: none"></label>
					</div>
				</form>
				<table class="table table-striped">
					<thead>
						<tr>
							<th style="width: 5%; text-align: center">#</th>
							<th style="width: 50%">Name</th>
							<th style="width: 10%">Action</th>
						</tr>
					</thead>
					<tbody>
						@foreach($services as $key => $service)
						<tr id="service-row-{{ hashid($service->id) }}">
							<td style="text-align: center">{{ $key+1 }}</td>
							<td>{{ $service->name }}</td>
							<td>
								<a href="javascript:void(0)" onclick="editservice('{{ hashid($service->id) }}')" class="btn btn-success btn-xs"><i class="fa fa-pencil"></i></a>
								<!-- <a href="javascript:void(0)" onclick="deleteservice('{{ hashid($service->id) }}')" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></a> -->
							</td>
						</tr>
						@endforeach
					</tbody>
				</table>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
@endsection

@section('page-js')
@include('Client::crm.activity.activity-js')
<script type="text/javascript">
$(document).ready(function() {
	$('#activitymultipleForm').validate({
		ignore: [],
        rules: {
        	customer_ids: { required : true },
            activity_type: { required : true },
            remarks: { required : true }
        },
        messages : {
            customer_ids: "Please provide customers.",
            activity_type: "Please provide activity type.",
            remarks: "Please provide remarks."
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
            var data = $('#activitymultipleForm').serializeArray();
            $('#addactivitybtn').attr('disabled', true);
            $('#addactivitybtn').html('<i class="fa fa-spinner fa-spin"></i> Saving');
            $.ajax({
                url: "{{ url('client/crm/customer/storemultipleactivity') }}",
                type: 'POST',
                dataType: 'json',
                data: data,
                success: function(result){
                    swal({
                        title: 'Success!',
                        text: 'Activity has been saved.',
                        type: 'success',
                        showConfirmButton: false
                    })
                    setTimeout(function(){ 
                        swal.close();
                        $('#addactivitybtn').removeAttr('disabled');
                    	$('#addactivitybtn').html('Save Changes');
                    	$('#activitymultipleForm')[0].reset();
                    	$("#customer_ids").tokenInput("clear");
                    	$("#assignto").tokenInput("clear");
                    	$("#fyi").tokenInput("clear");
                    }, 1000);
                }
            });
        }
    })
});
</script>
@endsection