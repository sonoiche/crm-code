<?php

namespace QxCMS\Modules\Client\Models\CRM;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $connection = 'client';
    protected $table = "customers";
    protected $fillable = [
        'product_id',
    	'industry_id',
        'name',
        'tin_number',
        'address',
        'address2',
        'telephone',
        'local',
        'fax_number',
        'email',
        'website',
        'person_in_charge',
        'status',
        'firstcontact',
        'remarks',
        'user_id',
        'mobile_number',
        'tracker_status',
        'usage',
        'applicants',
        'last_login',
        'usage_link'
    ];

    protected $appends = ['firstcontact_display','firstcontact_form','hashid','last_login_display'];

    public function setFirstcontactAttribute($value)
    {
        if($value){
            return $this->attributes['firstcontact'] = Carbon::parse($value)->format('Y-m-d');
        }
        return '';
    }

    public function getFirstcontactDisplayAttribute()
    {
        if($this->attributes['firstcontact']!='0000-00-00'){
            return Carbon::parse($this->attributes['firstcontact'])->format('M d, Y');
        }
        return '';
    }

    public function getLastLoginDisplayAttribute()
    {
        if(isset($this->attributes['last_login']) && $this->attributes['last_login']!='0000-00-00 00:00:00'){
            return Carbon::parse($this->attributes['last_login'])->format('M d, Y');
        }
        return '';
    }

    public function getFirstcontactFormAttribute()
    {
        if($this->attributes['firstcontact']!='0000-00-00'){
            return Carbon::parse($this->attributes['firstcontact'])->format('m/d/Y');
        }
        return '';
    }

    public function getApplicantsAttribute()
    {
        if($this->attributes['applicants']!=''){
            return number_format($this->attributes['applicants']);
        }
        return '';
    }

    public function getUsageAttribute()
    {
        if($this->attributes['usage']!=''){
            return rtrim($this->attributes['usage'],'.');
        }
        return '';
    }

    public function getHashidAttribute()
    {
        return hashid($this->attributes['id']);
    }

    public function person()
    {
        return $this->belongsTo('QxCMS\Modules\Likod\Models\Clients\User', 'person_in_charge');
    }

    public function industry()
    {
        return $this->belongsTo('QxCMS\Modules\Client\Models\CRM\Industry', 'industry_id');
    }

    public function product()
    {
        return $this->belongsTo('QxCMS\Modules\Client\Models\CRM\Product', 'product_id');
    }

    public function latestactivity()
    {
        return $this->hasMany('QxCMS\Modules\Client\Models\CRM\Activity', 'customer_id');
    }

    public function contact()
    {
        return $this->hasMany('QxCMS\Modules\Client\Models\CRM\CustomerContact', 'customer_id');
    }

    public function payment()
    {
        return $this->hasMany('QxCMS\Modules\Client\Models\CRM\Payment', 'customer_id');
    }

    public function proposal()
    {
        return $this->hasMany('QxCMS\Modules\Client\Models\CRM\Proposal', 'customer_id');
    }

}
