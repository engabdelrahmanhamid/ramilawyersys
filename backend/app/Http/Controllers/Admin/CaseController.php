<?php

namespace App\Http\Controllers\Admin;

use App\Http\Collections\CaseCollection;
use App\Http\Collections\ClientCollection;
use App\Http\Resources\CaseReportResource;
use App\Http\Resources\CaseResource;
use App\Http\Resources\ClientResource;
use App\Http\Resources\HomeResource;
use App\Repos\CaseRepo;
use App\Repos\CaseStatusHistoryRepo;
use App\Repos\ClientRepo;
use App\Repos\LogRepo;
use App\Repos\ReceiptRepo;
use App\Repos\SettingRepo;
use App\Validations\CaseValidation;
use App\Validations\ClientValidation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Validator, Auth, Artisan, Hash, File, Mail;

class CaseController extends Controller
{
    use \App\Traits\ApiResponseTrait;

    private $caseRepo;
    private $caseValidation;
    private $receiptRepo;
    private $caseStatusHistoryRepo;
    private $settingRepo;

    public function __construct(CaseRepo $caseRepo
        , CaseValidation $caseValidation
        , ReceiptRepo $receiptRepo
        , CaseStatusHistoryRepo $caseStatusHistoryRepo
        ,SettingRepo $settingRepo)
    {
        $this->caseRepo = $caseRepo;
        $this->caseValidation = $caseValidation;
        $this->receiptRepo = $receiptRepo;
        $this->caseStatusHistoryRepo = $caseStatusHistoryRepo;
        $this->settingRepo = $settingRepo;

    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function get(Request $request)
    {
        $cases = $this->caseRepo->get($request);
        return $this->apiResponseData(new CaseCollection($cases));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function single(Request $request)
    {
        $response = $this->caseRepo->getCaseById($request->case_id);
        $request['case_logs']=1;
        return $this->apiResponseData(new CaseResource($response->data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        App::setLocale($request->header('lang'));
        $request['makeReceipts'] = json_decode($request->makeReceipts, true);
            $validateCase = $this->caseValidation->validate($request);
        if($validateCase->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateCase->error,200);
        }
        if($request->payment_type == 2) {
            $receiptsAmount = $this->receiptRepo->checkReceiptValue($request);
            if ($receiptsAmount != $request->amount)
                return $this->apiResponseMessage(0, __('responseMessage.amount_not_valid'));
        }

        $data = $this->caseRepo->create($request);
        LogRepo::create($data,'case','add');
        if ($request->payment_type == 1) {
            $this->receiptRepo->createReceipt($request, $data);
        }
        if ($request->payment_type == 2) {
            $this->receiptRepo->save_receipts($request, $data);
        }
        return $this->apiResponseData(new CaseResource($data));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $response = $this->caseRepo->getCaseById($request->case_id);
        $case=$response->data;
        $validateCase = $this->caseValidation->validate($request);
        if($validateCase->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateCase->error,200);
        }
        $this->caseStatusHistoryRepo->create($case,$request);
        $data = $this->caseRepo->update($request,$case);
        LogRepo::create($data,'case','update');
        return $this->apiResponseData(new CaseResource($data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function change_status(Request $request)
    {
        $response = $this->caseRepo->getCaseById($request->case_id);
        $case=$response->data;
        $this->caseStatusHistoryRepo->create($case,$request);
        $data = $this->caseRepo->changeStatus($request,$case);
        LogRepo::logStatusUpdate($case, 'case', 'update_status');
        return $this->apiResponseData(new CaseResource($data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        App::setLocale($request->header('lang'));
        $response = $this->caseRepo->getCaseById($request->case_id);
        $this->caseRepo->delete($response->data);
        LogRepo::create($response->data,'case','delete');
        return $this->apiResponseMessage(1,'deleted successfully');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function make_report(Request $request)
    {
        $request['no_pagination'] = true;
        $cases = $this->caseRepo->get($request);
        $total_amount = $cases->sum('amount');
        $total_deposit = $cases->sum('deposit');

        return $this->apiResponseData(new CaseReportResource([
            'total_amount' => $total_amount,
            'total_deposit' => $total_deposit,
        ]));
    }
}
