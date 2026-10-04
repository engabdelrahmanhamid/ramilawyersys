<?php

namespace App\Http\Controllers\Admin;

use App\Http\Collections\CountryCollection;
use App\Http\Collections\PaymentMethodCollection;
use App\Http\Resources\CountryResource;
use App\Http\Resources\PaymentMethodResource;
use App\Models\PaymentMethod;
use App\Repos\CountryRepo;
use App\Repos\PaymentMethodRepo;
use App\Repos\ReceiptRepo;
use App\Repos\ServiceReceiptRepo;
use App\Validations\CountryValidation;
use App\Validations\PaymentMethodValidation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Validator, Auth, Artisan, Hash, File, Mail;

class PageController extends Controller
{
    use \App\Traits\ApiResponseTrait;

    /**
     * @param Request $request
     * @param ReceiptRepo $receiptRepo
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function case_receipt(Request $request,ReceiptRepo $receiptRepo)
    {
        $data=$receiptRepo->single($request->id);
        return view('page.single_case_receipt',compact('data'));
    }

    public function service_receipt(Request $request,ServiceReceiptRepo $serviceReceiptRepo){
        $data=$serviceReceiptRepo->single($request->id);
        return view('page.single_service_receipt',compact('data'));

    }
}
