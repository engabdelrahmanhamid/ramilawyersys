<?php

namespace App\Validations;

use App\Core\AppResult;
use Validator,Auth;

class ClientFileValidation
{
    public function validate($payload)
    {
        $input = $payload->all();
        $validationMessages = [
            'name.required'=> __('validationMessage.name_required'),
        ];
        $validator = Validator::make($input , [
            'name' => 'required' ,
            'file' => $payload->create ? 'required|max:10000|mimes:doc,docx,png,jpg,pdf' : '' ,
            'client_id' => 'required|exists:clients,id' ,
        ],$validationMessages);
        if($validator->fails()){
            return AppResult::error($validator->messages()->first());
        }
        return AppResult::success(null);
    }
}
