<ul class="nav nav-tabs">
    <li class="{{ (basename(Request::url()) == hashid($customer->id)) ? 'active' : '' }}"><a href="{{ url('client/crm/customer', hashid($customer->id)) }}">Overview</a></li>
    <li class="dropdown {{ (in_array(basename(Request::url()),['edit','contact'])) ? 'active' : '' }}">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#" aria-expanded="false">
          Customer Info <span class="caret"></span>
        </a>
        <ul class="dropdown-menu">
            <li role="presentation"><a role="menuitem" tabindex="-1" href="{{ url('client/crm/customer',hashid($customer->id)) }}/edit">Update Information</a></li>
            <li role="presentation"><a role="menuitem" tabindex="-1" href="{{ url('client/crm/customer',hashid($customer->id)) }}/contact">List of Contacts</a></li>
            <li role="presentation"><a role="menuitem" tabindex="-1" href="javascript:void(0)" class="addcontactbtn">Add Contact</a></li>
        </ul>
    </li>
    <li class="{{ (basename(Request::url()) == 'activity') ? 'active' : '' }}"><a href="{{ url('client/crm/customer', hashid($customer->id)) }}/activity">Activity</a></li>
    <li class="{{ (basename(Request::url()) == 'payment') ? 'active' : '' }}"><a href="{{ url('client/crm/customer', hashid($customer->id)) }}/payment">Invoice</a></li>
    <li class="{{ (basename(Request::url()) == 'recurring') ? 'active' : '' }}"><a href="{{ url('client/crm/customer', hashid($customer->id)) }}/recurring">Recurring Billing</a></li>
    <li class="{{ (basename(Request::url()) == 'proposal') ? 'active' : '' }}"><a href="{{ url('client/crm/customer', hashid($customer->id)) }}/proposal">Proposal</a></li>
    <li class="{{ (basename(Request::url()) == 'contract') ? 'active' : '' }}"><a href="{{ url('client/crm/customer', hashid($customer->id)) }}/contract">Contract</a></li>
</ul>
@if(basename(Request::url()) != 'activity')
<div class="pull-right" style="margin-top: -35px;"><a href="javascript:void(0)" id="createactivity" class="btn btn-primary btn-sm">Add Activity</a></div>
@endif