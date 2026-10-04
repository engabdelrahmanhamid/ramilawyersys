<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseStatusHistory extends Model
{
    use HasFactory;

    public function current_status(){
        return $this->belongsTo(CaseStatus::class,'current_status_id');
    }

    public function old_status(){
        return $this->belongsTo(CaseStatus::class,'old_status_id');
    }

    public function user_case(){
        return $this->belongsTo(UserCase::class,'case_id');
    }










}
