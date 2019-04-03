@extends('Client::layouts')
@section('page-body')
<section class="content-header">
    <h1>
        {{ $customer->name }}
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Update Customer Info</li>
    </ol>
</section>
<section class="content">
	<div class="box box-primary">
		<div class="box-header with-border">
			<div class="pull-left">
				Update Information
			</div>
			<div class="pull-right">
				<a href="{{ url('client/crm/customer', hashid($customer->id)) }}" class="btn btn-primary btn-sm">Customer Overview</a>
			</div>
		</div>
		<div class="box-body">
			<div class="nav-tabs-custom">
			    @include('Client::crm.customer.menu')
			    <div class="tab-content">
			    	<div class="tab-pane active" id="tab_1">
			    		<span class="text-muted error">All fields with <i class="fa fa-asterisk" style="font-size: 11px"></i> is required.</span>
			    		<br><br>
			    		<form class="form-horizontal" id="customerForm">
			            	<div class="form-group">
			            		<label class="col-sm-4">Company Name <i class="fa fa-asterisk error" style="font-size: 11px"></i></label>
			            		<div class="col-sm-8">
			            			<input type="text" class="form-control" name="name" placeholder="Company Name" value="{{ $customer->name }}">
			            			<label id="namedup-error" class="error" for="namedup" style="display: none">Customer name already exists.</label>
			            		</div>
			            	</div>
			            	<div class="form-group">
			            		<label class="col-sm-4">TIN Number</label>
			            		<div class="col-sm-8">
			            			<input type="text" class="form-control" name="tin_number" placeholder="TIN Number" id="tinmask" value="{{ $customer->tin_number }}">
			            		</div>
			            	</div>
			            	<div class="form-group">
			            		<label class="col-sm-4">Company Address 1 <i class="fa fa-asterisk error" style="font-size: 11px"></i></label>
			            		<div class="col-sm-8">
			            			<input type="text" class="form-control" name="address" placeholder="Company Address 1" value="{{ $customer->address }}">
			            		</div>
			            	</div>
			            	<div class="form-group">
			            		<label class="col-sm-4">Company Address 2</label>
			            		<div class="col-sm-8">
			            			<input type="text" class="form-control" name="address2" placeholder="Company Address 2" value="{{ $customer->address2 }}">
			            		</div>
			            	</div>
			            	@if($customer->mobile_number=='' || $customer->telephone=='' || $customer->fax_number=='')
			            	<div class="form-group" id="form-contact">
			            		<label class="col-sm-4">Contact Numbers</label>
			            		<div class="col-sm-8">
	            					<div class="dropdown">
	            						<a class="dropdown-toggle" data-toggle="dropdown" href="#" aria-expanded="false">
								          	Add Contact Number <span class="caret"></span>
								        </a>
	            						<ul class="dropdown-menu">
	            							@if($customer->mobile_number=='')
								            <li role="presentation" id="li-mobile"><a role="menuitem" tabindex="-1" href="javascript:void(0)" onclick="contactnumber(1)">Mobile Number</a></li>
								            @endif
								            @if($customer->telephone=='')
								            <li role="presentation" id="li-phone"><a role="menuitem" tabindex="-1" href="javascript:void(0)" onclick="contactnumber(2)">Telephone Number</a></li>
								            @endif
								            @if($customer->fax_number=='')
								            <li role="presentation" id="li-fax"><a role="menuitem" tabindex="-1" href="javascript:void(0)" onclick="contactnumber(3)">Fax Number</a></li>
								        	@endif
								        </ul>
	            					</div>
			            		</div>
			            	</div>
			            	@endif
			            	<div class="form-group" style="{{ ($customer->mobile_number=='') ? 'display: none' : 'display: block' }}" id="form-mobile">
			            		<label class="col-sm-4">Mobile Number</label>
			            		<div class="col-sm-8">
			            			<input type="text" class="form-control" name="mobile_number" placeholder="Mobile Number" value="{{ $customer->mobile_number }}">
			            		</div>
			            	</div>
			            	<div class="form-group" style="{{ ($customer->telephone=='') ? 'display: none' : 'display: block' }}" id="form-telephone">
			            		<label class="col-sm-4">Telephone</label>
			            		<div class="col-sm-8">
			            			<input type="text" class="form-control" name="telephone" placeholder="Telephone" value="{{ $customer->telephone }}">
			            		</div>
			            	</div>
			            	<div class="form-group" style="{{ ($customer->local=='') ? 'display: none' : 'display: block' }}" id="form-local">
			            		<label class="col-sm-4">Local</label>
			            		<div class="col-sm-8">
			            			<input type="text" class="form-control" name="local" placeholder="Local" value="{{ $customer->local }}">
			            		</div>
			            	</div>
			            	<div class="form-group" style="{{ ($customer->fax_number=='') ? 'display: none' : 'display: block' }}" id="form-fax">
			            		<label class="col-sm-4">Fax Number</label>
			            		<div class="col-sm-8">
			            			<input type="text" class="form-control" name="fax_number" placeholder="Fax Number" value="{{ $customer->fax_number }}">
			            		</div>
			            	</div>
			            	<div class="form-group">
			            		<label class="col-sm-4">Email Address</label>
			            		<div class="col-sm-8">
			            			<input type="email" class="form-control" name="email" id="email-tags" placeholder="Email Address" value="{{ $customer->email }}">
			            		</div>
			            	</div>
			            	<div class="form-group">
			            		<label class="col-sm-4">Company Website</label>
			            		<div class="col-sm-8">
			            			<input type="text" class="form-control" name="website" placeholder="Company Website" value="{{ ($customer->website!='http://') ? $customer->website : '' }}">
			            		</div>
			            	</div>
			            	<div class="form-group">
			            		<label class="col-sm-4">Person in Charge</label>
			            		<div class="col-sm-8">
				            		{!! Form::select('person_in_charge', $userList, $customer->person_in_charge, ['class'=>'form-control']) !!}
				            	</div>
			            	</div>
			            	<div class="form-group">
			            		<label class="col-sm-4">Date of First Contact</label>
			            		<div class="col-sm-8">
			            			<div class="input-group">
			            				<input type="text" class="form-control" name="firstcontact" id="firstcontact" placeholder="Date of First Contact" value="{{ $customer->firstcontact_form }}">
			            				<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
			            			</div>
			            		</div>
			            	</div>
			            	<div class="form-group">
			            		<label class="col-sm-4">Company Status</label>
			            		<div class="col-sm-8">
				            		<select class="form-control" name="status">
				            			<option value="Active" {{ ($customer->status == 'Active') ? 'selected=selected' : '' }}>Active</option>
				            			<option value="Inactive" {{ ($customer->status == 'Inactive') ? 'selected=selected' : '' }}>Inactive</option>
				            		</select>
				            	</div>
			            	</div>
			            	<div class="form-group">
			            		<label class="col-sm-4">Remarks</label>
			            		<div class="col-sm-8">
			            			<textarea class="form-control" name="remarks" rows="3" style="resize: none">{{ $customer->remarks }}</textarea>
			            		</div>
			            	</div>
			            	<div class="form-group">
			            		<label class="col-sm-4"></label>
				            	<div class="col-sm-8">
				            		<input type="hidden" name="id" value="{{ hashid($customer->id) }}">
				            		<button type="submit" class="btn btn-primary" id="addcustomerbtn">Save Changes</button>
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
@include('Client::crm.activity.modal-activity')
@include('Client::crm.customer.contact-modal')
@endsection

