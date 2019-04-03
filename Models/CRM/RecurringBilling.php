<?php

namespace QxCMS\Modules\Client\Models\CRM;

use Illuminate\Database\Eloquent\Model;

class RecurringBilling extends Model
{
    protected $connection = "client";
    protected $table = "recurring_billings";
    protected $fillable = [
    	'user_id',
        'customer_id',
        'service_id',
        'amount',
        'frequency',
        'anniv_month',
        'anniv_day',
        'remarks',
        'deletestatus'
    ];

    protected $appends = ['anniversary','hashid'];

    public function service()
    {
        return $this->belongsTo('QxCMS\Modules\Client\Models\CRM\ActivityType', 'service_id');
    }

    public function user()
    {
        return $this->belongsTo('QxCMS\Modules\Likod\Models\Clients\User', 'user_id');
    }

    public function customer()
    {
        return $this->belongsTo('QxCMS\Modules\Client\Models\CRM\Customer', 'customer_id');
    }

    public function getAnniversaryAttribute()
    {
        if($this->attributes['anniv_month'] && $this->attributes['anniv_day']){
            return $this->attributes['anniv_month'].'/'.$this->attributes['anniv_day'];
        }
        return '';
    }

    public function getHashidAttribute()
    {
        return hashid($this->attributes['id']);
    }
}
