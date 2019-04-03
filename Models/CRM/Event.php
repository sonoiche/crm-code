<?php

namespace QxCMS\Modules\Client\Models\CRM;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use QxCMS\Modules\Likod\Models\Clients\User;

class Event extends Model
{
    protected $connection = 'client';
    protected $table = "events";
    protected $fillable = [
    	'user_id',
        'customer_id',
        'activity_id',
        'event_date',
        'attendee',
        'fyi',
        'details'
    ];

    protected $appends = ['attendee_display','event_date_form','event_time_form','event_date_display','hashid','dashboard_event_date'];

    public function user()
    {
        return $this->belongsTo('QxCMS\Modules\Likod\Models\Clients\User', 'user_id');
    }

    public function activity()
    {
        return $this->belongsTo('QxCMS\Modules\Client\Models\CRM\ActivityType', 'activity_id');
    }

    public function customer()
    {
        return $this->belongsTo('QxCMS\Modules\Client\Models\CRM\Customer', 'customer_id');
    }

    public function getHashidAttribute()
    {
        return hashid($this->attributes['id']);
    }

    public function getAttendeeDisplayAttribute()
    {
        if(ContainsNumbers($this->attributes['attendee'])){
            $ids = explode(',', $this->attributes['attendee']);
            $countuser = User::where('status',1)->count();
            if(count($ids) != $countuser){
                $users = User::whereIn('id',$ids)->orderBy('name')->get();
                $attendee = '';
                if(count($users)){
                    foreach ($users as $user) {
                        $attendee .= $user->name.', ';
                    }
                    return substr($attendee, 0, -2);
                }
                return 'All QX Employee';
            }

            if($this->attributes['attendee']){
                return $this->attributes['attendee'];
            }
            return 'All QX Employee';
        } else {
            $usernames = str_replace(",", "','", $this->attributes['attendee']);
            $countuser = User::where('status',1)->count();
            if($countuser){
                $users = User::whereRaw("username in ('".$usernames."')")->orderBy('name')->get();
                $attendee = '';
                if(count($users)){
                    foreach ($users as $user) {
                        $attendee .= $user->name.', ';
                    }
                    return substr($attendee, 0, -2);
                }
                return 'All QX Employee';
            }
        }
    }

    public function getEventDateFormAttribute()
    {
        if($this->attributes['event_date'] != '0000-00-00 00:00:00'){
            return Carbon::parse($this->attributes['event_date'])->format('m/d/Y');
        }
        return '';
    }

    public function getEventTimeFormAttribute()
    {
        if($this->attributes['event_date'] != '0000-00-00 00:00:00'){
            return Carbon::parse($this->attributes['event_date'])->format('g:i A');
        }
        return '';
    }

    public function getEventDateDisplayAttribute()
    {
        if($this->attributes['event_date'] != '0000-00-00 00:00:00'){
            return Carbon::parse($this->attributes['event_date'])->format('M d, Y g:i A');
        }
        return '';
    }

    public function getDashboardEventDateAttribute()
    {
        if($this->attributes['event_date'] != '0000-00-00 00:00:00'){
            return Carbon::parse($this->attributes['event_date'])->format('M d');
        }
        return '';
    }

    public function scopeFilterusercal($query, $param = array())
    {
        $id = $param['username'];
        if($id){
            $query->whereRaw('FIND_IN_SET(\''.$id.'\', attendee)');
        }
        return $query;
    }

    public function scopeFilterevent($query, $param = array())
    {
        $id = $param['username'];
        if($id){
            $query->whereRaw('FIND_IN_SET(\''.$id.'\', attendee)');
        }
        return $query;
    }
}
