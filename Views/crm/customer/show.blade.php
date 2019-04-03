@extends('Client::layouts')
@section('page-body')
<section class="content-header">
    <h1>
        {{ $customer->name }}
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Customer Information</li>
    </ol>
</section>
<section class="content">
	<div class="box box-primary">
		<div class="box-header with-border">
			<div class="pull-right">
				<a href="{{ url('client/crm/customer', hashid($customer->id)) }}/edit" class="btn btn-primary btn-sm">Edit Customer</a>
			</div>
		</div>
		<div class="box-body">
			<div class="nav-tabs-custom">
			    @include('Client::crm.customer.menu')
			    <div class="tab-content">
			        <div class="tab-pane active" id="tab_1">
			            <div class="box box-primary">
							<div class="box-header with-border">
								<div class="pull-left">
									Customer Information
								</div>
							</div>
							<div class="box-body">
								<div class="row">
									<label class="col-lg-3 col-md-3 col-sm-6"><b>Company Name</b></label>
									<div class="col-lg-9 col-md-9 col-sm-6">{{ $customer->name }}</div>
								</div>
								<div class="row">
									<label class="col-lg-3 col-md-3 col-sm-6"><b>Product</b></label>
									<div class="col-lg-9 col-md-9 col-sm-6">{{ (count($customer->product)) ? $customer->product->name : '' }}</div>
								</div>
								<div class="row">
									<label class="col-lg-3 col-md-3 col-sm-6"><b>Industry</b></label>
									<div class="col-lg-9 col-md-9 col-sm-6">{{ (count($customer->industry)) ? $customer->industry->name : '' }}</div>
								</div>
								<div class="row">
									<label class="col-lg-3 col-md-3 col-sm-6"><b>Company Address</b></label>
									<div class="col-lg-9 col-md-9 col-sm-6">{{ $customer->address }}</div>
								</div>
								@if($customer->address2!='')
								<div class="row">
									<label class="col-lg-3 col-md-3 col-sm-6"><b>Other Address</b></label>
									<div class="col-lg-9 col-md-9 col-sm-6">{{ $customer->address2 }}</div>
								</div>
								@endif
								<div class="row">
									<label class="col-lg-3 col-md-3 col-sm-6"><b>TIN Number</b></label>
									<div class="col-lg-9 col-md-9 col-sm-6">{{ $customer->tin_number }}</div>
								</div>
								<div class="row">
									<label class="col-lg-3 col-md-3 col-sm-6"><b>Mobile Number</b></label>
									<div class="col-lg-9 col-md-9 col-sm-6">{{ $customer->mobile_number }}</div>
								</div>
								<div class="row">
									<label class="col-lg-3 col-md-3 col-sm-6"><b>Telephone Number</b></label>
									<div class="col-lg-9 col-md-9 col-sm-6">{{ $customer->telephone }} {{ ($customer->local) ? 'local '.$customer->local : '' }}</div>
								</div>
								<div class="row">
									<label class="col-lg-3 col-md-3 col-sm-6"><b>Fax Number</b></label>
									<div class="col-lg-9 col-md-9 col-sm-6">{{ $customer->fax_number }}</div>
								</div>
								<div class="row">
									<label class="col-lg-3 col-md-3 col-sm-6"><b>Website</b></label>
									<div class="col-lg-9 col-md-9 col-sm-6"><a href="{{ ($customer->website!='http://') ? $customer->website : '' }}" target="_blank">{{ ($customer->website!='http://') ? $customer->website : '' }}</a></div>
								</div>
								<div class="row">
									<label class="col-lg-3 col-md-3 col-sm-6"><b>Person in Charge</b></label>
									<div class="col-lg-9 col-md-9 col-sm-6">{{ count($customer->person) ? $customer->person->name : '' }}</div>
								</div>
								<div class="row">
									<label class="col-lg-3 col-md-3 col-sm-6"><b>Date of first contact</b></label>
									<div class="col-lg-9 col-md-9 col-sm-6">{{ $customer->firstcontact_display }}</div>
								</div>
								<div class="row">
									<label class="col-lg-3 col-md-3 col-sm-6"><b>Remarks</b></label>
									<div class="col-lg-9 col-md-9 col-sm-6">{{ $customer->remarks }}</div>
								</div>
							</div>
						</div>
						<div class="box box-primary">
							<div class="box-header with-border">
								<div class="pull-left">
									Personal Contacts
								</div>
								<div class="pull-right">
									<a href="{{ url('client/crm/customer',hashid($customer->id)) }}/contact"><i class="fa fa-share fa-fw"></i> List</a>
								</div>
							</div>
							<div class="box-body">
								<table class="table table-striped table-inverse table-hover">
									<thead>
										<tr>
											<th style="width:2%">#</th>
											<th style="width:15%">Name</th>
											<th style="width:15%">Contacts</th>
											<th style="width:10%">Email</th>
											<th style="width:25%">Remarks</th>
										</tr>
									</thead>
									<tbody>
										@if(count($contacts))
											@foreach($contacts as $key => $contact)
											<tr>
												<td>{{ $key+1 }}</td>
												<td>{{ $contact->fullname }}</td>
												<td>
													{!! ($contact->telephone) ? '<i class="fa fa-phone fa-fw"></i> '.$contact->telephone.'<br>' : '' !!}
													{!! ($contact->mobile_number) ? '<i class="fa fa-mobile fa-fw"></i> '.$contact->mobile_number.'<br>' : '' !!}
													{!! ($contact->fax_number) ? '<i class="fa fa-fax fa-fw"></i> '.$contact->fax_number : '' !!}
												</td>
												<td>{{ $contact->email }}</td>
												<td>{{ $contact->remarks }}</td>
											</tr>
											@endforeach
										@else
											<tr>
												<td colspan="5" class="text-center">No records available</td>
											</tr>
										@endif
									</tbody>
								</table>
							</div>
						</div>
						<div class="box box-primary">
							<div class="box-header with-border">
								<div class="pull-left">
									Customer Activities
								</div>
								<div class="pull-right">
									<a href="{{ url('client/crm/customer',hashid($customer->id)) }}/activity"><i class="fa fa-share fa-fw"></i> List</a>
								</div>
							</div>
							<div class="box-body">
								<table class="table table-striped" id="activity-table">
									<thead>
										<tr>
											<th>Activity</th>
											<th>Next Activity</th>
											<th>Remarks</th>
											<th>Due Date</th>
											<th>Date Added</th>
											<th>In-charge</th>
										</tr>
									</thead>
								</table>
							</div>
						</div>
			        </div>
			    </div>
			</div>
		</div>
	</div>
