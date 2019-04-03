<?php

namespace QxCMS\Modules\Client\Models\CRM;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Activity extends Model
{
	protected $connection = 'client';
    protected $table = "activities";
    protected $fillable = [
    	'user_id',
        'customer_id',
        'activity_type',
        'next_activity_type',
        'service_id',
        'due_date',
        'assign_to',
        'fyi',
        'remarks',
        'attach_file',
        'file_permission',
        'deletestatus',
        'proposal_id',
        'date_added',
        'done'
    ];

    protected $appends = ['hashid','due_date_display','due_date_form','created_at_display','count_file','dlfile','remarks_display'];

    public function getHashidAttribute()
    {
        return hashid($this->attributes['id']);
    }

    public function setDueDateAttribute($value)
    {
        if($value){
            return $this->attributes['due_date'] = Carbon::parse($value)->format('Y-m-d');
        }
        return '';
    }

    public function getDueDateDisplayAttribute()
    {
        if($this->attributes['due_date']!='0000-00-00'){
            return Carbon::parse($this->attributes['due_date'])->format('M d, Y');
        }
        return '';

    }

    public function getCreatedAtDisplayAttribute()
    {
        if(isset($this->attributes['date_added']) && $this->attributes['date_added']!='0000-00-00'){
            return Carbon::parse($this->attributes['date_added'])->format('M d, Y');
        }
        return '';

    }

    public function getDueDateFormAttribute()
    {
        if($this->attributes['due_date']!='0000-00-00'){
            return Carbon::parse($this->attributes['due_date'])->format('m/d/Y');
        }
        return '';
    }

    public function getCountFileAttribute()
    {
        if($this->attributes['attach_file']!=''){
            $files = array_filter(explode('xnx', $this->attributes['attach_file']));
            $filex = [];
            foreach ($files as $value) {
                $filex[] = $value;
            }

            return count($filex);
        }
        return '0';
    }

    public function getDlfileAttribute()
    {
        $path = 'uploads/customer/activity/';
        if($this->attributes['attach_file']!=''){
            $files = array_filter(explode('xnx', $this->attributes['attach_file']));
            $filex = [];
            foreach ($files as $value) {
                $filex[] = $value;
            }

            if(count($filex) == 1){
                return 'http://quantumx-crm-bucket.s3.amazonaws.com/'.$path.basename($filex[0]);
            }
            return '';
        }
        return '';
    }

    public function getRemarksDisplayAttribute()
    {
        return linkreplace($this->attributes['remarks']);
    }

    public function activitytype()
    {
        return $this->belongsTo('QxCMS\Modules\Client\Models\CRM\ActivityType', 'activity_type');
    }

    public function nextactivitytype()
    {
        return $this->belongsTo('QxCMS\Modules\Client\Models\CRM\ActivityType', 'next_activity_type');
    }

    public function assign()
    {
        return $this->belongsTo('QxCMS\Modules\Likod\Models\Clients\User', 'assign_to');
    }

    public function user()
    {
        return $this->belongsTo('QxCMS\Modules\Likod\Models\Clients\User', 'user_id');
    }

    public function customer()
    {
        return $this->belongsTo('QxCMS\Modules\Client\Models\CRM\Customer', 'customer_id');
    }

    public function proposal()
    {
        return $this->belongsTo('QxCMS\Modules\Client\Models\CRM\Proposal', 'proposal_id');
    }
}
