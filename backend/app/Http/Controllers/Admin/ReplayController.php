<?php

namespace App\Http\Controllers\Admin;

use App\Http\Collections\CountryCollection;
use App\Http\Collections\ReplayCollection;
use App\Http\Collections\ServiceCollection;
use App\Http\Resources\CountryResource;
use App\Http\Resources\ReplayResource;
use App\Models\Replay;
use App\Repos\CountryRepo;
use App\Repos\ReplayRepo;
use App\Validations\CountryValidation;
use App\Validations\ReplayValidation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Validator, Auth, Artisan, Hash, File, Mail;

class ReplayController extends Controller
{
    use \App\Traits\ApiResponseTrait;

    private $replayRepo;
    private $replayValidation;

    public function __construct(ReplayRepo $replayRepo, ReplayValidation $replayValidation)
    {
        $this->replayRepo = $replayRepo;
        $this->replayValidation = $replayValidation;

    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function get(Request $request)
    {
        $data = $this->replayRepo->get($request);
        return $this->apiResponseData(new ReplayCollection($data));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function single(Request $request)
    {
        $response = $this->replayRepo->getReplayById($request->replay_id);
        return $this->apiResponseData(new ReplayResource($response->data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $validateReplay = $this->replayValidation->validate($request);
        if($validateReplay->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateReplay->error,200);
        }
        $data = $this->replayRepo->create($request);
        return $this->apiResponseData(new ReplayResource($data));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $response = $this->replayRepo->getReplayById($request->replay_id);
        $replay=$response->data;
        $validateReplay = $this->replayValidation->validate($request);
        if($validateReplay->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateReplay->error,200);
        }
        $data = $this->replayRepo->update($request,$replay);
        return $this->apiResponseData(new ReplayResource($data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $response = $this->replayRepo->getReplayById($request->replay_id);
        $this->replayRepo->delete($response->data);
        return $this->apiResponseMessage(1,'deleted successfully');
    }

}
