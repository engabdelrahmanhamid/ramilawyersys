<?php

namespace App\Repos;

use App\Core\AppResult;
use App\Helpers\ImageHelper;
use App\Models\AdminFile;
use Validator, Auth, Artisan, Hash, File, Crypt;

class AdminFileRepo
{
    /**
     * @param $id
     * @return AppResult
     */
    public function getAdminFileById($id)
    {
        $adminFile = AdminFile::findOrfail($id);
        return AppResult::success($adminFile);
    }

    /**
     * @param $payload
     * @return AdminFile
     */
    public function create($payload)
    {
        $adminFile = new AdminFile();
        $adminFile->name = $payload->name;
        $adminFile->date = $payload->date;
        $adminFile->admin_id = $payload->admin_id;
        $adminFile->file = ImageHelper::getInstance()->saveImage('AdminFile',$payload->file);
        $adminFile->save();
        return $adminFile;
    }

    /**
     * @param $payload
     * @param $adminFile
     * @return mixed
     */
    public function update($payload, $adminFile)
    {
        $adminFile->name = $payload->name;
        $adminFile->date = $payload->date;
        $adminFile->admin_id = $payload->case_id;
        if($payload->file){
            ImageHelper::getInstance()->deleteFile('AdminFile',$adminFile->file);
            $adminFile->file = ImageHelper::getInstance()->saveImage('AdminFile',$payload->file);
        }
        $adminFile->save();
        return $adminFile;
    }

    /**
     * @param $adminFile
     * @return void
     */
    public function delete($adminFile)
    {
        ImageHelper::getInstance()->deleteFile('AdminFile',$adminFile->file);
        $adminFile->delete();
    }

}
