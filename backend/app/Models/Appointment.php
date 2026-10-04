<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client(){
        return $this->belongsTo(Client::class,'client_id');
    }

    /***
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function admin(){
        return $this->belongsTo(Admin::class,'createdBy_id');
    }

    /**
     * @param $data
     * @param $filter
     * @return mixed
     */
    public function scopeFilter($data,$filter)
    {
        if ($filter->search_text) {
            $data->where(function ($query) use ($filter) {
                $query->whereHas('client', function ($query) use ($filter) {
                    $query->where('name', 'LIKE', '%' . $filter->search_text . '%')
                        ->orWhere('phone', 'LIKE', '%' . $filter->search_text . '%');
                });
            });
        }
        if($filter->client_id)
            $data=$data->where('client_id',$filter->client_id);
        if($filter->admin_id)
            $data=$data->where('createdBy_id',$filter->admin_id);
        if ($filter->branch_id) {
            $data = $data->whereHas('client', function ($query) use ($filter) {
                $query->where('branch_id', $filter->branch_id);
            });
        }
        if($filter->client_type)
            $data=$data->where('client_type',$filter->client_type);
        if($filter->status)
            $data=$data->where('status',$filter->status);
        if ($filter->month) {
            $data = $data->whereMonth('date', $filter->month);
        }
        if ($filter->date_from) {
            $data = $data->whereDate('date', '>=', $filter->date_from);
        }
        if ($filter->date_to) {
            $data = $data->whereDate('date', '<=', $filter->date_to);
        }
        return $data;
    }
}