@section('page-css')
{!! Html::style('vendor/tagthis/jquery-tag-this.css') !!}
@endsection

@section('page-js')
{!! Html::script('vendor/tagthis/jquery-tag-this.js') !!}
{!! Html::script('vendor/tagthis/main.js') !!}
<script type="text/javascript">
$(document).ready(function() {
	var body = $('body');
	var url = "{{ url('/') }}";
    $("#firstcontact").inputmask(body.data('datepicker-mask'), {"placeholder": body.data('datepicker-mask')});
    $("#firstcontact").datepicker({ 
        yearRange: "-2:+2",
        changeMonth: true,
        changeYear: true,
        dateFormat: body.data('datepicker-format'),
        onSelect: function(datetext){
            $(this).valid();
        }
    });

    $('#tinmask').inputmask({
	  	mask: '999-999-999-999'
	});

	$("[name='name']").blur(function() {
    	// alert('IKON Solutions Inc');
    	$.ajax({
    		url: "{{ url('client/crm/customer/checkname') }}",
    		type: 'POST',
    		dataType: 'json',
    		data: {name: $("[name='name']").val()},
    		success: function(result){
    			if(result == 1){
    				$('#namedup-error').show();
    				$('#addcustomerbtn').attr('type', 'button');
    			} else {
    				$('#namedup-error').hide();
    				$('#addcustomerbtn').attr('type', 'submit');
    			}
    		},
            error: function(XMLHttpRequest, textStatus, errorThrown) {
            	console.log(XMLHttpRequest.status);
		        if (XMLHttpRequest.status == 500) {
		            swal({
                        title: 'Error!',
                        text: 'System experiencing an Internal Server Error.',
                        type: 'error',
                        showConfirmButton: false
                    })
                    location.reload();
		        } else if (XMLHttpRequest.readyState == 0) {
		            swal({
                        title: 'Error!',
                        text: 'Network error (i.e. connection refused, access denied due to CORS, etc.)',
                        type: 'error',
                        showConfirmButton: false
                    })
                    location.reload();
		        } else {
		            swal({
                        title: 'Error!',
                        text: 'Something went wrong, please try again.',
                        type: 'error',
                        showConfirmButton: false
                    })
                    location.reload();
		        }
		    }
    	});
    });

    <?php if(count($emails)){ foreach (array_filter($emails) as $value){ ?>
	$('#email-tags').addTag("{{ str_replace(' ','',$value) }}");
	<?php }} ?>

	$("[name='name']").blur(function() {
    	$.ajax({
    		url: "{{ url('client/crm/customer/checkname') }}",
    		type: 'POST',
    		dataType: 'json',
    		data: {
    			name: $("[name='name']").val(),
    			id: $("[name='id']").val()
    		},
    		success: function(result){
    			if(result == 1){
    				$('#namedup-error').show();
    				$('#addcustomerbtn').attr('type', 'button');
    			} else {
    				$('#namedup-error').hide();
    				$('#addcustomerbtn').attr('type', 'submit');
    			}
    		},
            error: function(XMLHttpRequest, textStatus, errorThrown) {
            	console.log(XMLHttpRequest.status);
		        if (XMLHttpRequest.status == 500) {
		            swal({
                        title: 'Error!',
                        text: 'System experiencing an Internal Server Error.',
                        type: 'error',
                        showConfirmButton: false
                    })
                    location.reload();
		        } else if (XMLHttpRequest.readyState == 0) {
		            swal({
                        title: 'Error!',
                        text: 'Network error (i.e. connection refused, access denied due to CORS, etc.)',
                        type: 'error',
                        showConfirmButton: false
                    })
                    location.reload();
		        } else {
		            swal({
                        title: 'Error!',
                        text: 'Something went wrong, please try again.',
                        type: 'error',
                        showConfirmButton: false
                    })
                    location.reload();
		        }
		    }
    	});
    });

	$.validator.addMethod("phone", function(value, element) {
        return this.optional(element) || value === "NA" ||
            value.match(/^[0-9,\+-\d\s]+$/);
    }, "Please enter a valid number");

    $.validator.addMethod("tin", function() {
        var tin = $('#tinmask').val();
        if (tin.indexOf('_') > -1){
		    return '';
		}
		return 1;
    }, "Please enter a valid TIN Number");

    $('#customerForm').validate({
        rules: {
            name: { required : true },
            tin_number: { 
            	tin: true
            },
            address: { required : true }
            // mobile_number: { phone: true },
            // telephone: { phone: true },
            // fax_number : { phone: true }
        },
        messages : {
            name: "Please provide Customer Name.",
            tin_number: {
            	tin: "Please input a valid TIN Number."
            },
            address: "Please provide Customer address.",
            mobile_number: {
                phone: "Please provide a valid phone number"
            },
            telephone: {
                phone: "Please provide a valid phone number"
            },
            fax_number : { phone: "Please provide a valid phone number" }
        },
    	errorElement: 'label',
	    errorClass: 'error',
	    errorPlacement: function(error, element) {
	        if(element.parent('.input-group').length) {
	            error.insertAfter(element.parent());
	        } else {
	            error.insertAfter(element);
	        }
	    },
        submitHandler: function () {
        	$('#addcustomerbtn').attr('disabled', true);
			$('#addcustomerbtn').html('<i class="fa fa-spinner fa-spin"></i> Saving');
        	var data = $('#customerForm').serializeArray();
			var tags = $('#email-tags').data('tags');
			data.push({name: 'array_email', value: JSON.stringify(tags)});
        	$.ajax({
        		url: "{{ url('client/crm/customer') }}",
        		type: 'POST',
        		dataType: 'json',
        		data: data,
        		success: function(result){
        			swal({
                        title: 'Success!',
                        text: 'Customer has been saved.',
                        type: 'success',
                        showConfirmButton: false
                    })
                    setTimeout(function(){
                    	swal.close();
                    	$('#addcustomerbtn').removeAttr('disabled');
                    	$('#addcustomerbtn').html('Save Changes');
                    }, 1000);
        		},
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                	console.log(XMLHttpRequest.status);
			        if (XMLHttpRequest.status == 500) {
			            swal({
	                        title: 'Error!',
	                        text: 'System experiencing an Internal Server Error.',
	                        type: 'error',
	                        showConfirmButton: false
	                    })
	                    location.reload();
			        } else if (XMLHttpRequest.readyState == 0) {
			            swal({
	                        title: 'Error!',
	                        text: 'Network error (i.e. connection refused, access denied due to CORS, etc.)',
	                        type: 'error',
	                        showConfirmButton: false
	                    })
	                    location.reload();
			        } else {
			            swal({
	                        title: 'Error!',
	                        text: 'Something went wrong, please try again.',
	                        type: 'error',
	                        showConfirmButton: false
	                    })
	                    location.reload();
			        }
			    }
        	});
        }
    })

    $('#industryForm').validate({
    	rules: {
    		name: { required: true }
    	},
    	messages : {
    		name: "Please provide industry name."
    	},
    	errorElement: 'label',
	    errorClass: 'error',
	    errorPlacement: function(error, element) {
	        if(element.parent('.input-group').length) {
	            error.insertAfter(element.parent());
	        } else {
	            error.insertAfter(element);
	        }
	    },
    	submitHandler: function () {
    		var data = $('#industryForm').serializeArray();
    		$.ajax({
    			url: "{{ url('client/crm/customer/storeindustry') }}",
    			type: 'POST',
    			dataType: 'json',
    			data: data,
    			success: function(result){
    				if(result.result == '1'){
    					$('#name-duplicate').show();
    					$('#name-duplicate').html('Industry Name already exist.');
    				} else if(result.result == '2'){
    					swal({
                            title: 'Success!',
                            text: 'Industry has been updated.',
                            type: 'success',
                            showConfirmButton: false
                        })
                        setTimeout(function(){ location.reload(); }, 1000);
    					$('#modal-industry').modal('hide');
    				} else {
    					swal({
                            title: 'Success!',
                            text: 'Industry has been added.',
                            type: 'success',
                            showConfirmButton: false
                        })
    					$('#industry_id_form').append('<option value="'+result.id+'" selected="selected">'+result.name+'</option>');
    					$('#modal-industry').modal('hide');
    					$('#tbody-industry').append(
    						'<tr id="industry-row-'+result.hashid+'">'+
								'<td style="text-align: center">'+result.count+'</td>'+
								'<td>'+result.name+'</td>'+
								'<td>'+
									'<a href="javascript:void(0)" onclick="editindustry(\''+result.hashid+'\')" class="btn btn-success btn-xs"><i class="fa fa-pencil"></i></a>&nbsp;'+
									'<a href="javascript:void(0)" onclick="deleteindustry(\''+result.hashid+'\')" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></a>'+
								'</td>'+
							'</tr>'
    					);
    					setTimeout(function(){ swal.close() }, 1000);
    				}
    			},
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                	console.log(XMLHttpRequest.status);
			        if (XMLHttpRequest.status == 500) {
			            swal({
	                        title: 'Error!',
	                        text: 'System experiencing an Internal Server Error.',
	                        type: 'error',
	                        showConfirmButton: false
	                    })
	                    location.reload();
			        } else if (XMLHttpRequest.readyState == 0) {
			            swal({
	                        title: 'Error!',
	                        text: 'Network error (i.e. connection refused, access denied due to CORS, etc.)',
	                        type: 'error',
	                        showConfirmButton: false
	                    })
	                    location.reload();
			        } else {
			            swal({
	                        title: 'Error!',
	                        text: 'Something went wrong, please try again.',
	                        type: 'error',
	                        showConfirmButton: false
	                    })
	                    location.reload();
			        }
			    }
    		});
    	}
    })

    $('#productForm').validate({
    	rules: {
    		name: { required: true }
    	},
    	messages : {
    		name: "Please provide product name."
    	},
    	errorElement: 'label',
	    errorClass: 'error',
	    errorPlacement: function(error, element) {
	        if(element.parent('.input-group').length) {
	            error.insertAfter(element.parent());
	        } else {
	            error.insertAfter(element);
	        }
	    },
    	submitHandler: function () {
    		var data = $('#productForm').serializeArray();
    		$.ajax({
    			url: "{{ url('client/crm/customer/storeproduct') }}",
    			type: 'POST',
    			dataType: 'json',
    			data: data,
    			success: function(result){
    				if(result.result == '1'){
    					$('#productname-duplicate').show();
    					$('#productname-duplicate').html('Product Name already exist.');
    				} else if(result.result == '2'){
    					swal({
                            title: 'Success!',
                            text: 'Product has been updated.',
                            type: 'success',
                            showConfirmButton: false
                        })
                        setTimeout(function(){ location.reload(); }, 1000);
    					$('#modal-product').modal('hide');
    				} else {
    					$('#product_id_form').append('<option value="'+result.id+'" selected="selected">'+result.name+'</option>');
    					$('#modal-product').modal('hide');
    					$('#tbody-product').append(
    						'<tr id="product-row-'+result.hashid+'">'+
								'<td style="text-align: center">'+result.count+'</td>'+
								'<td>'+result.name+'</td>'+
								'<td>'+
									'<a href="javascript:void(0)" onclick="editproduct(\''+result.hashid+'\')" class="btn btn-success btn-xs"><i class="fa fa-pencil"></i></a>&nbsp;'+
									'<a href="javascript:void(0)" onclick="deleteproduct(\''+result.hashid+'\')" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></a>'+
								'</td>'+
							'</tr>'
    					);
    					setTimeout(function(){ swal.close() }, 1000);
    				}
    			},
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                	console.log(XMLHttpRequest.status);
			        if (XMLHttpRequest.status == 500) {
			            swal({
	                        title: 'Error!',
	                        text: 'System experiencing an Internal Server Error.',
	                        type: 'error',
	                        showConfirmButton: false
	                    })
	                    location.reload();
			        } else if (XMLHttpRequest.readyState == 0) {
			            swal({
	                        title: 'Error!',
	                        text: 'Network error (i.e. connection refused, access denied due to CORS, etc.)',
	                        type: 'error',
	                        showConfirmButton: false
	                    })
	                    location.reload();
			        } else {
			            swal({
	                        title: 'Error!',
	                        text: 'Something went wrong, please try again.',
	                        type: 'error',
	                        showConfirmButton: false
	                    })
	                    location.reload();
			        }
			    }
    		});
    	}
    })

    $('#contactnumberForm').validate({
    	rules: {
    		contact_number: { 
    			required: true
    		}
    	},
    	messages : {
    		contact_number: { 
    			requierd: "Please provide contact number.",
    			phone: "Please provide a valid contact number."
    		}
    	},
    	submitHandler: function () {
    		var type = $('#contact_type').val();
    		var contact = $('#contact-number').val();
    		var local = $('#local').val();
    		if(type == 1){
    			$('#form-mobile').show();
    			$("[name='mobile_number']").val(contact);
    			$('#li-mobile').remove();
    			$('#contact-modal').modal('hide');
    		}

    		if(type == 2){
    			$('#form-telephone').show();
    			$('#form-local').show();
    			$("[name='telephone']").val(contact);
    			$("[name='local']").val(local);
    			$('#li-phone').remove();
    			$('#contact-modal').modal('hide');
    		}

    		if(type == 3){
    			$('#form-fax').show();
    			$("[name='fax_number']").val(contact);
    			$('#li-fax').remove();
    			$('#contact-modal').modal('hide');
    		}

    		if($("[name='mobile_number']").val()!='' && $("[name='telephone']").val()!='' && $("[name='fax_number']").val()!=''){
    			$('#form-contact').remove();
    		}
    	}
    })

    $('#modalindustry').click(function() {
    	$('#industryForm')[0].reset();
    	$('#name-error').hide();
    	$('#modal-industry').modal();
    });

    $('#modalproduct').click(function() {
    	$('#productForm')[0].reset();
    	$('#name-error').hide();
    	$('#modal-product').modal();
    });
});

