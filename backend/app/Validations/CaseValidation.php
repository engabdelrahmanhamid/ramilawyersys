<?php

namespace App\Validations;

use App\Core\AppResult;
use Validator,Auth;

class CaseValidation
{
    public function validate($payload)
    {
        $input = $payload->all();
        $validationMessages = [
            'name.required'=> __('validationMessage.name_required'),
        ];
        $validator = Validator::make($input , [
            'name' => 'required' ,
            'opponent_type_id' => 'required|exists:types,id,model,opponent' ,
            'case_type_id' => 'required|exists:types,id,model,case' ,
            'client_id' => 'required|exists:clients,id' ,
        ],$validationMessages);
        if($validator->fails()){
            return AppResult::error($validator->messages()->first());
        }
        return AppResult::success(null);
    }
}

