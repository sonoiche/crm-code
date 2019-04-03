<div class="modal fade" id="modal-payment" data-backdrop="static" data-keyboard="false">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Create New Invoice</h4>
			</div>
			{!! Form::open(['id' => 'paymentForm', 'files' => true]) !!}
			<div class="modal-body">			    
		        <div class="panel panel-primary">
		            <div class="panel-body">
		                <div class="form-group">
		                    <label class="control-label">Name</label>
		                    <input maxlength="100" name="title" type="text" required="required" class="form-control" placeholder="Name" />
		                </div>
		                <div class="form-group">
		                    <label class="control-label">Service</label>
		                    {!! Form::select('service_id', $serviceList, '', ['class' => 'form-control', 'required' => 'required']) !!}
		                </div>
		                <div class="form-group">
		                    <label class="control-label">Status</label>
		                    {!! Form::select('status', $statusList, '', ['class' => 'form-control', 'required' => 'required', 'id' => 'status']) !!}
		                </div>
		                <div class="form-group">
							<div class="checkbox">
								<label>
									<input type="checkbox" name="cert"> Pending 2307
								</label>
							</div>
		                </div>
		                <div class="form-group">
		                    <label class="control-label">Date Billed</label>
		                    <div class="input-group">
	            				<input type="text" class="form-control token-date" name="date_bill" id="date_bill" value="{{ date('m/d/Y') }}" placeholder="Date Billed">
	            				<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
	            			</div>
		                </div>
		                <div class="form-group">
		                    <label class="control-label">Due Date</label>
		                    <div class="input-group">
	            				<input type="text" class="form-control token-date" name="due_date" id="due_date" placeholder="Due Date">
	            				<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
	            			</div>
		                </div>
		                <div class="form-group">
		                    <label class="control-label">Date Paid</label>
		                    <div class="input-group">
	            				<input type="text" class="form-control token-date" name="date_paid" id="date_paid" placeholder="Date Paid">
	            				<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
	            			</div>
		                </div>
		                <div class="form-group">
		                    <label class="control-label">Amount</label>
		                    <input maxlength="100" name="amount" type="text" required="required" class="form-control" placeholder="Amount" />
		                </div>
		                <div class="form-group">
		                    <label class="control-label">Receipt Type</label>
		                    <select class="form-control" name="receipt_type" id="receipt_type">
		                    	<option value="">--</option>
		                    	<option value="1">Provisional Reciept</option>
		                    	<option value="2">Official Receipt</option>
		                    </select>
		                </div>
		                <div class="form-group" id="div-prnumber" style="display: none">
		                    <label class="control-label">PR Number</label>
		                    <input maxlength="100" name="pr_number" id="pr_number" type="text" class="form-control" placeholder="PR Number" />
		                </div>
		                <div class="form-group" id="div-ornumber" style="display: none">
		                    <label class="control-label">OR Number</label>
		                    <input maxlength="100" name="or_number" id="or_number" type="text" class="form-control" placeholder="OR Number" />
		                </div>
		                <div class="form-group">
							<label class="form-control-label">FYI</label>
							<input type="text" class="form-control" name="fyi" id="fyi-payment" placeholder="FYI">
						</div>
		                <div class="form-group">
		                    <label class="control-label">Remarks</label>
		                    <textarea class="form-control" name="details" style="height: 100px; resize: none"></textarea>
		                </div>
		                <div class="form-group">
		                    <label class="control-label">File Upload</label>
							<div id="create-uploaded">
								<p>Your browser doesn\'t have Flash, Silverlight or HTML5 support.</p>
							</div>
		                </div>
		                <div class="form-group">
		                    <label class="control-label">File View</label>
		                    {!! Form::select('file_permission', ['Everyone' => 'Everyone', 'Only Me' => 'Only Me'], '', ['class' => 'form-control', 'required' => 'required']) !!}
		                </div>
		            </div>
		        </div>
		        <input type="hidden" name="customer_id" value="{{ hashid($customer->id) }}">
		        <input type="hidden" name="id">
			</div>
			<div class="modal-footer">
				<button class="btn btn-primary pluploadpayment-btn" type="submit" id="savepaymentbtn">Save Payment</button>
				<button type="button" class="btn btn-secondary" data-dismiss="modal" id="clean-directory">Close</button>
			</div>
			</form>
		</div>
	</div>
