<?php

namespace App\Validations;

use App\Core\AppResult;
use Illuminate\Support\Facades\App;
use Validator,Auth;

class ClientValidation
{
    public function validate($payload)
    {
        App::setLocale($payload->header('lang') ?: 'ar');
        // Store phone numbers and ID numbers as plain digits so format checks and duplicate checks work.
        foreach (['phone', 'secondary_phone', 'id_number'] as $field) {
            if (is_string($payload->$field))
                $payload[$field] = preg_replace('/[\s\-()]/', '', $this->toLatinDigits($payload->$field));
        }
        if (is_string($payload->email))
            $payload['email'] = trim($payload->email);

        $input = $payload->all();
        $client_id = $payload->client_id ? $payload->client_id : 0;
        $phoneFormat = 'regex:/^\+?[0-9]{8,15}$/';
        $validationMessages = [
            'name.required' => __('validationMessage.name_required'),
            'phone.required' => __('validationMessage.phone_required'),
            'phone.regex' => __('validationMessage.phone_invalid'),
            'secondary_phone.regex' => __('validationMessage.phone_invalid'),
            'email.email' => __('validationMessage.email_invalid'),
            'id_number.regex' => __('validationMessage.id_number_invalid'),
            'country_id.required' => __('validationMessage.country_required'),
            'device_id.required' => __('validationMessage.device_required'),
            'source_id.required' => __('validationMessage.source_required'),
            'type_id.required' => __('validationMessage.client_type_required'),
        ];
        $validator = Validator::make($input , [
            'name' => 'required' ,
            'country_id' => 'required|exists:countries,id' ,
            'device_id' => 'required|exists:devices,id' ,
            'source_id' => 'required|exists:sources,id' ,
            'type_id' => 'required|exists:types,id,model,client' ,
            'phone' => ['required', $phoneFormat, $this->notTakenBy('phone', $client_id, 'phone_taken_by')],
            'secondary_phone' => ['nullable', $phoneFormat],
            'email' => 'nullable|email',
            'id_number' => ['nullable', 'regex:/^[0-9]{5,20}$/', $this->notTakenBy('id_number', $client_id, 'id_number_taken_by')],
        ],$validationMessages);
        if($validator->fails()){
            // Return every problem at once instead of one per attempt.
            return AppResult::error(implode(' • ', array_unique($validator->messages()->all())));
        }
        return AppResult::success(null);
    }

    /**
     * Duplicate check that names the existing client. Phones match on their last 9 digits,
     * so 0501234567, 966501234567 and +966501234567 count as the same number.
     *
     * @param string $column
     * @param int $client_id
     * @param string $messageKey
     * @return \Closure
     */
    private function notTakenBy($column, $client_id, $messageKey)
    {
        return function ($attribute, $value, $fail) use ($column, $client_id, $messageKey) {
            if ($value === null || $value === '')
                return;
            $query = \App\Models\Client::where('id', '!=', $client_id);
            $digits = ltrim($value, '+');
            if ($column == 'phone' && strlen($digits) >= 9)
                $query->where('phone', 'LIKE', '%' . substr($digits, -9));
            else
                $query->where($column, $value);
            $existing = $query->first();
            if ($existing)
                $fail(__('validationMessage.' . $messageKey, ['name' => $existing->name, 'id' => $existing->id]));
        };
    }

    /**
     * @param string $value
     * @return string
     */
    private function toLatinDigits($value)
    {
        return strtr($value, [
            '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',
            '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
        ]);
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
