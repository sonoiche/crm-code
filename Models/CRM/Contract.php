<?php

namespace QxCMS\Modules\Client\Models\CRM;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $connection = "client";
    protected $table = "contracts";
    protected $fillable = [
        'user_id',
    	'customer_id',
    	'name',
    	'product_id',
    	'amount',
        'contract_date',
    	'complete_date',
    	'remarks',
        'fyi',
        'attach_file',
        'deletestatus',
        'date_added',
        'contract_type'
    ];

    protected $appends = ['hashid','complete_date_form','contract_date_form','count_file','dlfile','amount_display','date_added_display','remarks_format'];

    public function getHashidAttribute()
    {
        return hashid($this->attributes['id']);
    }

    public function product()
    {
    	return $this->belongsTo('QxCMS\Modules\Client\Models\CRM\ActivityType', 'product_id');
    }

    public function user()
    {
    	return $this->belongsTo('QxCMS\Modules\Likod\Models\Clients\User', 'user_id');
    }

    public function customer()
    {
        return $this->belongsTo('QxCMS\Modules\Client\Models\CRM\Customer', 'customer_id');
    }

    public function setCompleteDateAttribute($value)
    {
        if($value){
            return $this->attributes['complete_date'] = Carbon::parse($value)->format('Y-m-d');
        }
        return '';
    }

    public function setContractDateAttribute($value)
    {
        if($value){
            return $this->attributes['contract_date'] = Carbon::parse($value)->format('Y-m-d');
        }
        return '';
    }

    public function getCompleteDateFormAttribute()
    {
        if($this->attributes['complete_date']!='0000-00-00'){
            return Carbon::parse($this->attributes['complete_date'])->format('m/d/Y');
        }
        return '';
    }

    public function getContractDateFormAttribute()
    {
        if($this->attributes['contract_date']!='0000-00-00'){
            return Carbon::parse($this->attributes['contract_date'])->format('m/d/Y');
        }
        return '';
    }

    public function getCompleteDateAttribute()
    {
        if($this->attributes['complete_date']!='0000-00-00'){
            return Carbon::parse($this->attributes['complete_date'])->format('M d, Y');
        }
        return '';
    }

    public function getContractDateAttribute()
    {
        if($this->attributes['contract_date']!='0000-00-00'){
            return Carbon::parse($this->attributes['contract_date'])->format('M d, Y');
        }
        return '';
    }

    public function getCreatedAtAttribute()
    {
        if(isset($this->attributes['date_added']) && $this->attributes['date_added']!='0000-00-00'){
            return Carbon::parse($this->attributes['date_added'])->format('M d, Y');
        }
        return '';
    }

    public function getDateAddedDisplayAttribute()
    {
        if($this->attributes['date_added']!='0000-00-00'){
            return Carbon::parse($this->attributes['date_added'])->format('m/d/Y');
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
        $path = 'uploads/customer/contract/';
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

    public function getAmountDisplayAttribute()
    {
        if($this->attributes['amount']){
            return number_format($this->attributes['amount'],2);
        }
        return '0.00';
    }

    public function getRemarksFormatAttribute()
    {
        return preg_replace( "/\r|\n/", "", $this->attributes['remarks']);
    }
}
