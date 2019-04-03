@extends('Client::layouts')
@section('page-body')
<section class="content-header">
    <h1>
        Customer
        <small>Add New Customer</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Add New Customer</li>
    </ol>
</section>
<section class="content">
	<div class="box box-primary">
		<div class="box-header with-border">
			<div class="pull-right">
				<a href="{{ url('client/crm/customer') }}" class="btn btn-primary btn-sm">Search Customer</a>
			</div>
		</div>
		<div class="box-body">
			<div class="nav-tabs-custom">
			    <ul class="nav nav-tabs">
			        <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="false">Create New Customer</a></li>
			    </ul>
			    <div class="tab-content">
			    	<div class="tab-pane active" id="tab_1">
			    		<span class="text-muted error">All fields with <i class="fa fa-asterisk" style="font-size: 11px"></i> is required.</span>
			    		<br><br>
			    		<form class="form-horizontal" id="customerForm">
			            	<div class="form-group">
			            		<label class="col-sm-4">Company Name <i class="fa fa-asterisk error" style="font-size: 11px"></i></label>
			            		<div class="col-sm-8">
			            			<input type="text" class="form-control" name="name" placeholder="Company Name">
			            			<label id="namedup-error" class="error" for="namedup" style="display: none">Customer name already exists.</label>
			            		</div>
			            	</div>
			            	<div class="form-group">
			            		<label class="col-sm-4">TIN Number</label>
			            		<div class="col-sm-8">
			            			<input type="text" class="form-control" name="tin_number" placeholder="TIN Number" id="tinmask">
			            		</div>
			            	</div>
			            	<div class="form-group">
			            		<label class="col-sm-4">Company Address 1 <i class="fa fa-asterisk error" style="font-size: 11px"></i></label>
			            		<div class="col-sm-8">
			            			<input type="text" class="form-control" name="address" placeholder="Company Address 1">
			            		</div>
			            	</div>
			            	<div class="form-group">
			            		<label class="col-sm-4">Company Address 2</label>
			            		<div class="col-sm-8">
			            			<input type="text" class="form-control" name="address2" placeholder="Company Address 2">
			            		</div>
			            	</div>
			            	<div class="form-group" id="form-contact">
			            		<label class="col-sm-4">Contact Numbers</label>
			            		<div class="col-sm-8">
	            					<div class="dropdown">
	            						<a class="dropdown-toggle" data-toggle="dropdown" href="#" aria-expanded="false">
								          	Add Contact Number <span class="caret"></span>
								        </a>
	            						<ul class="dropdown-menu">
								            <li role="presentation" id="li-mobile"><a role="menuitem" tabindex="-1" href="javascript:void(0)" onclick="contactnumber(1)">Mobile Number</a></li>
								            <li role="presentation" id="li-phone"><a role="menuitem" tabindex="-1" href="javascript:void(0)" onclick="contactnumber(2)">Telephone Number</a></li>
								            <li role="presentation" id="li-fax"><a role="menuitem" tabindex="-1" href="javascript:void(0)" onclick="contactnumber(3)">Fax Number</a></li>
								        </ul>
	            					</div>
			            		</div>
			            	</div>
			            	<div class="form-group" style="display: none" id="form-mobile">
			            		<label class="col-sm-4">Mobile Number</label>
			            		<div class="col-sm-8">
			            			<input type="text" class="form-control" name="mobile_number" placeholder="Mobile Number">
			            		</div>
			            	</div>
			            	<div class="form-group" style="display: none" id="form-telephone">
			            		<label class="col-sm-4">Telephone</label>
			            		<div class="col-sm-8">
			            			<input type="text" class="form-control" name="telephone" placeholder="Telephone">
			            		</div>
			            	</div>
			            	<div class="form-group" style="display: none" id="form-local">
			            		<label class="col-sm-4">Local</label>
			            		<div class="col-sm-8">
			            			<input type="text" class="form-control" name="local" placeholder="Local">
			            		</div>
			            	</div>
			            	<div class="form-group" style="display: none" id="form-fax">
			            		<label class="col-sm-4">Fax Number</label>
			            		<div class="col-sm-8">
			            			<input type="text" class="form-control" name="fax_number" placeholder="Fax Number">
			            		</div>
			            	</div>
			            	<div class="form-group">
			            		<label class="col-sm-4">Email Address</label>
			            		<div class="col-sm-8">
			            			<input type="email" class="form-control" name="email" id="email-tags" placeholder="Email Address">
			            		</div>
			            	</div>
			            	<div class="form-group">
			            		<label class="col-sm-4">Company Website</label>
			            		<div class="col-sm-8">
			            			<input type="text" class="form-control" name="website" placeholder="Company Website">
			            		</div>
			            	</div>
			            	<div class="form-group">
			            		<label class="col-sm-4">Person in Charge</label>
			            		<div class="col-sm-8">
				            		{!! Form::select('person_in_charge', $userList, '', ['class'=>'form-control']) !!}
				            	</div>
			            	</div>
			            	<div class="form-group">
			            		<label class="col-sm-4">Date of First Contact</label>
			            		<div class="col-sm-8">
			            			<div class="input-group">
			            				<input type="text" class="form-control" name="firstcontact" id="firstcontact" placeholder="Date of First Contact">
			            				<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
			            			</div>
			            		</div>
			            	</div>
			            	<div class="form-group">
			            		<label class="col-sm-4">Remarks</label>
			            		<div class="col-sm-8">
			            			<textarea class="form-control" name="remarks" rows="3" style="resize: none"></textarea>
			            		</div>
			            	</div>
			            	<div class="form-group">
			            		<label class="col-sm-4"></label>
				            	<div class="col-sm-8">
				            		<input type="hidden" name="status" value="Active">
				            		<button type="submit" class="btn btn-primary" id="addcustomerbtn">Submit</button>
				            	</div>
				            </div>
			            </form>
			    	</div>
			    </div>
			</div>
		</div>
	</div>
