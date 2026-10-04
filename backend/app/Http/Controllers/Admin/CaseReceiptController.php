<?php

namespace App\Http\Controllers\Admin;
use App\Http\Collections\CaseReceiptCollection;
use App\Http\Resources\CaseReceiptResource;
use App\Repos\CaseRepo;
use App\Repos\ReceiptRepo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator, Auth, Artisan, Hash, File, Mail;

class CaseReceiptController extends Controller
{
    use \App\Traits\ApiResponseTrait;

    private $receiptRepo;
    private $caseRepo;

    /**
     * @param ReceiptRepo $receiptRepo
     * @param CaseRepo $caseRepo
     */
    public function __construct(ReceiptRepo $receiptRepo ,CaseRepo $caseRepo)
    {
        $this->receiptRepo = $receiptRepo;
        $this->caseRepo = $caseRepo;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function get(Request $request){
        $data=$this->receiptRepo->get($request);
        return $this->apiResponseData(new CaseReceiptCollection($data));
    }



    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function pay_receipt(Request $request)
    {
        $response = $this->receiptRepo->getReceiptById($request->receipt_id);
        $receipt = $response->data;
        $case = $receipt->userCase;
        if ($receipt->payment_status == 1)
            return $this->apiResponseMessage(0, __('responseMessage.paid_receipt'));
        if ($request->pay_amount <= 0 || $request->pay_amount > $receipt->unpaid_amount)
            return $this->apiResponseMessage(0, __('responseMessage.invalid_amount'));
        $this->receiptRepo->cal_paid_and_unpaid_amount($receipt,$request);
        $this->receiptRepo->changeReceiptPaymentStatus($receipt,$request);
        $this->caseRepo->changeDeposit($case,$request);
        $this->caseRepo->changePaymentStatus($case);
        return $this->apiResponseData(new CaseReceiptResource($receipt), __('responseMessage.receipt_paid_successfully'),200);
    }


}
