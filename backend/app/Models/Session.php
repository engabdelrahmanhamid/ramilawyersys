<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function userCase(){
        return $this->belongsTo(UserCase::class,'case_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function admin(){
        return $this->belongsTo(Admin::class,'admin_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client(){
        return $this->belongsTo(Client::class,'client_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status(){
        return $this->belongsTo(SessionStatus::class,'status_id');
    }

    /**
     *
     * @param $sessions
     * @param $filter
     * @return mixed
     */
    public function scopeFilter($sessions,$filter)
    {
        if ($filter->search_text) {
            $sessions->where(function ($query) use ($filter) {
                $query->where('link', 'LIKE', '%' . $filter->search_text . '%')
                    ->orWhereHas('client', function ($query) use ($filter) {
                        $query->where('name', 'LIKE', '%' . $filter->search_text . '%')
                            ->orWhere('phone', 'LIKE', '%' . $filter->search_text . '%');
                    })
                    ->orWhereHas('userCase', function ($query) use ($filter) {
                        $query->where('name', 'LIKE', '%' . $filter->search_text . '%');
                    })
                    ->orWhereHas('admin', function ($query) use ($filter) {
                        $query->where('name', 'LIKE', '%' . $filter->search_text . '%');
                    });
            });
        }
        if($filter->case_id)
            $sessions=$sessions->where('case_id',$filter->case_id);
        if($filter->type)
            $sessions=$sessions->where('type',$filter->type);
        if($filter->admin_id)
            $sessions=$sessions->where('admin_id',$filter->admin_id);
        if($filter->client_id)
            $sessions=$sessions->where('client_id',$filter->client_id);
        if($filter->status_id)
            $sessions=$sessions->where('status_id',$filter->status_id);
        if ($filter->branch_id) {
            $sessions = $sessions->where(function ($query) use ($filter) {
                $query->whereHas('admin', function ($q) use ($filter) {
                    $q->where('branch_id', $filter->branch_id);
                });
            });
        }
        if ($filter->remainder == 1)
        {
            $tomorrow = now()->addDay()->startOfDay();
            $threeDaysLater = now()->addDays(3)->endOfDay();
            $sessions = $sessions->whereBetween('gregorian_date', [$tomorrow, $threeDaysLater]);
        }
        if ($filter->month) {
            $sessions = $sessions->whereMonth('gregorian_date', $filter->month);
        }
        if ($filter->date_from) {
            $sessions = $sessions->whereDate('gregorian_date', '>=', $filter->date_from);
        }
        if ($filter->date_to) {
            $sessions = $sessions->whereDate('gregorian_date', '<=', $filter->date_to);
        }
        return $sessions;
    }


}

