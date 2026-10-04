<?php

namespace App\Validations;

use App\Core\AppResult;
use Validator,Auth;

class ContactValidation
{
    public function validate($payload)
    {
        $input = $payload->all();
        $validationMessages = [
        ];
        $validator = Validator::make($input , [
            'contact_type_id' => 'required|exists:types,id,model,contact' ,
            'contact_reason_id' => 'required|exists:contact_reasons,id' ,
            'client_id' => 'required|exists:clients,id' ,
        ],$validationMessages);
        if($validator->fails()){
            return AppResult::error($validator->messages()->first());
        }
        return AppResult::success(null);
    }
}

