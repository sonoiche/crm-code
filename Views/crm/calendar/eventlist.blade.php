<div class="modal-header">
    <h4 class="modal-title" id="myModalLabel"><span class="fa fa-calendar"></span> &nbsp;&nbsp;Schedule on {{ $eventdate }}</h4>
</div>
<div class="modal-body">
	<table class="table table-hover">
		<thead>
			<th style="width:10%">Time</th>
			<th style="width:15%">Company</th>
			<th style="width:20%">Remarks</th>
			<th style="width:15%">Attendee</th>
			<th style="width:5%"></th>
		</thead>
		<tbody>
			@foreach($events as $key => $event)
			<tr>
				<td>{{ $event->event_date_display }}</td>
				<td>{{ $event->company }}</td>
				<td>{{ $event->details }}</td>
				<td>{{ $event->attendee_display }}</td>
				<td>
				@if($event->user_id == \Auth::user()->id)
					<a onclick="viewEvent({{ $event->id }})" style="cursor:pointer" class="btn btn-success btn-sm">Edit</a>
				@endif
				</td>
			</tr>
			@endforeach
		</tbody>
	</table>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-default pull-left" data-dismiss="modal" id="closeeventlistbtn">Close</button>
</div>

<script>
$(document).ready(function() {
	$('#closeeventlistbtn').click(function() {
		$('#vieweventModal').removeData('bs.modal');
	});
});
</script>