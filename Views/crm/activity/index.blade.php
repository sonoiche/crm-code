@extends('Client::layouts')
@section('page-body')
<section class="content-header">
    <h1>
        {{ $customer->name }}
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li>Customer</li>
        <li class="active">Activity</li>
    </ol>
</section>
<section class="content">
	<div class="col-md-12">
		<div class="box box-primary">
			<div class="box-header with-border">
				<div class="pull-left">
					Customer Activities
				</div>
				<div class="pull-right">
					<a href="javascript:void(0)" class="btn btn-primary btn-sm createactivity">Create New Activity</a>
				</div>
			</div>
			<div class="box-body">
				<div class="nav-tabs-custom">
					@include('Client::crm.customer.menu')
					<div class="tab-content">
				        <div class="tab-pane active" id="tab_1">
				            <div class="box box-primary">
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
												<th></th>
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
	</div>
</section>
<div style="clear: both;"></div>
<input type="hidden" id="customer_id" value="{{ $customer->id }}">
@include('Client::crm.activity.modal-activity')
@include('Client::crm.customer.contact-modal')
@endsection

@section('page-css')
{!! Html::style('vendor/tagthis/jquery-tag-this.css') !!}
<style type="text/css">
.row-deleted {
	background: #f2dede !important;
}
</style>
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
            url: '{!! url(config('modules.client').'/crm/customer/activity/getactivity') !!}',
            type:'POST',
            data: {
            	customer_id: $('#customer_id').val()
            }
        },
        columns: [
            { data: 'activity_type', name: 'activitytype.name', width:'10%' },
            { data: 'nextactivitytype', name: 'activitytype.name', searchable: false, width:'10%'},
            { data: 'remarks', name: 'remarks', searchable: true, orderable: false, width:'30%'},
            { data: 'due_date_display', name: 'due_date', searchable: false, orderable: false, width:'10%'},
            { data: 'created_at_display', name: 'date_added', searchable: false, orderable: true, width:'10%'},
            { data: 'name', name: 'client_users.name', searchable: true, orderable: false, width:'10%'},
            { data: 'action', name: 'action', orderable: false, searchable: false, width:'10%'},
        ],
        "order": [[4, "desc"]],
    });
});

function editact(id){
    $('label.error').hide();
    $('#div-assignto').html('');
    $('#div-fyi').html('');
    $('#div-activity-files').empty();
    var url = "{{ url('/') }}";
    var urlx = "{{ \Storage::url('uploads') }}";
    var user_id = "{{ Auth::guard('client')->user()->id }}";
    var access_id = "{{ Auth::guard('client')->user()->role_id }}";
    $.ajax({
        url: "{{ url('client/crm/customer/') }}/"+id+"/editact",
        type: 'GET',
        dataType: 'json',
        success: function(result){
            $('#modal-activity').modal();
            $("[name='activity_type']").val(result.activity.activity_type);
            $("[name='next_activity_type']").val(result.activity.next_activity_type);
            $("[name='service_id']").val(result.activity.service_id);
            $("[name='due_date']").val(result.activity.due_date_form);
            $("[name='remarks']").val(result.activity.remarks);
            $("[name='activity_id']").val(result.activity.hashid);
            $("[name='file_permission']").val(result.activity.file_permission);
            
            if(result.activity.attach_file){
                $('#div-files').show();
                var datas = result.files;
                var a = 1;
                for(i=0; i<result.arrcount; ++i){
                    $('#div-activity-files').append(
                    '<div class="btn-group" role="group" aria-label="...">'+
                        '<a href="http://quantumx-crm-bucket.s3.amazonaws.com/uploads/customer/activity/'+datas[i]+'" class="btn btn-primary btn-xs file-'+i+'" target="_blank"><i class="fa fa-download fa-fw"></i> Attachment '+a+'</a>&nbsp;'+
                        '<a href="javascript:void(0)" onclick="activityremoveattachment('+i+','+result.activity.id+',\''+datas[i]+'\')" class="btn btn-danger btn-xs file-'+i+'"><i class="fa fa-remove"></i></a>'+
                    '</div>'
                    );
                    a++;
                }
            }

            $('#div-assignto').html('<input type="text" class="form-control" name="assign_to" id="assignto" placeholder="Assign To">');
            $('#div-fyi').html('<input type="text" class="form-control" name="fyi" id="fyi" placeholder="FYI">');

            if(result.user!=null){
                $("#assignto").tokenInput(url+ "/client/crm/customer/userlist", {
                    tokenLimit: 1,
                        prePopulate: [
                            {id: result.user.id, name: result.user.name}
                        ]
                });
            } else {
                $("#assignto").tokenInput(url+ "/client/crm/customer/userlist", {
                    tokenLimit: 1
                });
            }

            $("#fyi").tokenInput(url+ "/client/crm/customer/userlist", {
                theme: "facebook",
                preventDuplicates: true,
                prePopulate: result.users
            });

        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            if (XMLHttpRequest.status == 500) {
                swal({
                    title: 'Server Error!',
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
</script>
@endsection