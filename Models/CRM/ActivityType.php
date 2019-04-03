<?php

namespace QxCMS\Modules\Client\Models\CRM;

use Illuminate\Database\Eloquent\Model;

class ActivityType extends Model
{
	protected $connection = "client";
    protected $table = "activity_types";
    protected $fillable = ['user_id', 'name', 'status', 'invoice', 'product', 'service', 'calendar', 'recurring', 'deletestatus'];

    protected $appends = ['hashid'];

    public function getHashidAttribute()
    {
        return hashid($this->attributes['id']);
    }

    public function activity()
    {
    	return $this->hasMany('QxCMS\Modules\Client\Models\CRM\Activity', 'activity_type');
    }

    public function payment()
    {
    	return $this->hasMany('QxCMS\Modules\Client\Models\CRM\Activity', 'service_id');
    }

    public function proposal()
    {
    	return $this->hasMany('QxCMS\Modules\Client\Models\CRM\Proposal', 'product_id');
    }
}
