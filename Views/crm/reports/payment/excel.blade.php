<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<table class="table table-hover table-striped">
		<thead>
			<tr>
				<th style="width: 10%">Date Paid</th>
				@if(isset($pr_number) && $pr_number==1)
				<th style="width: 10%">PR Number</th>
				@endif
				@if(isset($or_number) && $or_number==1)
				<th style="width: 10%">OR Number</th>
				@endif
				<th style="width: 15%">Company</th>
				<th style="width: 15%">Company Address</th>
				<th style="width: 10%">TIN Number</th>
				<th style="width: 10%">Amount</th>
				<th style="width: 10%">Service</th>
				<th style="width: 10%">By</th>
				<th style="width: 20%">Remarks</th>
			</tr>
		</thead>
		<tbody>
			@foreach($payments as $key => $result)
			<tr>
    			<td>{{ $result->date_paid }}</td>
    			@if(isset($pr_number) && $pr_number==1)
    			<td align="left">{{ ($result->pr_number!='') ? 'PR-'.$result->pr_number : '' }}</td>
    			@endif
    			@if(isset($or_number) && $or_number==1)
    			<td align="left">{{ $result->or_number }}</td>
    			@endif
    			<td>{{ $result->name }}</td>
    			<td>{{ $result->address }}</td>
    			<td>{{ $result->tin_number }}</td>
    			<td style="text-align: right">{{ $result->amount_display }}</td>
    			<td>{{ $result->activityname }}</td>
    			<td>{{ $result->username }}</td>
    			<td>{{ $result->details }}</td>
    		</tr>
    		@endforeach
    		<tr>
    			<td colspan="4" style="text-align:right"><b>Total Amount</b></td>
    			<td style="text-align:right">{{ number_format($pay->total,2) }}</td>
    			<td colspan="4"></td>
    		</tr>
		</tbody>
	</table>
</html>