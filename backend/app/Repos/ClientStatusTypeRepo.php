<?php

namespace App\Repos;

use App\Models\ClientStatusType;
use Validator,Auth,Artisan,Hash,File,Crypt;

class ClientStatusTypeRepo{
    use \App\Traits\ApiResponseTrait;

    /**
     * @param $payloadString
     * @param $status
     * @return void
     */
    public function createArray($payloadString, $status)
    {
        ClientStatusType::where('status_id', $status->id)->delete();
        $payloadArray = array_map('trim', explode(',', $payloadString));
        foreach ($payloadArray as $row) {
            if (is_numeric($row)) {
                $this->create((int) $row, $status->id);
            }
        }
    }


    /**
     * @param $type_id
     * @param $status_id
     * @return void
     */
    public function create($type_id, $status_id)
    {
        $clientStatusType = ClientStatusType::where('status_id', $status_id)
            ->where('type_id', $type_id)
            ->first();
        if (is_null($clientStatusType)) {
            $clientStatusType = new ClientStatusType();
        }
        $clientStatusType->status_id = $status_id;
        $clientStatusType->type_id = $type_id;
        $clientStatusType->save();
    }

}
