<?php

namespace App\Validations;

use App\Core\AppResult;
use Validator,Auth;

class ClientStatusValidation
{
    public function validate($payload)
    {
        $input = $payload->all();
        $validationMessages = [
            'name.required'=> __('validationMessage.name_required'),
        ];
        $validator = Validator::make($input , [
            'name' => 'required' ,
//            'type_id' => 'required|exists:types,id,model,admin' ,
        ],$validationMessages);
        if($validator->fails()){
            return AppResult::error($validator->messages()->first());
        }
        return AppResult::success(null);
    }


}
