<div class="modal fade" id="contact-all-modal" data-backdrop="static" data-keyboard="false">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Add New Contact</h4>
			</div>
			<div class="modal-body">
				<span class="text-muted error">All fields with <i class="fa fa-asterisk" style="font-size: 11px"></i> is required.</span>
	    		<br><br>
				<form id="contactForm">
					{!! csrf_field() !!}
					<div class="form-group row">
						<label class="col-sm-3 form-control-label">First Name <i class="fa fa-asterisk error" style="font-size: 11px"></i></label>
						<div class="col-sm-3">
							{!! Form::select('salutation', $salutationList, '', ['class'=>'form-control']) !!}
						</div>
						<div class="col-sm-6">
							<input type="text" class="form-control" name="fname" placeholder="First Name">
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3 form-control-label">Last Name <i class="fa fa-asterisk error" style="font-size: 11px"></i></label>
						<div class="col-sm-9">
							<input type="text" class="form-control" name="lname" placeholder="Last Name">
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3 form-control-label">Position <i class="fa fa-asterisk error" style="font-size: 11px"></i></label>
						<div class="col-sm-9">
							<input type="text" class="form-control" name="position" placeholder="Position">
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3 form-control-label">Department</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" name="department" placeholder="Department">
						</div>
					</div>
					<div class="form-group row" id="form-contact">
	            		<label class="col-sm-3">Contact Numbers</label>
	            		<div class="col-sm-9">
        					<div class="dropdown">
        						<a class="dropdown-toggle" data-toggle="dropdown" href="#" aria-expanded="false">
						          	Add Contact Number <span class="caret"></span>
						        </a>
        						<ul class="dropdown-menu">
						            <li role="presentation" id="li-mobile-all"><a role="menuitem" tabindex="-1" href="javascript:void(0)" onclick="contactnumberall(1)">Mobile Number</a></li>
						            <li role="presentation" id="li-phone-all"><a role="menuitem" tabindex="-1" href="javascript:void(0)" onclick="contactnumberall(2)">Telephone Number</a></li>
						            <li role="presentation" id="li-fax-all"><a role="menuitem" tabindex="-1" href="javascript:void(0)" onclick="contactnumberall(3)">Fax Number</a></li>
						        </ul>
        					</div>
	            		</div>
	            	</div>
					<div class="form-group row" style="display: none" id="form-mobile-all">
	            		<label class="col-sm-3">Mobile Number</label>
	            		<div class="col-sm-9">
	            			<input type="text" class="form-control" name="mobile_number" placeholder="Mobile Number">
	            		</div>
	            	</div>
	            	<div class="form-group row" style="display: none" id="form-telephone-all">
	            		<label class="col-sm-3">Telephone</label>
	            		<div class="col-sm-9">
	            			<input type="text" class="form-control" name="telephone" placeholder="Telephone">
	            		</div>
	            	</div>
	            	<div class="form-group row" style="display: none" id="form-local-all">
	            		<label class="col-sm-3">Local</label>
	            		<div class="col-sm-9">
	            			<input type="text" class="form-control" name="local" placeholder="Local">
	            		</div>
	            	</div>
	            	<div class="form-group row" style="display: none" id="form-fax-all">
	            		<label class="col-sm-3">Fax Number</label>
	            		<div class="col-sm-9">
	            			<input type="text" class="form-control" name="fax_number" placeholder="Fax Number">
	            		</div>
	            	</div>
					<div class="form-group row">
						<label class="col-sm-3 form-control-label">Email Address</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" name="email" id="email-tags" placeholder="Email Address">
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3 form-control-label">Remarks</label>
						<div class="col-sm-9">
							<textarea class="form-control" name="remarks" rows="3" style="resize: none"></textarea>
						</div>
					</div>
					<div class="form-group row" style="display: none">
						<label class="col-sm-3">Status</label>
						<div class="col-sm-9">
							<div class="radio">
								<label>
									<input type="radio" name="status" id="status1" value="1" checked>
									Active
								</label>
							</div>
							<div class="radio">
								<label>
									<input type="radio" name="status" id="status1" value="0">
									Inactive
								</label>
							</div>
						</div>
					</div>
					<div class="form-group row">
						<div class="col-sm-offset-3 col-sm-9">
							<input type="hidden" name="customer_id" value="{{ hashid($customer->id) }}">
							<input type="hidden" name="contact_id">
							<button type="submit" class="btn btn-primary" id="savecontactbtn">Save Contact</button>
							<button type="button" class="btn btn-secondary" data-dismiss="modal" id="clean-directory">Close</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="phone-modal">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="contact-all-title"></h4>
			</div>
			<div class="modal-body">
				<form id="contactnumberallForm">
					{!! csrf_field() !!}
					<div class="form-group row">
						<label class="col-sm-3 form-control-label" id="label-all-contact"></label>
						<div class="col-sm-9">
							<input type="text" class="form-control" name="contact_number" id="contact-number-all" placeholder="">
						</div>
					</div>
					<div class="form-group row" id="div-all-local" style="display: none">
						<label class="col-sm-3 form-control-label">Local</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" name="local_number" id="local-all" placeholder="Local">
						</div>
					</div>
					<div class="form-group row">
						<div class="col-sm-offset-3 col-sm-9">
							<input type="hidden" name="contact_type" id="contact_type-all">
							<button type="submit" class="btn btn-primary btn-md">Add Contact</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>