</div>
<div class="modal fade" id="modal-editpayment" data-backdrop="static" data-keyboard="false">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Update Payment Details</h4>
			</div>
			{!! Form::open(['id' => 'updatepaymentForm', 'files' => true]) !!}
				<div class="modal-body">
					<div class="form-group">
	                    <label class="control-label">Name</label>
	                    <input maxlength="100" name="title" type="text" required="required" class="form-control" placeholder="Name" />
	                </div>
	                <div class="form-group">
	                    <label class="control-label">Service</label>
	                    {!! Form::select('service_id', $serviceList, '', ['class' => 'form-control', 'required' => 'required']) !!}
	                </div>
	                <div class="form-group">
	                    <label class="control-label">Status</label>
	                    {!! Form::select('status', $statusList, '', ['class' => 'form-control', 'required' => 'required', 'id' => 'upd-status']) !!}
	                </div>
	                <div class="form-group">
	                    <label class="control-label">Due Date</label>
	                    <div class="input-group">
            				<input type="text" class="form-control token-date" name="due_date" placeholder="Due Date">
            				<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
            			</div>
	                </div>
	                <div class="form-group">
	                    <label class="control-label">Date Paid</label>
	                    <div class="input-group">
            				<input type="text" class="form-control token-date" name="date_paid" placeholder="Date Paid">
            				<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
            			</div>
	                </div>
	                <div class="form-group">
	                    <label class="control-label">Amount</label>
	                    <input maxlength="100" name="amount" type="text" required="required" class="form-control" placeholder="Amount" />
	                </div>
	                <div class="form-group">
	                    <label class="control-label">Receipt Type</label>
	                    <select class="form-control" name="receipt_type" id="receipt_type2">
	                    	<option value="">--</option>
	                    	<option value="1">Provisional Reciept</option>
	                    	<option value="2">Official Receipt</option>
	                    </select>
	                </div>
	                <div class="form-group" id="div-prnumber2" style="display: none">
	                    <label class="control-label">PR Number</label>
	                    <input maxlength="100" name="pr_number" id="pr_number" type="text" class="form-control" placeholder="PR Number" />
	                </div>
	                <div class="form-group" id="div-ornumber2" style="display: none">
	                    <label class="control-label">OR Number</label>
	                    <input maxlength="100" name="or_number" id="or_number" type="text" class="form-control" placeholder="OR Number" />
	                </div>
	                <div class="form-group">
						<div class="checkbox">
							<label>
								<input type="checkbox" name="cert"> Pending 2307
							</label>
						</div>
	                </div>
	                <div class="form-group">
						<label class="form-control-label">FYI</label>
						<div id="div-fyi-invoice">
							<input type="text" class="form-control" name="fyi" id="fyi-payment2" placeholder="FYI">
						</div>
					</div>
	                <div class="form-group">
	                    <label class="control-label">Remarks</label>
	                    <textarea class="form-control" name="details" style="height: 100px; resize: none"></textarea>
	                </div>
	                <div class="form-group">
						<label class="control-label"></label>
						<div id="div-files" style="display: hidden">
							
						</div>
					</div>
					<div class="form-group">
						<label class="control-label">File Upload</label>
						<div id="uploaded">
							<p>Your browser doesn\'t have Flash, Silverlight or HTML5 support.</p>
						</div>
					</div>
	                <div class="form-group">
	                    <label class="control-label">File View</label>
	                    {!! Form::select('file_permission', ['Everyone' => 'Everyone', 'Only Me' => 'Only Me'], '', ['class' => 'form-control', 'required' => 'required']) !!}
	                </div>
				</div>
				<div class="modal-footer">
					<input type="hidden" name="customer_id">
					<input type="hidden" name="id" id="payment_id">
					<button type="button" class="btn btn-secondary" data-dismiss="modal" id="clean-directory">Close</button>
					<button type="submit" class="btn btn-primary plupload-btn" id="updatepaymentbtn">Save changes</button>
				</div>
			</form>
		</div>
	</div>
