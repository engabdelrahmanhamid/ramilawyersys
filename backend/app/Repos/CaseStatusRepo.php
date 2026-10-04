<?php

namespace App\Repos;

use App\Core\AppResult;
use App\Models\CaseStatus;
use Validator, Auth, Artisan, Hash, File, Crypt;

class CaseStatusRepo
{

    /**
     * @param $filter
     * @return mixed
     */
    public function get($filter)
    {
        $caseStatuses = CaseStatus::orderBy('id', 'desc');
        $limit = $filter->limit ? $filter->limit : 10;
        $caseStatuses = $caseStatuses->paginate($limit);
        return $caseStatuses;
    }


    /**
     * @param $id
     * @return AppResult
     */
    public function getCaseStatusById($id)
    {
        $CaseStatus = CaseStatus::findOrfail($id);
        return AppResult::success($CaseStatus);
    }

    /**
     * @param $payload
     * @return CaseStatus
     */
    public function create($payload)
    {
        $CaseStatus = new CaseStatus();
        $CaseStatus->name = $payload->name;
        $CaseStatus->save();
        return $CaseStatus;
    }

    /**
     * @param $payload
     * @param $CaseStatus
     * @return mixed
     */
    public function update($payload, $CaseStatus)
    {
        $CaseStatus->name = $payload->name;
        $CaseStatus->save();
        return $CaseStatus;
    }

    /**
     * @param $CaseStatus
     * @return void
     */
    public function delete($CaseStatus)
    {
        $CaseStatus->delete();
    }


}
