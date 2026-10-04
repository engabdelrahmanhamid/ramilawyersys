<?php

namespace App\Http\Controllers\Admin;
use App\Http\Collections\ServiceReceiptCollection;
use App\Http\Resources\CaseReceiptResource;
use App\Http\Resources\ServiceReceiptResource;
use App\Repos\CaseRepo;
use App\Repos\ReceiptRepo;
use App\Repos\ServiceReceiptRepo;
use App\Repos\ServiceRepo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator, Auth, Artisan, Hash, File, Mail;

class ServiceReceiptController extends Controller
{
    use \App\Traits\ApiResponseTrait;

    private $serviceReceiptRepo;
    private $serviceRepo;

    /**
     * @param ServiceReceiptRepo $serviceReceiptRepo
     * @param ServiceRepo $serviceRepo
     */
    public function __construct(ServiceReceiptRepo $serviceReceiptRepo , ServiceRepo $serviceRepo)
    {
        $this->serviceReceiptRepo = $serviceReceiptRepo;
        $this->serviceRepo = $serviceRepo;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function get(Request $request){
        $data=$this->serviceReceiptRepo->get($request);
        return $this->apiResponseData(new ServiceReceiptCollection($data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function pay_service_receipt(Request $request)
    {
        $response = $this->serviceReceiptRepo->getServiceReceiptById($request->service_receipt_id);
        $service_receipt = $response->data;
        $service = $service_receipt->service;
        if ($service_receipt->payment_status == 1)
            return $this->apiResponseMessage(0, __('responseMessage.paid_receipt'));
        if ($request->pay_amount <= 0 || $request->pay_amount > $service_receipt->unpaid_amount)
            return $this->apiResponseMessage(0, __('responseMessage.invalid_amount'));
        $this->serviceReceiptRepo->cal_paid_and_unpaid_amount($service_receipt,$request);
        $this->serviceReceiptRepo->changeReceiptPaymentStatus($service_receipt,$request);
        $this->serviceRepo->changeDeposit($service,$request);
        $this->serviceRepo->changePaymentStatus($service);
        return $this->apiResponseData(new ServiceReceiptResource($service_receipt), __('responseMessage.service_receipt_paid_successfully'),200);
    }


}
