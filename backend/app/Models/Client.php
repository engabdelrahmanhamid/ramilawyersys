<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cases()
    {
        return $this->hasMany(UserCase::class, 'client_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function services()
    {
        return $this->hasMany(Service::class, 'client_id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type(){
        return $this->belongsTo(Type::class,'type_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function device(){
        return $this->belongsTo(Device::class,'device_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function source(){
        return $this->belongsTo(Source::class,'source_id');
    }

    /**
     * @return mixed
     */
    public function branch(){
        return $this->belongsTo(Branch::class,'branch_id');
    }

    /***
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function district(){
        return $this->belongsTo(District::class,'district_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function city(){
        return $this->belongsTo(City::class,'city_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country(){
        return $this->belongsTo(Country::class,'country_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status(){
        return $this->belongsTo(ClientStatus::class,'status_id');
    }
    /*
     *
     */
    public function admin(){
        return $this->belongsTo(Admin::class,'admin_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function files(){
        return $this->hasMany(ClientFile::class,'client_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function logs()
    {
        return $this->hasMany(Log::class, 'model_id')
            ->where('model_name', 'client')
            ->orderBy('created_at', 'desc');
    }

    /**
     * @param $clients
     * @param $filter
     * @return mixed
     */
    public function scopeFilter($clients,$filter)
    {
        if ($filter->search_text) {
            $clients->where(function($query) use ($filter) {
                $query->where('name', 'LIKE', '%' . $filter->search_text . '%')
                    ->orWhere('id', $filter->search_text)
                    ->orWhere('phone', 'LIKE', '%' . $filter->search_text . '%')
                    ->orWhere('secondary_phone', 'LIKE', '%' . $filter->search_text . '%');
            });
        }
        if($filter->status_id)
            $clients=$clients->where('status_id',$filter->status_id);
        if($filter->admin_id)
            $clients=$clients->where('admin_id',$filter->admin_id);

        if($filter->device_id)
            $clients=$clients->where('device_id',$filter->device_id);
        if($filter->source_id)
            $clients=$clients->where('source_id',$filter->source_id);
        if($filter->type_id)
            $clients=$clients->where('type_id',$filter->type_id);
        if ($filter->month)
            $clients = $clients->whereMonth('created_at', $filter->month);
         if ($filter->date_from)
             $clients->whereDate('created_at', '>=', $filter->date_from);
         if ($filter->date_to)
            $clients->whereDate('created_at', '<=', $filter->date_to);
        if($filter->branch_id)
            $clients=$clients->where('branch_id',$filter->branch_id);
        return $clients;


    }
}
