<?php

namespace App\Validations;

use App\Core\AppResult;
use Validator,Auth;

class AdminFileValidation
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
            'admin_id' => 'required|exists:admins,id' ,
        ],$validationMessages);
        if($validator->fails()){
            return AppResult::error($validator->messages()->first());
        }
        return AppResult::success(null);
    }
}
