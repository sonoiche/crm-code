<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<table class="table table-hover table-striped">
		<thead>
			<tr>
				<th style="width: 10%">Date Added</th>
				<th style="width: 15%">Company</th>
				<th style="width: 10%">Activity</th>
				<th style="width: 10%">Person in Charge</th>
				<th style="width: 30%">Remarks</th>
			</tr>
		</thead>
		<tbody>
			@foreach($activity as $key => $result)
			<tr>
    			<td>{{ $result->created_at_display }}</td>
    			<td>{{ $result->name }}</td>
    			<td>{{ $result->activityname }}</td>
    			<td>{{ $result->username }}</td>
    			<td>{{ $result->remarks }}</td>
    		</tr>
    		@endforeach
		</tbody>
	</table>
</html>