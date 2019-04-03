<?php

namespace QxCMS\Modules\Client\Models\CRM;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $connection = "client";
    protected $table = "payments";
    protected $fillable = [
    	'user_id',
        'customer_id',
        'service_id',
        'title',
        'date_added',
        'cert',
        'status',
        'details',
        'attachment',
        'file_permission',
        'deletestatus',
        'fyi'
    ];

    protected $appends = ['hashid','totalamount','latest_date_bill','latest_date_paid','latest_due_date','status_display','file_display','count_file','dlfile','latest_or_number','created_at_display'];

    public function getHashidAttribute()
    {
        return hashid($this->attributes['id']);
    }

    public function customer()
    {
        return $this->belongsTo('QxCMS\Modules\Client\Models\CRM\Customer', 'customer_id');
    }

    public function paymentor()
    {
        return $this->hasMany('QxCMS\Modules\Client\Models\CRM\PaymentOR', 'payment_id');
    }

    public function paymentorfirst()
    {
        return $this->hasOne('QxCMS\Modules\Client\Models\CRM\PaymentOR', 'payment_id');
    }

    public function service()
    {
        return $this->belongsTo('QxCMS\Modules\Client\Models\CRM\Service', 'service_id');
    }

    public function user()
    {
        return $this->belongsTo('QxCMS\Modules\Likod\Models\Clients\User', 'user_id');
    }

    public function getTotalamountAttribute()
    {
        $payment_id = $this->attributes['id'];
        $payment = PaymentOR::selectRaw("sum(amount) as total")->where('payment_id', $payment_id)->get();
        if(count($payment)){
            return number_format($payment[0]->total,2);
        }
        return '0.00';
    }

    public function getLatestDateBillAttribute()
    {
        $payment_id = $this->attributes['id'];
        $payment = PaymentOR::select('date_bill')->where('payment_id', $payment_id)->orderBy('date_paid','desc')->first();
        if(count($payment)){
            return $payment->date_bill;
        }
        return '';
    }

    public function getLatestDatePaidAttribute()
    {
        $payment_id = $this->attributes['id'];
        $payment = PaymentOR::select('date_paid')->where('payment_id', $payment_id)->orderBy('date_paid','desc')->first();
        if(count($payment)){
            return $payment->date_paid;
        }
        return '';
    }

    public function getLatestDueDateAttribute()
    {
        $payment_id = $this->attributes['id'];
        $payment = PaymentOR::select('due_date')->where('payment_id', $payment_id)->orderBy('date_paid','desc')->first();
        if(count($payment)){
            return $payment->due_date;
        }
        return '';
    }

    public function getLatestOrNumberAttribute()
    {
        $payment_id = $this->attributes['id'];
        $payment = PaymentOR::select('or_number')->where('payment_id', $payment_id)->orderBy('date_paid','desc')->first();
        if(count($payment)){
            return $payment->or_number;
        }
        return '';
    }

    public function getStatusDisplayAttribute()
    {
        if($this->attributes['status'] == 'Paid'){
            return '<span class="label label-success">Paid</span>';
        }

        if($this->attributes['status'] == 'Unpaid'){
            return '<span class="label label-danger">Unpaid</span>';
        }

        if($this->attributes['status'] == 'Cancelled'){
            return '<span class="label label-default">Cancelled</span>';
        }
    }

    public function getFileDisplayAttribute()
    {
        if($this->attributes['attachment']){
            return str_limit(basename($this->attributes['attachment']),30);
        }
    }

    public function getCountFileAttribute()
    {
        if($this->attributes['attachment']!=''){
            $files = array_filter(explode('xnx', $this->attributes['attachment']));
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
        $path = 'uploads/customer/payments/';
        if($this->attributes['attachment']!=''){
            $files = array_filter(explode('xnx', $this->attributes['attachment']));
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

    public function getCreatedAtDisplayAttribute()
    {
        if($this->attributes['date_added']!='0000-00-00 00:00:00' || $this->attributes['date_added'] != NULL){
            return Carbon::parse($this->attributes['date_added'])->format('M d, Y');
        }
        return '';

    }
}
