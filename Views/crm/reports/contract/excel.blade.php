<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table class="table table-hover table-striped">
	<thead>
		<tr>
			<th style="width: 10%">Date Added</th>
			<th style="width: 15%">Company Name</th>
			<th style="width: 15%">Contract Name</th>
			<th style="width: 10%">Product Type</th>
			<th style="width: 20%">Remarks</th>
			<th style="width: 10%">Price</th>
			<th style="width: 10%">Contract Date</th>
			<th style="width: 10%">Added By</th>
			<th style="width: 10%">Date Added</th>
		</tr>
	</thead>
	<tbody>
		@foreach($contracts as $key => $contract)
		<tr>
			<td>{{ $contract->date_added_display }}</td>
			<td>{{ (count($contract->customer)) ? $contract->customer->name : '' }}</td>
			<td>{{ $contract->name }}</td>
			<td>{{ $contract->contract_type }}</td>
			<td>{{ $contract->remarks }}</td>
			<td style="text-align: right">{{ $contract->amount_display }}</td>
			<td>{{ $contract->contract_date }}</td>
			<td>{{ (count($contract->user)) ? $contract->user->name : '' }}</td>
			<td>{{ $contract->created_at }}</td>
		</tr>
		@endforeach
	</tbody>
</table>