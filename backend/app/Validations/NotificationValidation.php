<?php

namespace App\Validations;

use App\Core\AppResult;
use Validator,Auth;

class NotificationValidation
{
    public function validate($payload)
    {
        $input = $payload->all();
        $validationMessages = [
        ];
        $validator = Validator::make($input , [
            'admin_id' => 'required|exists:admins,id' ,
        ],$validationMessages);
        if($validator->fails()){
            return AppResult::error($validator->messages()->first());
        }
        return AppResult::success(null);
    }
}


