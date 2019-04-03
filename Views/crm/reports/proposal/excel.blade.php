<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<table class="table table-hover table-striped">
		<thead>
			<tr>
				<th style="width:10%">Date</th>
				<th style="width:15%">Customer</th>
				<th style="width:10%">Amount</th>
				<th style="width:10%">Status</th>
				<th style="width:20%">Remarks</th>
			</tr>
		</thead>
		<tbody>
			@foreach($proposals as $key => $result)
			<tr>
    			<td>{{ $result->created_at }}</td>
    			<td>{{ $result->name }}</td>
    			<td>{{ $result->amount_display }}</td>
    			<td>{{ $result->status }}</td>
    			<td>{{ $result->remarks }}</td>
    		</tr>
    		@endforeach
    		<tr>
    			<td colspan="2" style="text-align:right"><b>Total Amount</b></td>
    			<td style="text-align:right">{{ number_format($total->total,2) }}</td>
    			<td colspan="3"></td>
    		</tr>
		</tbody>
	</table>
</html>