<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    /***
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service_type(){
        return $this->belongsTo(ServiceType::class,'service_type_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payment_method(){
        return $this->belongsTo(PaymentMethod::class,'payment_method_id');
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
            ->where('model_name','service')
            ->orderBy('created_at', 'desc');
    }

    /**
     * @param $services
     * @param $filter
     * @return mixed
     */
    public function scopeFilter($services,$filter)
    {
        if($filter->search_text) {
            $services->where('name', 'LIKE','%'.$filter->search_text.'%')
                ->orWhere('id',$filter->search_text);
        }
        if($filter->payment_method_id)
            $services=$services->where('payment_method_id',$filter->payment_method_id);
        if($filter->service_type_id)
            $services=$services->where('service_type_id',$filter->service_type_id);
        if (isset($filter->payment_status)) {
            $services = $services->where('payment_status', $filter->payment_status);
        }
        if($filter->client_id)
            $services=$services->where('client_id',$filter->client_id);
        if($filter->admin_id)
            $services=$services->where('admin_id',$filter->admin_id);
        if($filter->branch_id)
            $services=$services->where('branch_id',$filter->branch_id);
        if($filter->status)
            $services=$services->where('status',$filter->status);
        if ($filter->month) {
            $services = $services->whereMonth('created_at', $filter->month);
        }
        if ($filter->date_from) {
            $services = $services->whereDate('created_at', '>=', $filter->date_from);
        }
        if ($filter->date_to) {
            $services = $services->whereDate('created_at', '<=', $filter->date_to);
        }
        return $services;
    }

}
