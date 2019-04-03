<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<table class="table table-hover table-striped">
		<thead>
			<tr>
				<th style="width: 15%">Customer</th>
				<th style="width: 10%">Amount</th>
				<th style="width: 10%">Status</th>
				<th style="width: 10%">OR Number</th>
				<th style="width: 10%">Date</th>
				<th style="width: 10%">Added By</th>
				<th style="width: 20%">Remarks</th>
			</tr>
		</thead>
		<tbody>
			@foreach($billings as $key => $result)
			<tr>
    			<td>{{ $result->name }}</td>
    			<td>{{ $result->totalamount }}</td>
    			<td>{{ $result->status }}</td>
    			<td>{{ $result->latest_or_number }}</td>
    			<td>{{ $result->created_at_display }}</td>
    			<td>{{ (count($result->username)) ? $result->username : '' }}</td>
    			<td>{{ $result->details }}</td>
    		</tr>
    		@endforeach
		</tbody>
	</table>
</html>