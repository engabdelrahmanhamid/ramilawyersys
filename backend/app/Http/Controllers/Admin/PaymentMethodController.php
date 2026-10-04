<?php

namespace App\Http\Controllers\Admin;

use App\Http\Collections\CountryCollection;
use App\Http\Collections\PaymentMethodCollection;
use App\Http\Resources\CountryResource;
use App\Http\Resources\PaymentMethodResource;
use App\Models\PaymentMethod;
use App\Repos\CountryRepo;
use App\Repos\PaymentMethodRepo;
use App\Validations\CountryValidation;
use App\Validations\PaymentMethodValidation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Validator, Auth, Artisan, Hash, File, Mail;

class PaymentMethodController extends Controller
{
    use \App\Traits\ApiResponseTrait;

    private $paymentMethodRepo;
    private $paymentMethodValidation;


    public function __construct(PaymentMethodRepo $paymentMethodRepo , PaymentMethodValidation $paymentMethodValidation)
    {
        $this->paymentMethodRepo = $paymentMethodRepo;
        $this->paymentMethodValidation = $paymentMethodValidation;

    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function get(Request $request)
    {
        $payment_methods = $this->paymentMethodRepo->get($request);
        return $this->apiResponseData(new PaymentMethodCollection($payment_methods));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function single(Request $request)
    {
        $response = $this->paymentMethodRepo->getPaymentMethodById($request->payment_method_id);
        return $this->apiResponseData(new PaymentMethodResource($response->data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        App::setLocale($request->header('lang'));
        $validatePaymentMethod = $this->paymentMethodValidation->validate($request);
        if($validatePaymentMethod->operationType==ERROR){
            return $this->apiResponseMessage(0,$validatePaymentMethod->error,200);
        }
        $data = $this->paymentMethodRepo->create($request);
        return $this->apiResponseData(new PaymentMethodResource($data));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        App::setLocale($request->header('lang'));
        $response = $this->paymentMethodRepo->getPaymentMethodById($request->payment_method_id);
        $payment_method=$response->data;
        $validatePaymentMethod = $this->paymentMethodValidation->validate($request);
        if($validatePaymentMethod->operationType==ERROR){
            return $this->apiResponseMessage(0,$validatePaymentMethod->error,200);
        }
        $data = $this->paymentMethodRepo->update($request,$payment_method);
        return $this->apiResponseData(new PaymentMethodResource($data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        App::setLocale($request->header('lang'));
        $response = $this->paymentMethodRepo->getPaymentMethodById($request->payment_method_id);
        $this->paymentMethodRepo->delete($response->data);
        return $this->apiResponseMessage(1,'deleted successfully');
    }

}
