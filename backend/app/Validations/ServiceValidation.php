<?php

namespace App\Validations;

use App\Core\AppResult;
use Validator,Auth;

class ServiceValidation
{
    public function validate($payload)
    {
        $input = $payload->all();
        $validationMessages = [
            'name.required'=> __('validationMessage.name_required'),
        ];
        $validator = Validator::make($input , [
            'name' => 'required' ,
//            'payment_method_id' => 'required|exists:payment_methods,id' ,
            'service_type_id' => 'required|exists:service_types,id' ,
            'client_id' => 'required|exists:clients,id' ,
        ],$validationMessages);
        if($validator->fails()){
            return AppResult::error($validator->messages()->first());
        }
        return AppResult::success(null);
    }
}

