<div class="modal fade" id="modal-event" data-backdrop="static" data-keyboard="false" style="z-index: 1111">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
					<span class="sr-only">Close</span>
				</button>
				<h4 class="modal-title">Add Event</h4>
				<small style="color:red">All fields with ( <i class="fa fa-asterisk error" style="font-size: 11px"></i>) are required</small>
			</div>
			<form id="eventForm">
				<div class="modal-body">
					<div class="form-group row">
						<label class="col-sm-3 form-control-label">Date <i class="fa fa-asterisk error" style="font-size: 11px"></i></label>
						<div class="col-sm-9">
							<div class="input-group">
		                        <div class="input-group-addon" style="padding:0 10px 0 10px">
		                        	<i class="fa fa-calendar"></i>
		                        </div>
								<input class="form-control datemask" id="event_date" name="date" type="text">
							</div>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3 form-control-label">Time <i class="fa fa-asterisk error" style="font-size: 11px"></i></label>
						<div class="col-sm-9">
							<div class="input-group bootstrap-timepicker">
		                        <div class="input-group-addon" style="padding:0 10px 0 10px">
		                        	<i class="fa fa-clock-o"></i>
		                        </div>
								<input class="form-control timepicker" name="time" type="text">
							</div>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3 form-control-label">Customer <i class="fa fa-asterisk error" style="font-size: 11px"></i></label>
						<div class="col-sm-9">
							<input type="text" name="customer_id" id="customer_id" class="form-control">
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3 form-control-label">Activity <i class="fa fa-asterisk error" style="font-size: 11px"></i></label>
						<div class="col-sm-9">
							<div class="input-group">
			            		{!! Form::select('activity_id', $activityList, '', ['class'=>'form-control']) !!}
			            		<div class="input-group-btn">
			            			<a href="javascript:void(0)" class="btn btn-primary btn-md modalactivity">Add Activity</a>
			            		</div>
		            		</div>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3 form-control-label">Attendee</label>
						<div class="col-sm-9">
							<input type="text" name="user_ids" id="user_ids" class="form-control">
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3 form-control-label">Inform Also</label>
						<div class="col-sm-9">
							<input type="text" name="fyi" id="fyi" class="form-control">
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3 form-control-label">Remarks <i class="fa fa-asterisk error" style="font-size: 11px"></i></label>
						<div class="col-sm-9">
							<textarea class="form-control" name="details" style="height: 120px; resize: none"></textarea>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<input type="hidden" name="event_id">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<a href="javascript:void(0)" class="btn btn-danger" id="deleteeventbtn">Delete</a>
					<button type="submit" id="addeventbtnx" class="btn btn-primary">Save Changes</button>
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
<div class="modal fade bs-example-modal-md" id="vieweventModal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            
		</div>
	</div>
</div>