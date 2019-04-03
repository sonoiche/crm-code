<?php

namespace QxCMS\Modules\Client\Models\CRM;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ProposalApproval extends Model
{
    protected $connection = 'client';
    protected $table = "proposal_approval";
    protected $fillable = [
    	'customer_id',
        'product_id',
        'name',
        'status',
        'approver',
        'requestor',
        'fyi',
        'doc_file',
        'link',
        'remarks',
        'pro_status',
        'pro_chance'
    ];

    protected $appends = ['created_at_display','hashid'];

    public function getHashidAttribute()
    {
        return hashid($this->attributes['id']);
    }

    public function userrequestor()
    {
        return $this->belongsTo('QxCMS\Modules\Likod\Models\Clients\User', 'requestor');
    }

    public function userapprover()
    {
        return $this->belongsTo('QxCMS\Modules\Likod\Models\Clients\User', 'approver');
    }

    public function customer()
    {
        return $this->belongsTo('QxCMS\Modules\Client\Models\CRM\Customer', 'customer_id');
    }

    public function proversion()
    {
        return $this->hasMany('QxCMS\Modules\Client\Models\CRM\ProposalVersion', 'proposal_id')->orderBy('created_at','desc');
    }

    public function proversionfirst()
    {
        return $this->hasOne('QxCMS\Modules\Client\Models\CRM\ProposalVersion', 'proposal_id')->orderBy('created_at','desc');
    }

    public function activitytype()
    {
        return $this->belongsTo('QxCMS\Modules\Client\Models\CRM\ActivityType', 'product_id');
    }

    public function getCreatedAtDisplayAttribute()
    {
        if(isset($this->attributes['created_at']) && $this->attributes['created_at']!='0000-00-00 00:00:00'){
            return Carbon::parse($this->attributes['created_at'])->format('M d, Y');
        }
        return '';

    }
}