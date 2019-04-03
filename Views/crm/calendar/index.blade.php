@extends('Client::layouts')
@section('page-body')
<section class="content-header">
	<h1>&nbsp;</h1>
	<ol class="breadcrumb">
		<li><a href="#"><i class="fa fa-gear"></i> Calendar</a></li>
		<li class="active"><a href="#">Event Calendar</a></li>
	</ol>
</section>
<section class="content">
	<div class="col-md-12">
		<div class="box box-primary">
			<div class="box-header with-border">
				<h4>Calendar Event</h4>
				{!! Form::open(['url'=>'client/crm/calendar/changeDate','id'=>'calendarForm']) !!}
                <div class="input-group pull-left" style="width:75%">
					<span class="input-group-addon" style="background:#d9edf7">Month</span>	
					{!! Form::selectMonth('month', isset($cmonth) ? $cmonth : date('m'), ['class'=>'form-control','id'=>'cmonth']) !!}
			        <span class="input-group-addon" style="background:#d9edf7">Year</span>
			        {!! Form::selectYear('year', date('Y', strtotime('- 3 year')), date('Y', strtotime('+ 1 year')), isset($cyear) ? $cyear : date('Y'), ['class'=>'form-control','id'=>'cyear']) !!}
					<span class="input-group-addon" style="background:#d9edf7">Username</span>
					{!! Form::select('username', $userList, isset($username) ? $username : '', ['class'=>'form-control','id'=>'username']) !!}
					<span class="input-group-addon" style="background:#d9edf7">View Type</span>
					{!! Form::select('viewtype', array(1=>'Calendar',2=>'List'),isset($viewtype) ? $viewtype : '',['class'=>'form-control','id'=>'viewtype']) !!}
				</div>
				{!! Form::close() !!}
				<div class="pull-right">
					<a style="cursor:pointer" class="btn btn-primary btn-flat btn-sm" id="addeventbtn">Add Event</a>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-12">
			<div style="width: 97.5%;margin: 0 auto;">
				{!! $calendar !!}
			</div>
		</div>
	</div>
</section>
@include('Client::crm.calendar.modal')
@stop

@section('page-css')
{!! Html::style('template/AdminLTE/plugins/timepicker/bootstrap-timepicker.min.css') !!}
<style type="text/css">
.bootstrap-timepicker-widget {
	z-index: 9999;
}
</style>
@stop

@section('page-js')
{!! Html::script('vendor/tokeninput/jquery.tokeninput.js') !!}
{!! Html::script('template/AdminLTE/plugins/timepicker/bootstrap-timepicker.min.js') !!}
{!! Html::script('crmjs/crm-calendar.js?ver='.env('FILE_VERSION')) !!}
@stop