<?php

namespace App\Http\Controllers\Admin;

use App\Http\Collections\ClientStatusCollection;
use App\Http\Resources\CampanClientResource;
use App\Http\Resources\StatusResource;
use App\Repos\ClientStatusRepo;
use App\Repos\ClientStatusTypeRepo;
use App\Validations\ClientStatusValidation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Validator, Auth, Artisan, Hash, File, Mail;

class ClientStatusController extends Controller
{
    use \App\Traits\ApiResponseTrait;

    private $ClientStatusRepo;
    private $ClientStatusValidation;
    private $clientStatusTypeRepo;

    public function __construct(ClientStatusRepo $ClientStatusRepo
        , ClientStatusValidation                 $ClientStatusValidation
        , ClientStatusTypeRepo                   $clientStatusTypeRepo)
    {
        $this->ClientStatusRepo = $ClientStatusRepo;
        $this->ClientStatusValidation = $ClientStatusValidation;
        $this->clientStatusTypeRepo = $clientStatusTypeRepo;

    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function get(Request $request)
    {
        $clientStatuses = $this->ClientStatusRepo->get($request);
        return $this->apiResponseData(new ClientStatusCollection($clientStatuses));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function get_clients(Request $request)
    {
        $admin = Auth::user();
        if ($admin->super != 1)
            $request['type_id'] = $admin->type_id;
        $clientStatuses = $this->ClientStatusRepo->get($request);
        return $this->apiResponseData(CampanClientResource::collection($clientStatuses));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function single(Request $request)
    {
        $response = $this->ClientStatusRepo->getClientStatusById($request->client_status_id);
        return $this->apiResponseData(new StatusResource($response->data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $validateClientStatus = $this->ClientStatusValidation->validate($request);
        if ($validateClientStatus->operationType == ERROR) {
            return $this->apiResponseMessage(0, $validateClientStatus->error, 200);
        }
        $data = $this->ClientStatusRepo->create($request);
        if (isset($request->type_id)) {
            $this->clientStatusTypeRepo->createArray($request->type_id, $data);
        }
        return $this->apiResponseData(new StatusResource($data));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $response = $this->ClientStatusRepo->getClientStatusById($request->status_id);
        $ClientStatus = $response->data;
        $validateClientStatus = $this->ClientStatusValidation->validate($request);
        if ($validateClientStatus->operationType == ERROR) {
            return $this->apiResponseMessage(0, $validateClientStatus->error, 200);
        }
        $data = $this->ClientStatusRepo->update($request, $ClientStatus);
        if (isset($request->type_id)) {
            $this->clientStatusTypeRepo->createArray($request->type_id, $data);
        }
        return $this->apiResponseData(new StatusResource($data));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        App::setLocale($request->header('lang'));
        $response = $this->ClientStatusRepo->getClientStatusById($request->client_status_id);
        $this->ClientStatusRepo->delete($response->data);
        return $this->apiResponseMessage(1, 'deleted successfully');
    }

}

