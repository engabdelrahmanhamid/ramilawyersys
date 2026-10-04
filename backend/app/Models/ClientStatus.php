<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;

class ClientStatus extends Model
{
    use HasFactory;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function types()
    {
        return $this->belongsToMany(Type::class, 'client_status_types', 'status_id', 'type_id');
    }

//    public function type(){
//        return $this->belongsTo(Type::class,'type_id');
//    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */

    public function clients(){
        return $this->hasMany(Client::class,'status_id');
//            ->where('branch_id',Auth::user()->branch_id)
    }

}

