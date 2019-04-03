<?php

namespace QxCMS\Modules\Client\Models\CRM;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PaymentOR extends Model
{
    protected $connection = "client";
    protected $table = "payments_or";
    protected $fillable = [
    	'payment_id',
        'due_date',
        'date_paid',
        'date_bill',
        'pr_number',
        'or_number',
        'amount',
        'tracker_id',
        'customer_id',
        'remarks'
    ];

    protected $appends = ['date_bill_form','date_paid_form','due_date_form','amount_display'];

    public function payment()
    {
        return $this->belongsTo('QxCMS\Modules\Client\Models\CRM\Payment', 'payment_id');
    }

    public function setDueDateAttribute($value)
    {
    	if($value){
    		return $this->attributes['due_date'] = Carbon::parse($value)->format('Y-m-d');
    	}
    	return '';
    }

    public function setDatePaidAttribute($value)
    {
    	if($value){
    		return $this->attributes['date_paid'] = Carbon::parse($value)->format('Y-m-d');
    	}
    	return '';
    }

    public function setDateBillAttribute($value)
    {
    	if($value){
    		return $this->attributes['date_bill'] = Carbon::parse($value)->format('Y-m-d');
    	}
    	return '';
    }

    public function getDueDateAttribute()
    {
        if($this->attributes['due_date']!='0000-00-00'){
            return Carbon::parse($this->attributes['due_date'])->format('M d, Y');
        }
        return '';
    }

    public function getDatePaidAttribute()
    {
        if($this->attributes['date_paid']!='0000-00-00'){
            return Carbon::parse($this->attributes['date_paid'])->format('M d, Y');
        }
        return '';
    }

    public function getDateBillAttribute()
    {
        if($this->attributes['date_bill']!='0000-00-00'){
            return Carbon::parse($this->attributes['date_bill'])->format('M d, Y');
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

    public function getDatePaidFormAttribute()
    {
        if($this->attributes['date_paid']!='0000-00-00'){
            return Carbon::parse($this->attributes['date_paid'])->format('m/d/Y');
        }
        return '';
    }

    public function getDateBillFormAttribute()
    {
        if($this->attributes['date_bill']!='0000-00-00'){
            return Carbon::parse($this->attributes['date_bill'])->format('m/d/Y');
        }
        return '';
    }

    public function getAmountDisplayAttribute()
    {
        if($this->attributes['amount']){
            return number_format($this->attributes['amount'],2);
        }
        return '0.00';
    }

    public function getOrNumberAttribute()
    {
        if(is_null($this->attributes['or_number'])){
            return '';
        }
        return $this->attributes['or_number'];
    }
}
