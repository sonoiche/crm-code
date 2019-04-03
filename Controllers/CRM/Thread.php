<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Thread extends Model
{
    protected $table = "threads";
    protected $fillable = ['message_id','user_id','status','message'];
}
