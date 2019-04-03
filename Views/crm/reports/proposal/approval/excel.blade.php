<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<table class="table table-hover table-striped">
		<thead>
			<tr>
				<th style="width:10%">Proposal</th>
				<th style="width:15%">Customer</th>
				<th style="width:10%">Product</th>
				<th style="width:10%">Date</th>
				<th style="width:10%">Status</th>
				<th style="width:10%">Chance</th>
				<th style="width:10%">Approver</th>
			</tr>
		</thead>
		<tbody>
			@foreach($proposals as $key => $result)
			<tr>
    			<td>{{ $result->name }}</td>
    			<td>{{ (count($result->customer)) ? $result->customer->name : '' }}</td>
    			<td>{{ (count($result->activitytype)) ? $result->activitytype->name : '' }}</td>
    			<td>{{ $result->created_at_display }}</td>
    			<td>{{ $result->pro_status }}</td>
    			<td>{{ $result->pro_chance }}</td>
    			<td>{{ (count($result->userapprover)) ? $result->userapprover->name : '' }}</td>
    		</tr>
    		@endforeach
		</tbody>
	</table>
</html>