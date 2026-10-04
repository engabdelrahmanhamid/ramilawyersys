<?php

namespace App\Repos;

use App\Core\AppResult;
use App\Models\CaseReceipt;
use App\Models\CaseStatusHistory;
use Validator,Auth,Artisan,Hash,File,Crypt;

class CaseStatusHistoryRepo{

    use \App\Traits\ApiResponseTrait;

    /**
     * @param $filter
     * @return mixed
     */
    public function get($filter){
        $data=CaseStatusHistory::orderBy('id','desc')->where('case_id',$filter->case_id);
        $data=$data->paginate(10);
        return $data;
    }


    /**
     * @param $payload
     * @param $case
     * @return CaseStatusHistory
     */
    public function create($case,$payload)
    {
        $caseStatusHistory = new CaseStatusHistory();
        $caseStatusHistory->case_id = $case->id;
        $caseStatusHistory->old_status_id = $case->case_status_id;
        $caseStatusHistory->current_status_id = $payload->case_status_id;
        $caseStatusHistory->save();
        return $caseStatusHistory;
    }


}
