<?php

namespace App\Validations;

use App\Core\AppResult;
use Validator,Auth;

class ReplayValidation
{
    public function validate($payload)
    {
        $input = $payload->all();
        $validationMessages = [
            'name.required'=> __('validationMessage.name_required'),
        ];
        $validator = Validator::make($input , [
            'message' => 'required' ,
            'log_id' => 'required|exists:logs,id' ,
        ],$validationMessages);
        if($validator->fails()){
            return AppResult::error($validator->messages()->first());
        }
        return AppResult::success(null);
    }
}