function contactnumber(type){
	$('#contact_type').val(type);
	$('#contact-number').val('');
	$('#div-local').hide();
	if(type == 1){
		$('#contact-title').html('Add Mobile Number');
		$('#label-contact').html('Mobile Number');
		$('#contact-number').attr('placeholder', 'Mobile Number');
	}

	if(type == 2){
		$('#contact-title').html('Add Telephone Number');
		$('#label-contact').html('Telephone');
		$('#contact-number').attr('placeholder', 'Telephone Number');
		$('#div-local').show();
	}

	if(type == 3){
		$('#contact-title').html('Add Fax Number');
		$('#label-contact').html('Fax Number');
		$('#contact-number').attr('placeholder', 'Fax Number');
	}

	$('#contact-modal').modal();
}

function editindustry(id){
	$.ajax({
		url: "{{ url('client/crm/customer') }}/"+id+"/editindustry",
		type: 'GET',
		dataType: 'json',
		success: function(result){
			$('#name-error').hide();
    		$('#modal-industry').modal();
    		$('#industry_name').val(result.name);
    		$('#industry_id').val(result.hashid);
		}
	});
}

function deleteindustry(id){
	swal({
		title: 'Are you sure?',
		text: "You won't be able to revert this!",
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: 'Yes, delete it!'
	}).then((result) => {
	    $.ajax({
	    	url: "{{ url('client/crm/customer') }}/"+id+"/deleteindustry",
	    	type: 'DELETE',
	    	dataType: 'json',
	    	success: function(response){
			    swal({
                    title: 'Success!',
                    text: 'Industry has been deleted.',
                    type: 'success',
                    showConfirmButton: false
                })
                setTimeout(function(){ 
                	swal.close();
                	$('#modal-industry').modal('hide');
                	$('#industry-row-'+id).remove();
                	$("#industry_id_form option[value='"+response.id+"']").remove();
                }, 1000);
	    	},
            error: function(XMLHttpRequest, textStatus, errorThrown) {
            	console.log(XMLHttpRequest.status);
		        if (XMLHttpRequest.status == 500) {
		            swal({
                        title: 'Error!',
                        text: 'System experiencing an Internal Server Error.',
                        type: 'error',
                        showConfirmButton: false
                    })
                    location.reload();
		        } else if (XMLHttpRequest.readyState == 0) {
		            swal({
                        title: 'Error!',
                        text: 'Network error (i.e. connection refused, access denied due to CORS, etc.)',
                        type: 'error',
                        showConfirmButton: false
                    })
                    location.reload();
		        } else {
		            swal({
                        title: 'Error!',
                        text: 'Something went wrong, please try again.',
                        type: 'error',
                        showConfirmButton: false
                    })
                    location.reload();
		        }
		    }
	    });
	})
}

