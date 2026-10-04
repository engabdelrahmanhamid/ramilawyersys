<?php

namespace App\Http\Controllers\Admin;

use App\Http\Collections\CaseCollection;
use App\Http\Collections\ClientCollection;
use App\Http\Collections\ServiceCollection;
use App\Http\Resources\CaseResource;
use App\Http\Resources\ClientResource;
use App\Http\Resources\ServiceResource;
use App\Repos\CaseRepo;
use App\Repos\ClientRepo;
use App\Repos\LogRepo;
use App\Repos\ReceiptRepo;
use App\Repos\ServiceReceiptRepo;
use App\Repos\ServiceRepo;
use App\Validations\CaseValidation;
use App\Validations\ClientValidation;
use App\Validations\ServiceValidation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Validator, Auth, Artisan, Hash, File, Mail;

class ServiceController extends Controller
{
    use \App\Traits\ApiResponseTrait;

    private $serviceRepo;
    private $serviceValidation;
    private $serviceReceiptRepo;


    public function __construct(ServiceRepo $serviceRepo , ServiceValidation $serviceValidation , ServiceReceiptRepo $serviceReceiptRepo)
    {
        $this->serviceRepo = $serviceRepo;
        $this->serviceValidation = $serviceValidation;
        $this->serviceReceiptRepo = $serviceReceiptRepo;



    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function get(Request $request)
    {
        $services = $this->serviceRepo->get($request);
        return $this->apiResponseData(new ServiceCollection($services));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function single(Request $request)
    {
        $response = $this->serviceRepo->getServiceById($request->service_id);
        $request['logs']=1;
        return $this->apiResponseData(new ServiceResource($response->data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        App::setLocale($request->header('lang'));
        $request['makeServiceReceipts'] = json_decode($request->makeServiceReceipts, true);
            $validateService = $this->serviceValidation->validate($request);
        if($validateService->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateService->error,200);
        }
        if($request->payment_type == 2) {
            $serviceReceiptsAmount = $this->serviceReceiptRepo->checkServiceReceiptValue($request);
            if ($serviceReceiptsAmount != $request->amount)
                return $this->apiResponseMessage(0, __('responseMessage.amount_not_valid'));
        }
        $data = $this->serviceRepo->create($request);
        LogRepo::create($data,'service','add');
        if ($request->payment_type == 1) {
            $this->serviceReceiptRepo->createReceipt($request, $data);
        }
        if ($request->payment_type == 2)
            $this->serviceReceiptRepo->save_service_receipts($request, $data);
        return $this->apiResponseData(new ServiceResource($data));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $response = $this->serviceRepo->getServiceById($request->service_id);
        $service=$response->data;
        $validateService = $this->serviceValidation->validate($request);
        if($validateService->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateService->error,200);
        }
        $data = $this->serviceRepo->update($request,$service);
        LogRepo::create($data,'service','update');
        return $this->apiResponseData(new ServiceResource($data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function change_status(Request $request)
    {
        $response = $this->serviceRepo->getServiceById($request->service_id);
        $service=$response->data;
        $data = $this->serviceRepo->change_status($service,$request);
        return $this->apiResponseData(new ServiceResource($data));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        App::setLocale($request->header('lang'));
        $response = $this->serviceRepo->getServiceById($request->service_id);
        $this->serviceRepo->delete($response->data);
        LogRepo::create($response->data,'service','delete');
        return $this->apiResponseMessage(1,'deleted successfully');
    }

}
