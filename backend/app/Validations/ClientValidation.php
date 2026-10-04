<?php

namespace App\Validations;

use App\Core\AppResult;
use Validator,Auth;

class ClientValidation
{
    public function validate($payload)
    {
        $input = $payload->all();
        $client_id=$payload->client_id ? $payload->client_id : null;
        $validationMessages = [
            'name.required'=> __('validationMessage.name_required'),
        ];
        $validator = Validator::make($input , [
            'name' => 'required' ,
            'country_id' => 'required|exists:countries,id' ,
            'device_id' => 'required|exists:devices,id' ,
            'source_id' => 'required|exists:sources,id' ,
            'type_id' => 'required|exists:types,id,model,client' ,
            'phone' => $client_id == 0 ? 'required||numeric|unique:clients' : 'required||numeric|unique:clients,phone,' . $client_id,
            'id_number' => $client_id == 0 ? 'required||numeric|unique:clients' : 'required||numeric|unique:clients,id_number,' . $client_id,
        ],$validationMessages);
        if($validator->fails()){
            return AppResult::error($validator->messages()->first());
        }
        return AppResult::success(null);
    }

    /**
     * @param $payload
     * @return AppResult
     */
    public function validateStatus($payload)
    {
        $input = $payload->all();

        $validator = Validator::make($input , [
            'status_id' => 'required|exists:client_statuses,id' ,
        ]);
        if($validator->fails()){
            return AppResult::error($validator->messages()->first());
        }
        return AppResult::success(null);
    }
}
