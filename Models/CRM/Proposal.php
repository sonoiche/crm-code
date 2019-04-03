<?php

namespace QxCMS\Modules\Client\Models\CRM;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    protected $connection = "client";
    protected $table = "proposals";
    protected $fillable = [
        'user_id',
    	'customer_id',
        'name',
        'product_id',
        'amount',
        'fyi',
        'status',
        'chance',
        'remarks',
        'file',
        'deletestatus',
        'date_submitted'
    ];

    protected $appends = ['count_file','dlfile','amount_display','downloadlink','remarks_display'];

    public function user()
    {
        return $this->belongsTo('QxCMS\Modules\Likod\Models\Clients\User', 'user_id');
    }

    public function customer()
    {
        return $this->belongsTo('QxCMS\Modules\Client\Models\CRM\Customer', 'customer_id');
    }

    public function product()
    {
        return $this->belongsTo('QxCMS\Modules\Client\Models\CRM\ActivityType', 'product_id');
    }

    public function getCreatedAtAttribute()
    {
        return Carbon::parse($this->attributes['created_at'])->format('M d, Y');
    }

    public function getAmountDisplayAttribute()
    {
        if($this->attributes['amount']){
            return number_format($this->attributes['amount'],2);
        }
        return '0.00';
    }

    public function getDownloadlinkAttribute()
    {
        if($this->count_file > 1){
            return '<a href="'.url('client/crm/customer',hashid($this->attributes['id'])).'/zipproposal" class="btn btn-primary btn-xs" target="_blank"><i class="fa fa-download fa-fw"></i></a>';
        }

        if($this->count_file == 1){
            return '<a href="'.url($this->dlfile).'" class="btn btn-primary btn-xs" target="_blank"><i class="fa fa-download fa-fw"></i></a>';
        }

        return '';
    }

    public function getCountFileAttribute()
    {
        if($this->attributes['file']!=''){
            $files = array_filter(explode('xnx', $this->attributes['file']));
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
        $path = 'uploads/customer/proposal/';
        if($this->attributes['file']!=''){
            $files = array_filter(explode('xnx', $this->attributes['file']));
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

    public function getRemarksDisplayAttribute()
    {
        return linkreplace($this->attributes['remarks']);
    }
}
