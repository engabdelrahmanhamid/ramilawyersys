<?php

namespace App\Repos;

use App\Core\AppResult;
use App\Helpers\ImageHelper;
use App\Models\ClientFile;
use Validator, Auth, Artisan, Hash, File, Crypt;

class ClientFileRepo
{
    /**
     * @param $id
     * @return AppResult
     */
    public function getClientFileById($id)
    {
        $ClientFile = ClientFile::findOrfail($id);
        return AppResult::success($ClientFile);
    }

    /**
     * @param $payload
     * @return ClientFile
     */
    public function create($payload)
    {
        $ClientFile = new ClientFile();
        $ClientFile->name = $payload->name;
        $ClientFile->date = $payload->date;
        $ClientFile->client_id = $payload->client_id;
        $ClientFile->file = ImageHelper::getInstance()->saveImage('Client',$payload->file);
        $ClientFile->save();
        return $ClientFile;
    }

    /**
     * @param $payload
     * @param $ClientFile
     * @return mixed
     */
    public function update($payload, $ClientFile)
    {
        $ClientFile->name = $payload->name;
        $ClientFile->date = $payload->date;
        $ClientFile->client_id = $payload->client_id;
        if($payload->file){
            ImageHelper::getInstance()->deleteFile('Client',$ClientFile->file);
            $ClientFile->file = ImageHelper::getInstance()->saveImage('Client',$payload->file);
        }
        $ClientFile->save();
        return $ClientFile;
    }

    /**
     * @param $ClientFile
     * @return void
     */
    public function delete($ClientFile)
    {
        ImageHelper::getInstance()->deleteFile('Client',$ClientFile->file);
        $ClientFile->delete();
    }

}
