<?php

namespace App\Repos;

use App\Core\AppResult;
use App\Helpers\ImageHelper;
use App\Models\CaseFile;
use App\Models\ClientFile;
use Validator, Auth, Artisan, Hash, File, Crypt;

class CaseFileRepo
{
    /**
     * @param $id
     * @return AppResult
     */
    public function getCaseFileById($id)
    {
        $caseFile = CaseFile::findOrfail($id);
        return AppResult::success($caseFile);
    }

    /**
     * @param $payload
     * @return CaseFile
     */
    public function create($payload)
    {
        $caseFile = new CaseFile();
        $caseFile->name = $payload->name;
        $caseFile->date = $payload->date;
        $caseFile->case_id = $payload->case_id;
        $caseFile->file = ImageHelper::getInstance()->saveImage('Case',$payload->file);
        $caseFile->save();
        return $caseFile;
    }

    /**
     * @param $payload
     * @param $caseFile
     * @return mixed
     */
    public function update($payload, $caseFile)
    {
        $caseFile->name = $payload->name;
        $caseFile->date = $payload->date;
        $caseFile->case_id = $payload->case_id;
        if($payload->file){
            ImageHelper::getInstance()->deleteFile('CaseFile',$caseFile->file);
            $caseFile->file = ImageHelper::getInstance()->saveImage('CaseFile',$payload->file);
        }
        $caseFile->save();
        return $caseFile;
    }

    /**
     * @param $caseFile
     * @return void
     */
    public function delete($caseFile)
    {
        ImageHelper::getInstance()->deleteFile('Case',$caseFile->file);
        $caseFile->delete();
    }

}
