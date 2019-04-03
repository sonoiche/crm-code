<?php

namespace QxCMS\Modules\Client\Models\CRM;

use Illuminate\Database\Eloquent\Model;

class Salutation extends Model
{
	protected $connection = 'client';
    protected $table = "salutations";
    protected $fillable = ['name'];
}
