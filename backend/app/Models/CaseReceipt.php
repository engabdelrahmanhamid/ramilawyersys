<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseReceipt extends Model
{
    use HasFactory;

    public function UserCase(){
        return $this->belongsTo(UserCase::class,'case_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payment_method(){
        return $this->belongsTo(PaymentMethod::class,'payment_method_id');
    }


    /**
     * @param $data
     * @param $filter
     * @return mixed
     */
    public function scopeFilter($data,$filter)
    {
        if ($filter->search_text) {
            $data = $data->where(function ($query) use ($filter) {
                $query->whereHas('UserCase', function ($q) use ($filter) {
                    $q->where('name', 'LIKE', '%' . $filter->search_text . '%');
                })->orWhereHas('UserCase.client', function ($q) use ($filter) {
                    $q->where('name', 'LIKE', '%' . $filter->search_text . '%');
                });
            });
        }
        if ($filter->client_id) {
            $data = $data->whereHas('UserCase', function ($query) use ($filter) {
                $query->where('client_id', $filter->client_id);
            });
        }
        if($filter->case_id)
            $data=$data->where('case_id',$filter->case_id);
        if(isset($filter->payment_status))
            $data=$data->where('payment_status',$filter->payment_status);
        if ($filter->branch_id) {
            $data = $data->whereHas('UserCase', function ($query) use ($filter) {
                $query->where('branch_id', $filter->branch_id);
            });
        }
        if ($filter->admin_id) {
            $data = $data->whereHas('UserCase', function ($query) use ($filter) {
                $query->where('admin_id', $filter->admin_id);
            });
        }
        if($filter->incomplete_amount) {
            $data = $data->where('payment_status', 0);
        }
        if ($filter->remainder == 1) {
            $tomorrow = now()->addDay()->startOfDay();
            $threeDaysLater = now()->addDays(3)->endOfDay();
            $data = $data->whereBetween('date', [$tomorrow, $threeDaysLater])
                ->where('payment_status', 0);
        }
        if ($filter->date_from) {
            $data = $data->whereDate('date', '>=', $filter->date_from);
        }
        if ($filter->date_to) {
            $data = $data->whereDate('date', '<=', $filter->date_to);
        }
        if ($filter->month) {
            $data = $data->whereMonth('date', $filter->month);
        }
        return $data;
    }
}
