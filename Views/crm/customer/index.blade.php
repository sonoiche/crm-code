@extends('Client::layouts')
@section('page-body')
<section class="content-header">
    <h1>
        Customer
        <small>Search Customer</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Search Customer</li>
    </ol>
</section>
<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box box-primary">
				<div class="box-header with-border">
					<div class="pull-right">
						<a href="{{ url('client/crm/customer/create') }}" class="btn btn-primary btn-sm">Create New Customer</a>
					</div>
				</div>
				<div class="box-body">
					<form id="searchForm" class="form-inline">
						{!! csrf_field() !!}
						<div class="form-group">
							<div class="input-group">
								<span class="input-group-addon">
									<div class="radio">
	  									<label style="padding-top:3px">
							        	{!! Form::radio('searchtype','1',''.(isset($searchtype) && $searchtype==1) ? "1" : "".'',['style'=>'vertical-align:middle']) !!} &nbsp;&nbsp;Start of the Name
							    		</label>
							    	</div>
							    </span>
								<span class="input-group-addon">
									<div class="radio">
	  									<label style="padding-top:3px">
							        	{!! Form::radio('searchtype','2',''.(isset($searchtype) && $searchtype==2) ? "1" : "1".'',['style'=>'vertical-align:middle']) !!} &nbsp;&nbsp;Any part of the Name
							    		</label>
							    	</div>
							    </span>
								{!! Form::text('searchtext', (isset($_GET['name']) && $_GET['name'] != '') ? $_GET['name'] : '', ['class'=>'form-control','placeholder'=>'Search Customer','style'=>'width:300px','id'=>'searchtext']) !!}
							</div>
						</div>
						<button type="button" id="searchcustomerbtn" class="btn btn-primary btn-flat">Search</button>
					</form>
					<br><br>
					<table class="table table-striped table-hover">
						<thead>
							<tr>
								<th>Customer Name</th>
								<th>Telephone</th>
								<th>Latest Activity</th>
								<th></th>
							</tr>
						</thead>
						<tbody id="table-tbody">
							@if(isset($statusname) && $statusname != '')
							@foreach($customers as $customer)
							<tr {{ ($customer->status == 'Inactive') ? 'style=background:#f2dede' : 'style=background:#fafafa' }}>
		            			<td style="width:20%"><a href="{{ url('client/crm/customer', $customer->hashid) }}" target="_blank">{{ $customer->name }}</a></td>
		            			<td style="width:10%">{{ $customer->telephone.' '.$customer->local }}</td>
		            			<td style="width:35%">
		            				<table>
										<tr>
											<td style="width:90px"><b>Activity</b></td>
											<td>: {{ (count($customer->latestactivity) && count($customer->latestactivity[0]->activitytype)) ? $customer->latestactivity[0]->activitytype->name : '' }}</td>
										</tr>
										<tr>
											<td><b>Date Added</b></td>
											<td>: {{ count($customer->latestactivity) ? $customer->latestactivity[0]->created_at_display : '' }}</td>
										</tr>
										<tr>
											<td style="vertical-align: top"><b>Remarks</b></td>
											<td>{{ count($customer->latestactivity) ? $customer->latestactivity[0]->remarks : '' }}</td>
										</tr>
									</table>
		            			</td>
		            			<td style="width:5%">
		            				<div class="input-group-btn">
										<a href="{{ url('client/crm/customer',$customer->hashid) }}/activity" class="btn btn-success btn-sm" data-toggle="tooltip" data-placement="top" title="View Activity"><i class="fa fa-cube"></i></a>
										<a href="javascript:void(0)" onclick="createactivity('{{ $customer->hashid }}')" class="btn btn-primary btn-sm" data-toggle="tooltip" data-placement="top" title="Add New Activity"><i class="fa fa-plus-square"></i></a>
		            				</div>
		            			</td>
		            		</tr>
		            		@endforeach
							@else
							<tr>
								<td colspan="4" class="text-center">No data available</td>
							</tr>
							@endif
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</section>
<div class="modal fade" id="modal-activity" data-backdrop="static" data-keyboard="false">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Create Activity</h4>
			</div>
			<form id="activityForm" enctype="multipart/form-data">
				<div class="modal-body">
					<div class="form-group row">
	            		<label class="col-sm-3" style="margin-top: 6px;">Activity <i class="fa fa-asterisk error" style="font-size: 11px"></i></label>
	            		<div class="col-sm-9">
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
	            		<div class="col-sm-9">
		            		<div class="input-group">
			            		{!! Form::select('next_activity_type', $activityList, '', ['class'=>'form-control','id'=>'next_activity_type']) !!}
			            		<div class="input-group-btn">
			            			<a href="javascript:void(0)" class="btn btn-primary btn-md modalactivity">Add Activity</a>
			            		</div>
		            		</div>
		            	</div>
	            	</div>
	            	<div class="form-group row">
	            		<label class="col-sm-3" style="margin-top: 6px;">Due Date</label>
	            		<div class="col-sm-9">
	            			<div class="input-group">
	            				<input type="text" class="form-control" name="due_date" id="due_date" placeholder="Due Date">
	            				<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
	            			</div>
	            		</div>
	            	</div>
					<div class="form-group row">
						<label class="col-sm-3 form-control-label" style="margin-top: 6px;">Assigned To</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" name="assign_to" id="assignto" placeholder="Assign To">
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3 form-control-label" style="margin-top: 6px;">FYI</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" name="fyi" id="fyi" placeholder="FYI">
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3 form-control-label" style="margin-top: 6px;">Remarks <i class="fa fa-asterisk error" style="font-size: 11px"></i></label>
						<div class="col-sm-9">
							<textarea class="form-control" name="remarks" style="resize:none; height: 80px"></textarea>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3"></label>
						<div class="col-sm-9" id="div-activity-files" style="display: hidden">
							
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3" style="margin-top: 6px;">File Upload</label>
						<div class="col-sm-9">
							<div id="activity-uploaded">
								<p>Your browser doesn\'t have Flash, Silverlight or HTML5 support.</p>
							</div>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3" style="margin-top: 6px;">File available</label>
						<div class="col-sm-9">
							{!! Form::select('file_permission', ['Everyone' => 'Everyone', 'Only Me' => 'Only Me'], '', ['class' => 'form-control']) !!}
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<input type="hidden" name="customer_id">
					<input type="hidden" name="activity_id">
					<button type="submit" class="btn btn-primary" id="addactivitybtn">Save Changes</button>
					<button type="button" class="btn btn-secondary" data-dismiss="modal" id="clean-directory">Close</button>
				</div>
			</form>
		</div>
	</div>
</div>
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
								<a href="javascript:void(0)" onclick="deleteactivity('{{ hashid($activity_type->id) }}')" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></a>
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
								<a href="javascript:void(0)" onclick="deleteservice('{{ hashid($service->id) }}')" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></a>
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
{!! Html::script('vendor/multiupload/plupload.full.min.js') !!}
{!! Html::script('vendor/multiupload/jquery.ui.plupload.js') !!}
{!! Html::script('vendor/tokeninput/jquery.tokeninput.js') !!}
{!! Html::script('crmjs/crm-search.js?ver='.env('FILE_VERSION')) !!}
@endsection