</section>
<div class="modal fade" id="modal-industry">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
					<span class="sr-only">Close</span>
				</button>
				<h4 class="modal-title">Industries</h4>
			</div>
			<div class="modal-body">
				<form id="industryForm">
					{!! csrf_field() !!}
					<input type="hidden" name="industry_id" id="industry_id">
					<div class="form-group">
						<div class="input-group">
							<input type="text" name="name" id="industry_name" class="form-control" placeholder="Industry Name">
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
					<tbody id="tbody-industry">
						@foreach($industries as $key => $industry)
						<tr id="industry-row-{{ hashid($industry->id) }}">
							<td style="text-align: center">{{ $key+1 }}</td>
							<td>{{ $industry->name }}</td>
							<td>
								<a href="javascript:void(0)" onclick="editindustry('{{ hashid($industry->id) }}')" class="btn btn-success btn-xs"><i class="fa fa-pencil"></i></a>
								<a href="javascript:void(0)" onclick="deleteindustry('{{ hashid($industry->id) }}')" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></a>
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
<div class="modal fade" id="modal-product">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
					<span class="sr-only">Close</span>
				</button>
				<h4 class="modal-title">Products</h4>
			</div>
			<div class="modal-body">
				<form id="productForm">
					{!! csrf_field() !!}
					<input type="hidden" name="product_id" id="product_id">
					<div class="form-group">
						<div class="input-group">
							<input type="text" name="name" id="product_name" class="form-control" placeholder="Product Name">
							<div class="input-group-btn">
								<button type="submit" class="btn btn-primary">Save Changes</button>
							</div>
						</div>
						<label id="productname-duplicate" class="error" for="name" style="display: none"></label>
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
					<tbody id="tbody-product">
						@foreach($products as $key => $product)
						<tr id="product-row-{{ hashid($product->id) }}">
							<td style="text-align: center">{{ $key+1 }}</td>
							<td>{{ $product->name }}</td>
							<td>
								@if(!in_array($product->id, $productarray))
								<a href="javascript:void(0)" onclick="editproduct('{{ hashid($product->id) }}')" class="btn btn-success btn-xs"><i class="fa fa-pencil"></i></a>
								<a href="javascript:void(0)" onclick="deleteproduct('{{ hashid($product->id) }}')" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></a>
								@endif
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
<div class="modal fade" id="contact-modal">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
					<span class="sr-only">Close</span>
				</button>
				<h4 class="modal-title" id="contact-title"></h4>
			</div>
			<div class="modal-body">
				<form id="contactnumberForm">
					{!! csrf_field() !!}
					<div class="form-group row">
						<label class="col-sm-3 form-control-label" id="label-contact"></label>
						<div class="col-sm-9">
							<input type="text" class="form-control" name="contact_number" id="contact-number" placeholder="">
						</div>
					</div>
					<div class="form-group row" id="div-local" style="display: none">
						<label class="col-sm-3 form-control-label">Local</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" name="local_number" id="local" placeholder="Local">
						</div>
					</div>
					<div class="form-group row">
						<div class="col-sm-offset-3 col-sm-9">
							<input type="hidden" name="contact_type" id="contact_type">
							<button type="submit" class="btn btn-primary btn-md">Add Contact</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
@endsection

@section('page-css')
{!! Html::style('vendor/tagthis/jquery-tag-this.css') !!}
@endsection

@section('page-js')
{!! Html::script('vendor/tagthis/jquery-tag-this.js') !!}
{!! Html::script('vendor/tagthis/main.js') !!}
{!! Html::script('crmjs/crm-customer.js?ver='.env('FILE_VERSION')) !!}
@endsection