<?php

namespace App\Http\Controllers\Admin;

use App\Http\Collections\ClientCollection;
use App\Http\Resources\ClientResource;
use App\Repos\ClientRepo;
use App\Repos\LogRepo;
use App\Validations\ClientValidation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Validator, Auth, Artisan, Hash, File, Mail;

class ClientController extends Controller
{
    use \App\Traits\ApiResponseTrait;

    private $clientRepo;
    private $clientValidation;


    public function __construct(ClientRepo $clientRepo , ClientValidation $clientValidation)
    {
        $this->clientRepo = $clientRepo;
        $this->clientValidation = $clientValidation;

    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function get(Request $request)
    {
        $data = $this->clientRepo->get($request);
        return $this->apiResponseData(new ClientCollection($data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function single(Request $request)
    {
        $response = $this->clientRepo->getClientById($request->client_id);
        $request['client_logs']=1;
        return $this->apiResponseData(new ClientResource($response->data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $admin=Auth::user();
        $validateClient = $this->clientValidation->validate($request);
        if($validateClient->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateClient->error,200);
        }
        $request['branch_id']=$admin->super ? $request->branch_id : $admin->branch_id;
        $data = $this->clientRepo->create($request);
        LogRepo::create($data,'client','add');
        return $this->apiResponseData(new ClientResource($data));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $response = $this->clientRepo->getClientById($request->client_id);
        $Client=$response->data;
        $request['client_id']=$request->client_id;
        $validateClient = $this->clientValidation->validate($request);
        if($validateClient->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateClient->error,200);
        }
        $data = $this->clientRepo->update($request,$Client);
        LogRepo::create($data,'client','update');
        return $this->apiResponseData(new ClientResource($data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function change_status(Request $request){
        $response = $this->clientRepo->getClientById($request->client_id);
        $client=$response->data;
        $validateClient = $this->clientValidation->validateStatus($request);
        if($validateClient->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateClient->error,200);
        }
         $this->clientRepo->change_status($client,$request);
        LogRepo::logStatusUpdate($client, 'client', 'update_status');
        return $this->apiResponseData(new ClientResource($client));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        App::setLocale($request->header('lang'));
        $response = $this->clientRepo->getClientById($request->client_id);
        $client = $response->data;
        if ($client->cases()->exists())
            return $this->apiResponseMessage(0, __('validationMessage.cannot_delete_client'));
        $this->clientRepo->delete($response->data);
        LogRepo::create($response->data,'client','delete');
        return $this->apiResponseMessage(1,'deleted successfully');
    }

}
