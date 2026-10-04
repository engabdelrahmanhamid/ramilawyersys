<?php

namespace App\Validations;

use App\Core\AppResult;
use Validator,Auth;

class SessionValidation
{
    public function validate($payload)
    {
        $input = $payload->all();
        $validationMessages = [
            'name.required'=> __('validationMessage.name_required'),
        ];
        $validator = Validator::make($input , [
//            'case_id' => 'required|exists:user_cases,id',
//            'admin_id' => 'required|exists:admins,id',
            'status_id' => 'required|exists:session_statuses,id',
            'session_reminder_date' => 'required',
        ],$validationMessages);
        if($validator->fails()){
            return AppResult::error($validator->messages()->first());
        }
        return AppResult::success(null);


    }
}
