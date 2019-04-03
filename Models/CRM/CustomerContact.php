<?php

namespace QxCMS\Modules\Client\Models\CRM;

use Illuminate\Database\Eloquent\Model;

class CustomerContact extends Model
{
    protected $connection = 'client';
    protected $table = "customer_contacts";
    protected $fillable = [
    	'customer_id',
    	'salutation',
    	'fname',
    	'lname',
    	'position',
    	'department',
        'telephone',
    	'local',
    	'mobile_number',
    	'fax_number',
    	'email',
    	'status',
    	'remarks'
    ];

    protected $appends = ['fullname','telephone_local'];

    public function getFullnameAttribute()
    {
        $salutation = ($this->attributes['salutation']) ? $this->attributes['salutation'].' ' : '';
        return $salutation.ucfirst($this->attributes['fname']).' '.ucfirst($this->attributes['lname']);
    }

    public function getTelephoneLocalAttribute()
    {
        if($this->attributes['telephone'] && $this->attributes['local']){
            return $this->attributes['telephone'].' local '.$this->attributes['local'];
        }
        return $this->attributes['telephone'];
    }
}
