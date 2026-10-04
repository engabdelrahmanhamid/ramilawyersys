<?php

namespace App\Http\Controllers\Admin;
use App\Http\Collections\ContactReasonCollection;
use App\Http\Resources\ContactReasonResource;
use App\Repos\ContactReasonRepo;
use App\Validations\ContactReasonValidation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Validator, Auth, Artisan, Hash, File, Mail;

class ContactReasonController extends Controller
{
    use \App\Traits\ApiResponseTrait;

    private $contactReasonRepo;
    private $contactReasonValidation;


    public function __construct(ContactReasonRepo $contactReasonRepo ,  ContactReasonValidation $contactReasonValidation)
    {
        $this->contactReasonRepo = $contactReasonRepo;
        $this->contactReasonValidation = $contactReasonValidation;

    }

    /***
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function get(Request $request)
    {
        $data = $this->contactReasonRepo->get($request);
        return $this->apiResponseData(new ContactReasonCollection($data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function single(Request $request)
    {
        $response = $this->contactReasonRepo->getContactReasonById($request->contactReason_id);
        return $this->apiResponseData(new ContactReasonResource($response->data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        App::setLocale($request->header('lang'));
        $validateContactReason = $this->contactReasonValidation->validate($request);
        if($validateContactReason->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateContactReason->error,200);
        }
        $data = $this->contactReasonRepo->create($request);
        return $this->apiResponseData(new ContactReasonResource($data));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        App::setLocale($request->header('lang'));
        $response = $this->contactReasonRepo->getContactReasonById($request->contactReason_id);
        $contact_reason=$response->data;
        $validateContactReason = $this->contactReasonValidation->validate($request);
        if($validateContactReason->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateContactReason->error,200);
        }
        $data = $this->contactReasonRepo->update($request,$contact_reason);
        return $this->apiResponseData(new ContactReasonResource($data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        App::setLocale($request->header('lang'));
        $response = $this->contactReasonRepo->getContactReasonById($request->contactReason_id);
        $this->contactReasonRepo->delete($response->data);
        return $this->apiResponseMessage(1,'deleted successfully');
    }

}
