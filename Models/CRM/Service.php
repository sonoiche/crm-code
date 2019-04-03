<?php

namespace QxCMS\Modules\Client\Models\CRM;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $connection = "client";
    protected $table = "services";
    protected $fillable = ['user_id','name'];
    
    protected $appends = ['hashid'];

    public function getHashidAttribute()
    {
        return hashid($this->attributes['id']);
    }
}
