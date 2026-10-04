<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;

    /**
     *
     */
    public function city()
    {
        $this->belongsTo(City::class , 'city_id');
    }
}
