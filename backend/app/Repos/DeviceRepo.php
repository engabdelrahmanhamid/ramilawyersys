<?php

namespace App\Repos;

use App\Core\AppResult;
use App\Models\Device;
use Validator,Auth,Artisan,Hash,File,Crypt;

class DeviceRepo{

    /**
     * @param $filter
     * @return mixed
     */
    public function get($filter)
    {
        $devices=Device::orderBy('id','desc');
        $limit=$filter->limit ? $filter->limit : 10;
        $devices=$devices->paginate($limit);
        return $devices;
    }


    /**
     * @param $id
     * @return AppResult
     */
    public function getDeviceById($id)
    {
        $device=Device::where('id',$id)->firstOrFail();
        return AppResult::success($device);
    }

    /**
     * @param $payload
     * @return Device
     */
    public function create($payload)
    {
        $device=new Device();
        $device->name=$payload->name;
        $device->save();
        return $device;
    }

    /**
     * @param $payload
     * @param $device
     * @return mixed
     */
    public function update($payload,$device)
    {
        if (isset($payload->name))
            $device->name=$payload->name;
        $device->save();
        return $device;
    }

    /**
     * @param $device
     */
    public function delete($device)
    {
        $device->delete();
    }


}
