<?php

namespace App\Repos;

use App\Core\AppResult;
use App\Helpers\ImageHelper;
use App\Models\Client;
use App\Models\UserCase;
use Validator, Auth, Artisan, Hash, File, Crypt;

class ClientRepo
{

    /**
     *
     * @param $filter
     * @return mixed
     */
    public function get($filter)
    {
        $clients = Client::orderBy('id', 'desc')->filter($filter);
        $limit = $filter->limit ? $filter->limit : 10;
        if($filter->export)
            return $clients=$clients->get();
        return $clients->paginate($limit);
    }

    /**
     * @param $filter
     * @return mixed
     */
    public function getReport($filter){
        return Client::orderBy('id', 'desc')->filter($filter);
    }


    /**
     * @param $id
     * @return AppResult
     */
    public function getClientById($id)
    {
        $client = Client::findOrfail($id);
        return AppResult::success($client);
    }
    // public function getClientById($id)
    // {
    //     $client = Client::with([
    //         'branch',
    //         'country',
    //         'city',
    //         'district',
    //         'type',
    //         'device',
    //         'source',
    //         'status',
    //         'admin',
    //         'files',
    //     ])->findOrFail($id);
    
    //     return AppResult::success($client);
    // }

    /**
     * @param $payload
     * @return Client
     */
    public function create($payload)
    {
        $client = new Client();
        $client->name = $payload->name;
        $client->phone = $payload->phone;
        $client->secondary_phone = $payload->secondary_phone;
        $client->email = $payload->email;
        $client->id_number = $payload->id_number;
        $client->tax_number = $payload->tax_number;
        $client->commercial_register = $payload->commercial_register;
        $client->address = $payload->address;
        $client->case_type = $payload->case_type;
        $client->country_id = $payload->country_id;
        $client->city_id = $payload->city_id;
        $client->district_id = $payload->district_id;
        $client->type_id = $payload->type_id;
        $client->status_id = $payload->status_id;
        $client->device_id = $payload->device_id;
        $client->source_id = $payload->source_id;
        $client->admin_id = $payload->admin_id;
        $client->branch_id = $payload->branch_id;
        if($payload->image && !is_string($payload->image))
            $client->image=ImageHelper::getInstance()->saveImage('Client',$payload->image);
        $client->save();
        return $client;
    }

    /**
     * @param $payload
     * @param $client
     * @return mixed
     */
    public function update($payload, $client)
    {
        $client->name = $payload->name;
        $client->phone = $payload->phone;
        $client->secondary_phone = $payload->secondary_phone;
        $client->email = $payload->email;
        $client->id_number = $payload->id_number;
        $client->tax_number = $payload->tax_number;
        $client->commercial_register = $payload->commercial_register;
        $client->address = $payload->address;
        $client->case_type = $payload->case_type;
        $client->country_id = $payload->country_id;
        $client->city_id = $payload->city_id;
        $client->district_id = $payload->district_id;
        $client->type_id = $payload->type_id;
        $client->status_id = $payload->status_id;
        $client->device_id = $payload->device_id;
        $client->source_id = $payload->source_id;
        $client->admin_id = $payload->admin_id;
        $client->branch_id = $payload->branch_id;
        if($payload->image){
            ImageHelper::getInstance()->deleteFile('Client',$client->image);
            $client->image=ImageHelper::getInstance()->saveImage('Client',$payload->image);
        }
        $client->status_updated_at=now();
        $client->save();
        return $client;
    }



    /**
     * @param $client
     * @param $payload
     */
    public function change_status($client,$payload){
        $client->status_id=$payload->status_id;
        $client->status_updated_at=now();
        $client->save();
    }

    /**
     * @param $client
     * @return void
     */
    public function delete($client)
    {
        ImageHelper::getInstance()->deleteFile('Client',$client->image);
        $client->delete();
    }


}
