@extends('Client::layouts')
@section('page-body')
<section class="content-header">
    <h1>
        {{ $customer->name }}
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li>Customer</li>
        <li class="active">Contacts</li>
    </ol>
</section>
<section class="content">
	<div class="box box-primary">
		<div class="box-header with-border">
			<div class="pull-left">
				Personal Contacts
			</div>
			<div class="pull-right">
				<a href="javascript:void(0)" class="btn btn-primary btn-sm addcontactbtn">Add New Contact</a>
			</div>
		</div>
		<div class="box-body">
			<div class="nav-tabs-custom">
			    @include('Client::crm.customer.menu')
			    <div class="tab-content">
			        <div class="tab-pane active" id="tab_1">
			            <div class="box box-primary">
							<div class="box-body">
								<table class="table table-striped table-inverse table-hover">
									<thead>
										<tr>
											<th style="width:2%">#</th>
											<th style="width:15%">Name</th>
											<th style="width:15%">Contacts</th>
											<th style="width:10%">Email</th>
											<th style="width:25%">Remarks</th>
											<th style="width:10%"></th>
										</tr>
									</thead>
									<tbody id="contact-tbody">
										@if(count($contacts))
											@foreach($contacts as $key => $contact)
											<tr id="contact-row-{{ hashid($contact->id) }}">
												<td>{{ $key+1 }}</td>
												<td>{{ $contact->fullname }}</td>
												<td>
													{!! ($contact->telephone) ? '<i class="fa fa-phone fa-fw"></i> '.$contact->telephone_local.'<br>' : '' !!}
													{!! ($contact->mobile_number) ? '<i class="fa fa-mobile fa-fw"></i> '.$contact->mobile_number.'<br>' : '' !!}
													{!! ($contact->fax_number) ? '<i class="fa fa-fax fa-fw"></i> '.$contact->fax_number : '' !!}
												</td>
												<td>{{ $contact->email }}</td>
												<td>{{ $contact->remarks }}</td>
												<td>
													<div class="btn-group">
														<button type="button" class="btn btn-primary btn-xs dropdown-toggle" data-toggle="dropdown">
															Action &nbsp;&nbsp;
															<span class="caret"></span>
														</button>
													    <ul class="dropdown-menu" id="menu3" aria-labelledby="drop6" style="min-width: 125px !important">
													        <li><a href="javascript:void(0)" onclick="editcontact('{{ hashid($contact->id) }}')"><i class="fa fa-pencil fa-fw"></i> Edit</a></li>
													        <li><a href="javascript:void(0)" onclick="deletecontact('{{ hashid($contact->id) }}')"><i class="fa fa-times fa-fw"></i> Resign</a></li>
													    </ul>
													</div>
												</td>
											</tr>
											@endforeach
										@else
											<tr>
												<td colspan="6" class="text-center">No record available</td>
											</tr>
										@endif
									</tbody>
								</table>
							</div>
						</div>
						<div class="box box-primary">
							<div class="box-body">
								<div class="pull-left">
									Resigned Contacts
								</div>
								<table class="table table-striped table-inverse table-hover">
									<thead>
										<tr>
											<th style="width:2%">#</th>
											<th style="width:15%">Name</th>
											<th style="width:15%">Contacts</th>
											<th style="width:10%">Email</th>
											<th style="width:25%">Remarks</th>
											<th style="width:10%"></th>
										</tr>
									</thead>
									<tbody id="contact-tbody">
										@if(count($resigns))
											@foreach($resigns as $key => $contact)
											<tr id="active-row-{{ hashid($contact->id) }}">
												<td>{{ $key+1 }}</td>
												<td>{{ $contact->fullname }}</td>
												<td>
													{!! ($contact->telephone) ? '<i class="fa fa-phone fa-fw"></i> '.$contact->telephone.'<br>' : '' !!}
													{!! ($contact->mobile_number) ? '<i class="fa fa-mobile fa-fw"></i> '.$contact->mobile_number.'<br>' : '' !!}
													{!! ($contact->fax_number) ? '<i class="fa fa-fax fa-fw"></i> '.$contact->fax_number : '' !!}
												</td>
												<td>{{ $contact->email }}</td>
												<td>{{ $contact->remarks }}</td>
												<td>
													<ul class="nav" role="tablist">
														<li role="presentation" class="dropdown"> <a href="#" class="dropdown-toggle" id="drop6" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="true"> Action <span class="caret"></span> </a>
														    <ul class="dropdown-menu" id="menu3" aria-labelledby="drop6" style="min-width: 125px !important;">
														        <li><a href="javascript:void(0)" onclick="editcontact('{{ hashid($contact->id) }}')"><i class="fa fa-pencil fa-fw"></i> Edit</a></li>
														        <li><a href="javascript:void(0)" onclick="activecontact('{{ hashid($contact->id) }}')"><i class="fa fa-check fa-fw"></i> Active</a></li>
														    </ul>
														</li>
													</ul>
												</td>
											</tr>
											@endforeach
										@else
											<tr>
												<td colspan="6" class="text-center">No record available</td>
											</tr>
										@endif
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
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
@endsection