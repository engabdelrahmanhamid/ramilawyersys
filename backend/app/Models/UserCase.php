<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCase extends Model
{
    use HasFactory;


    public function case_type(){
        return $this->belongsTo(Type::class,'case_type_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function opponent_type(){
        return $this->belongsTo(Type::class,'opponent_type_id');
    }

    public function payment_method(){
        return $this->belongsTo(PaymentMethod::class,'payment_method_id');
    }


    public function status(){
        return $this->belongsTo(CaseStatus::class,'case_status_id');
    }

    public function client(){
        return $this->belongsTo(Client::class,'client_id');
    }


    public function admin(){
        return $this->belongsTo(Admin::class,'admin_id');
    }

    public function branch(){
        return $this->belongsTo(Branch::class,'branch_id');
    }

    public function logs(){
        return $this->hasMany(Log::class,'model_id')
            ->where('model_name','case')
            ->orderBy('created_at', 'desc');
    }

    public function files(){
        return $this->hasMany(CaseFile::class,'case_id');
    }

    public function notes(){
        return $this->hasMany(Note::class,'case_id');
    }

    public function sessions(){
        return $this->hasMany(Session::class,'case_id');
    }

    public function statuses_history(){
        return $this->hasMany(CaseStatusHistory::class,'case_id');
    }

    /**
     * @param $cases
     * @param $filter
     * @return mixed
     */
    public function scopeFilter($cases,$filter)
    {
        if ($filter->search_text) {
            $cases->where(function ($query) use ($filter) {
                $query->where('name', 'LIKE', '%' . $filter->search_text . '%')
                    ->orWhere('id', $filter->search_text)
                    ->orWhere('number', 'LIKE', '%' . $filter->search_text . '%')
                    ->orWhereHas('client', function ($query) use ($filter) {
                        $query->where('name', 'LIKE', '%' . $filter->search_text . '%')
                            ->orWhere('phone', 'LIKE', '%' . $filter->search_text . '%');
                    });
            });
        }
        if ($filter->device_id) {
            $cases = $cases->whereHas('client', function ($query) use ($filter) {
                $query->where('device_id', $filter->device_id);
            });
        }

        if ($filter->source_id) {
            $cases = $cases->whereHas('client', function ($query) use ($filter) {
                $query->where('source_id', $filter->source_id);
            });
        }
        if (isset($filter->payment_status)) {
            $cases = $cases->where('payment_status', $filter->payment_status);
        }
        if($filter->opponent_type_id)
            $cases=$cases->where('opponent_type_id',$filter->opponent_type_id);
        if($filter->type_id)
            $cases=$cases->where('case_type_id',$filter->type_id);
        if($filter->status_id)
            $cases=$cases->where('case_status_id',$filter->status_id);
        if($filter->client_id)
            $cases=$cases->where('client_id',$filter->client_id);
        if($filter->admin_id)
            $cases=$cases->where('admin_id',$filter->admin_id);
        if($filter->branch_id)
            $cases=$cases->where('branch_id',$filter->branch_id);
        if ($filter->month) {
            $cases = $cases->whereMonth('gregorian_date', $filter->month);
        }
        if ($filter->date_from) {
            $cases = $cases->whereDate('gregorian_date', '>=', $filter->date_from);
        }
        if ($filter->date_to) {
            $cases = $cases->whereDate('gregorian_date', '<=', $filter->date_to);
        }
        return $cases;
    }


}
