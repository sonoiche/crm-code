<?php

namespace QxCMS\Modules\Client\Models\CRM;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
	protected $connection = 'client';
    protected $table = "products_crm";
    protected $fillable = ['user_id','name'];
    protected $appends = ['hashid'];

    public function getHashidAttribute()
    {
        return hashid($this->attributes['id']);
    }

    public function proposal()
    {
    	return $this->hasMany('QxCMS\Modules\Client\Models\CRM\Proposal', 'product_id');
    }
}