function editproduct(id){
	$.ajax({
		url: "{{ url('client/crm/customer') }}/"+id+"/editproduct",
		type: 'GET',
		dataType: 'json',
		success: function(result){
			$('#name-error').hide();
    		$('#modal-product').modal();
    		$('#product_name').val(result.name);
    		$('#product_id').val(result.hashid);
		},
        error: function(XMLHttpRequest, textStatus, errorThrown) {
        	console.log(XMLHttpRequest.status);
	        if (XMLHttpRequest.status == 500) {
	            swal({
                    title: 'Error!',
                    text: 'System experiencing an Internal Server Error.',
                    type: 'error',
                    showConfirmButton: false
                })
                location.reload();
	        } else if (XMLHttpRequest.readyState == 0) {
	            swal({
                    title: 'Error!',
                    text: 'Network error (i.e. connection refused, access denied due to CORS, etc.)',
                    type: 'error',
                    showConfirmButton: false
                })
                location.reload();
	        } else {
	            swal({
                    title: 'Error!',
                    text: 'Something went wrong, please try again.',
                    type: 'error',
                    showConfirmButton: false
                })
                location.reload();
	        }
	    }
	});
}

function deleteproduct(id){
	swal({
		title: 'Are you sure?',
		text: "You won't be able to revert this!",
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: 'Yes, delete it!'
	}).then((result) => {
	    $.ajax({
	    	url: "{{ url('client/crm/customer') }}/"+id+"/deleteproduct",
	    	type: 'DELETE',
	    	dataType: 'json',
	    	success: function(response){
			    swal({
                    title: 'Success!',
                    text: 'Product has been deleted.',
                    type: 'success',
                    showConfirmButton: false
                })
                setTimeout(function(){ 
                	swal.close();
                	$('#modal-product').modal('hide');
                	$('#product-row-'+id).remove();
                	$("#product_id_form option[value='"+response.id+"']").remove();
                }, 1000);
	    	},
            error: function(XMLHttpRequest, textStatus, errorThrown) {
            	console.log(XMLHttpRequest.status);
		        if (XMLHttpRequest.status == 500) {
		            swal({
                        title: 'Error!',
                        text: 'System experiencing an Internal Server Error.',
                        type: 'error',
                        showConfirmButton: false
                    })
                    location.reload();
		        } else if (XMLHttpRequest.readyState == 0) {
		            swal({
                        title: 'Error!',
                        text: 'Network error (i.e. connection refused, access denied due to CORS, etc.)',
                        type: 'error',
                        showConfirmButton: false
                    })
                    location.reload();
		        } else {
		            swal({
                        title: 'Error!',
                        text: 'Something went wrong, please try again.',
                        type: 'error',
                        showConfirmButton: false
                    })
                    location.reload();
		        }
		    }
	    });
	})
}
</script>
@include('Client::crm.activity.activity-js')
@include('Client::crm.customer.contact-js')
@endsection