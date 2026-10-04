<?php

namespace App\Http\Controllers\Admin;
use App\Http\Collections\ContactCollection;
use App\Http\Resources\ContactResource;
use App\Repos\ContactRepo;
use App\Validations\ContactValidation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Validator, Auth, Artisan, Hash, File, Mail;

class ContactController extends Controller
{
    use \App\Traits\ApiResponseTrait;

    private $contactRepo;
    private $contactValidation;


    public function __construct(ContactRepo $contactRepo ,  ContactValidation $contactValidation)
    {
        $this->contactRepo = $contactRepo;
        $this->contactValidation = $contactValidation;

    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function get(Request $request)
    {
        $admin = Auth::user();
        if($admin->super != 1) {
            $request['branch_id'] = $admin->branch_id;
        }
        $data = $this->contactRepo->get($request);
        return $this->apiResponseData(new ContactCollection($data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function single(Request $request)
    {
        $response = $this->contactRepo->getContactById($request->contact_id);
        return $this->apiResponseData(new ContactResource($response->data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        App::setLocale($request->header('lang'));
        $validateContact = $this->contactValidation->validate($request);
        if($validateContact->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateContact->error,200);
        }
        $data = $this->contactRepo->create($request);
        return $this->apiResponseData(new ContactResource($data));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        App::setLocale($request->header('lang'));
        $response = $this->contactRepo->getContactById($request->contact_id);
        $contact=$response->data;
        $validateContact = $this->contactValidation->validate($request);
        if($validateContact->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateContact->error,200);
        }
        $data = $this->contactRepo->update($request,$contact);
        return $this->apiResponseData(new ContactResource($data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        App::setLocale($request->header('lang'));
        $response = $this->contactRepo->getContactById($request->contact_id);
        $this->contactRepo->delete($response->data);
        return $this->apiResponseMessage(1,'deleted successfully');
    }

}
