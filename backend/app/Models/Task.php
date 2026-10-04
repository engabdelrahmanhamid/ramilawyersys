<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function task_type(){
        return $this->belongsTo(Type::class,'task_type_id');
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
    public function created_by(){
        return $this->belongsTo(Admin::class,'created_by_id');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function userCase(){
        return $this->belongsTo(UserCase::class,'model_id');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client_(){
        return $this->belongsTo(Client::class,'client_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client(){
        return $this->belongsTo(Client::class,'model_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function session(){
        return $this->belongsTo(Session::class,'model_id');
    }

    public function service(){
        return $this->belongsTo(Service::class,'model_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function logs(){
        return $this->hasMany(Log::class,'model_id')
            ->where('model_name','task')
            ->orderBy('created_at', 'desc');
    }


    /**
     * @param $tasks
     * @param $filter
     * @return mixed
     */

    public function scopeFilter($tasks,$filter)
    {
        if ($filter->search_text) {
            $tasks->where(function ($query) use ($filter) {
                $query->where('model_type', $filter->search_text . '%')
                    ->orWhere('id', $filter->search_text . '%');
            });

        }
        if ($filter->admin_id || $filter->created_by_id) {
            $tasks->where(function ($query) use ($filter) {
                if ($filter->admin_id) {
                    $query->where('admin_id', $filter->admin_id);
                }
                if ($filter->created_by_id) {
                    $query->orWhere('created_by_id', $filter->created_by_id);
                }

            });
        }
        if($filter->status_id)
            $tasks=$tasks->where('status',$filter->status_id);
        if($filter->client_id) {
            $tasks = $tasks->where(function($query) use ($filter) {
                $query->where('client_id', $filter->client_id)
                    ->orWhere(function($query) use ($filter) {
                        $query->where('model_type', 'client')
                            ->where('model_id', $filter->client_id);
                    });
            });
        }
        if($filter->type_id)
            $tasks=$tasks->where('task_type_id',$filter->type_id);
        if($filter->model_type)
            $tasks=$tasks->where('model_type',$filter->model_type);
        if($filter->model_id)
            $tasks=$tasks->where('model_id',$filter->model_id);
        if ($filter->branch_id) {
            $tasks = $tasks->where(function ($query) use ($filter) {
                $query->whereHas('admin', function ($q) use ($filter) {
                    $q->where('branch_id', $filter->branch_id);
                });
            });
        }
        if ($filter->month) {
            $tasks = $tasks->whereMonth('created_at', $filter->month);
        }
        if ($filter->date_from) {
            $tasks = $tasks->whereDate('created_at', '>=', $filter->date_from);
        }
        if ($filter->date_to) {
            $tasks = $tasks->whereDate('created_at', '<=', $filter->date_to);
        }
        return $tasks;
    }

    /**
     * The moment the task becomes late. A date without a time means the end of that day.
     *
     * @return \Carbon\Carbon|null
     */
    public function dueAt()
    {
        if (!$this->end_date)
            return null;
        try {
            $due = \Carbon\Carbon::parse($this->end_date);
        } catch (\Exception $e) {
            return null;
        }
        return strlen(trim($this->end_date)) <= 10 ? $due->endOfDay() : $due;
    }

    /**
     * @return bool
     */
    public function isOverdue()
    {
        $due = $this->dueAt();
        return (int) $this->status === 1 && $due && $due->isPast();
    }
}
