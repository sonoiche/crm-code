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
	            	<!-- <div class="form-group row">
	            		<label class="col-sm-3" style="margin-top: 6px;">Services</label>
	            		<div class="col-sm-9">
		            		<div class="input-group">
			            		{!! Form::select('service_id', $serviceList, '', ['class'=>'form-control','id'=>'service_id_form2']) !!}
			            		<div class="input-group-btn">
			            			<a href="javascript:void(0)" class="btn btn-primary btn-md" id="modalaservice">Add Service</a>
			            		</div>
		            		</div>
		            	</div>
	            	</div> -->
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
						<div class="col-sm-9" id="div-assignto">
							<input type="text" class="form-control" name="assign_to" id="assignto" placeholder="Assign To">
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3 form-control-label" style="margin-top: 6px;">FYI</label>
						<div class="col-sm-9" id="div-fyi">
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
					<input type="hidden" name="customer_id" value="{{ hashid($customer->id) }}">
					<input type="hidden" name="activity_id">
					<button type="submit" class="btn btn-primary plupload-btn" id="addactivitybtn">Save Changes</button>
					<button type="button" class="btn btn-secondary" data-dismiss="modal" id="clean-directory">Close</button>
				</div>
			</form>
		</div>
	</div>
</div>
<div class="modal fade" id="modal-activity-type" data-backdrop="static" data-keyboard="false">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
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
						<label id="activitytype-name-duplicate" class="error" for="name" style="display: none"></label>
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
					<tbody id="tbody-activitytype">
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
<div class="modal fade" id="modal-service" data-backdrop="static" data-keyboard="false">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
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
						<label id="service-name-duplicate" class="error" for="name" style="display: none"></label>
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
					<tbody id="tbody-service">
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

<div class="modal fade" id="multipledoc-modal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">Activity Multiple Documents</h4>
			</div>
			<div class="modal-body">
				<div id="div-activityfiles"></div>
			</div>
		</div>
	</div>
</div>