</div>
<div class="modal fade" id="modal-invoice">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
					<span class="sr-only">Close</span>
				</button>
				<h4 class="modal-title">Display OR Numbers</h4>
			</div>
			<div class="modal-body">
				<table class="table table-hover table-striped">
					<thead>
						<tr>
							<th style="width: 20%">PR Number</th>
							<th style="width: 20%">OR Number</th>
							<th style="width: 20%">Amount</th>
							<th style="width: 20%">Date Paid</th>
							<th style="width: 10%"></th>
						</tr>
					</thead>
					<tbody id="or-table">
						
					</tbody>
				</table>
				{!! Form::open(['id' => 'ornumberForm']) !!}
				<table class="table">
					<tbody>
						<tr>
							<td style="width: 22%"><small>PR Number</small><input type="text" name="pr_number" id="edit-pr-number" class="form-control input-md" placeholder="PR Number"></td>
							<td style="width: 22%"><small>OR Number</small><input type="text" name="or_number" id="edit-or-number" class="form-control input-md" placeholder="OR Number"></td>
							<td style="width: 22%"><small>Amount</small><input type="text" name="amount" id="edit-amount" class="form-control input-md" placeholder="Amount"></td>
							<td style="width: 22%"><small>Date Paid</small><input type="text" name="date_paid" id="edit-date-paid" class="form-control input-md token-date" placeholder="Date Paid"></td>
							<td style="width: 22%"><small>&nbsp;</small><button class="btn btn-md btn-primary btn-block" id="saveornumberbtn" style="width: 65px;">Save</button></td>
						</tr>
					</tbody>
				</table>
				<input type="hidden" name="id" id="edit-id">
				<input type="hidden" name="payment_id" id="edit-payment-id">
				{!! Form::close() !!}
				<div class="alert alert-danger" style="display: none;"></div>
			</div>
			<div class="modal-footer" id="payment-remarks" style="text-align: left"></div>
		</div>
	</div>
</div>
<div class="modal fade" id="modal-tracker">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">Add Payment Tracker</h4>
			</div>
			{!! Form::open(['id'=>'trackerForm']) !!}
			<div class="modal-body">
				<div class="form-group row">
					<label class="col-sm-3" style="margin-top: 6px;">Date Type</label>
					<div class="col-sm-9">
						<label class="radio-inline">
							<input type="radio" name="date_type" id="samedate" value="1" checked> Same Date
						</label>
						<label class="radio-inline">
							<input type="radio" name="date_type" id="monthend" value="2"> Base on Month End
						</label>
					</div>
				</div>
				<div class="form-group row">
					<label class="col-sm-3" style="margin-top: 6px;">From <i class="fa fa-asterisk error" style="font-size: 11px"></i></label>
					<div class="col-sm-9">
						<div class="input-group">
	        				<input type="text" class="form-control" name="from_date" id="from_date" placeholder="From">
	        				<input type="text" class="form-control" name="" id="from_date2" placeholder="From" style="display: none">
	        				<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
	        			</div>
	        		</div>
				</div>
				<div class="form-group row">
					<label class="col-sm-3" style="margin-top: 6px;">To <i class="fa fa-asterisk error" style="font-size: 11px"></i></label>
					<div class="col-sm-9">
						<div class="input-group">
	        				<input type="text" class="form-control" name="to_date" id="to_date" placeholder="To">
	        				<input type="text" class="form-control" name="" id="to_date2" placeholder="To" style="display: none">
	        				<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
	        			</div>
	        		</div>
				</div>
				<div class="form-group row">
					<label class="col-sm-3" style="margin-top: 6px;">Service <i class="fa fa-asterisk error" style="font-size: 11px"></i></label>
					<div class="col-sm-9">
						<div class="input-group">
		            		{!! Form::select('service_id', $serviceList, '', ['class'=>'form-control','id'=>'service_id_form2']) !!}
		            		<div class="input-group-btn">
		            			<a href="javascript:void(0)" class="btn btn-primary btn-md" id="modalaservice">Add Service</a>
		            		</div>
	            		</div>
	            	</div>
				</div>
				<div class="form-group row">
					<label class="col-sm-3" style="margin-top: 6px;">Amount <i class="fa fa-asterisk error" style="font-size: 11px"></i></label>
					<div class="col-sm-9">
						<input type="text" name="amount" class="form-control">
					</div>
				</div>
				<div class="form-group row">
					<label class="col-sm-3" style="margin-top: 6px;">Remarks</label>
					<div class="col-sm-9">
						<textarea class="form-control" name="remarks" style="height: 120px; resize: none"></textarea>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<input type="hidden" name="customer_id" id="tracker_customer_id">
				<input type="hidden" name="year" id="tracker_year">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary" id="savetrackerbtn">Save</button>
			</div>
			{!! Form::close() !!}
		</div>
	</div>
</div>
<div class="modal fade" id="multipledoc-modal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">Payment Multiple Documents</h4>
			</div>
			<div class="modal-body">
				<div id="div-paymentfiles"></div>
			</div>
		</div>
	</div>
</div>