<?php

namespace App\Validations;

use App\Core\AppResult;
use Validator,Auth;

class AppointmentValidation
{
    public function validate($payload)
    {
        $input = $payload->all();
        $validationMessages = [
            'date.required'=> __('validationMessage.date_required'),
        ];
        $validator = Validator::make($input , [
            'date' => 'required' ,
            'client_id' => 'required|exists:clients,id' ,
        ],$validationMessages);
        if($validator->fails()){
            return AppResult::error($validator->messages()->first());
        }
        return AppResult::success(null);
    }
}

