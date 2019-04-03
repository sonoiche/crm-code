<?php

namespace QxCMS\Modules\Client\Models\CRM;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ProposalVersion extends Model
{
    protected $connection = 'client';
    protected $table = "proposal_version";
    protected $fillable = [
    	'proposal_id',
        'requestor_remarks',
        'approver_remarks',
        'status',
        'version',
        'doc_file',
        'prostatus',
        'amount',
        'chances'
    ];

    protected $appends = ['dlfile','created_at_display','remarks_display','hashid','amount_display','count_file'];

    public function getHashidAttribute()
    {
        return hashid($this->attributes['id']);
    }

    public function getCountFileAttribute()
    {
        if($this->attributes['doc_file']!=''){
            $files = array_filter(explode('xnx', $this->attributes['doc_file']));
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
        if($this->attributes['doc_file']!=''){
            $files = array_filter(explode('xnx', $this->attributes['doc_file']));
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
        if(isset($this->attributes['created_at']) && $this->attributes['created_at']!='0000-00-00 00:00:00'){
            return Carbon::parse($this->attributes['created_at'])->format('M d, Y');
        }
        return '';

    }

    public function getRemarksDisplayAttribute()
    {
        if($this->attributes['requestor_remarks']){
            return linkreplace($this->attributes['requestor_remarks']);
        }
    }

    public function getAmountDisplayAttribute()
    {
        if($this->attributes['amount']){
            return number_format($this->attributes['amount'],2);
        }
        return '0.00';
    }
}
