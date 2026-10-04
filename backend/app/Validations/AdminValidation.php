<?php

namespace App\Validations;

use App\Core\AppResult;
use Validator,Auth;

class AdminValidation
{
    /***
     * @param $payload
     *  i can customize error messages
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|mixed
     */
    public function validate($payload)
    {
        $input = $payload->all();
        $admin_id = $payload->admin_id ?: Auth::user()->id;
        $validationMessages = [
            'phone.required'=> __('validationMessage.phone_required'),
            'phone.unique'=> __('validationMessage.phone_unique'),
            'email.required'=> __('validationMessage.email_required'),
            'email.unique'=> __('validationMessage.email_unique'),
        ];
        $validator = Validator::make($input, [
            'phone' => $admin_id == 0 ? 'required|numeric|unique:admins' : 'required||numeric|unique:admins,phone,' . $admin_id,
            'email' => $admin_id == 0 ? 'required|unique:admins|regex:/(.+)@(.+)\.(.+)/i' : 'required|unique:admins,email,' . $admin_id . '|regex:/(.+)@(.+)\.(.+)/i',
            'type_id' => 'required|exists:types,id,model,admin' ,
        ],$validationMessages);
        if ($validator->fails()) {
            return AppResult::error($validator->messages()->first());
        }
        return AppResult::success(null);
    }
}