</section>
<input type="hidden" id="customer_id" value="{{ $customer->id }}">
@include('Client::crm.activity.modal-activity')
@include('Client::crm.customer.contact-modal')
@endsection

@section('page-css')
{!! Html::style('vendor/tagthis/jquery-tag-this.css') !!}
@endsection

@section('page-js')
{!! Html::script('vendor/tagthis/jquery-tag-this.js') !!}
{!! Html::script('vendor/tagthis/main.js') !!}
@include('Client::crm.activity.activity-js')
@include('Client::crm.customer.contact-js')
<script type="text/javascript">
$(document).ready(function() {
	var userTable = $('#activity-table').DataTable({
        processing: true,
        serverSide: true,
        "pagingType": "input",
        ajax: {
            url: '{!! url(config('modules.client').'/crm/customer/activity/getactivityoverview') !!}',
            type:'POST',
            data: {
            	customer_id: $('#customer_id').val()
            }
        },
        columns: [
            { data: 'activity_type', name: 'activitytype.name', width:'10%' },
            { data: 'nextactivitytype', name: 'activitytype.name', searchable: false, width:'10%'},
            { data: 'remarks', name: 'remarks', searchable: false, orderable: false, width:'20%'},
            { data: 'due_date_display', name: 'due_date', searchable: false, orderable: false, width:'10%'},
            { data: 'created_at_display', name: 'date_added', searchable: false, orderable: true, width:'10%'},
            { data: 'assign', name: 'assign.name', searchable: false, orderable: false, width:'10%'},
        ],
        "order": [[4, "desc"]],
    });
});
</script>
@endsection