<?php

namespace QxCMS\Modules\Client\Models\CRM;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PaymentTracker extends Model
{
    protected $connection = "client";
    protected $table = "payment_trackers";
    protected $fillable = [
    	'customer_id',
        'date_type',
        'payment_date',
        'service_id',
        'amount',
        'remarks',
        'or_number',
        'status',
        'group_id',
        'month',
        'year'
    ];

    protected $appends = ['tracker_date','payment_date_form'];

    public function getTrackerDateAttribute()
    {
        if($this->attributes['payment_date']!='0000-00-00'){
            return Carbon::parse($this->attributes['payment_date'])->format('m/d');
        }
        return '';
    }

    public function getPaymentDateFormAttribute()
    {
        if($this->attributes['payment_date']!='0000-00-00'){
            return Carbon::parse($this->attributes['payment_date'])->format('m/d/Y');
        }
        return '';
    }

    public function setPaymentDateAttribute($value)
    {
        if($value){
            return Carbon::parse($value)->format('Y-m-d');
        }
        return '';
    }
}
