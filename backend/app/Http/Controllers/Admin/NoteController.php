<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\CaseFileResource;
use App\Http\Resources\NoteResource;
use App\Repos\CaseFileRepo;
use App\Repos\NoteRepo;
use App\Validations\CaseFileValidation;
use App\Validations\NoteValidation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Validator, Auth, Artisan, Hash, File, Mail;

class NoteController extends Controller
{
    use \App\Traits\ApiResponseTrait;

    private $noteRepo;
    private $noteValidation;


    public function __construct(NoteRepo $noteRepo , NoteValidation $noteValidation)
    {
        $this->noteRepo = $noteRepo;
        $this->noteValidation = $noteValidation;

    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request['create']=1;
        $validateNote = $this->noteValidation->validate($request);
        if($validateNote->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateNote->error,200);
        }
        $data = $this->noteRepo->create($request);
        return $this->apiResponseData(new NoteResource($data));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $response = $this->noteRepo->getNoteById($request->note_id);
        $note=$response->data;
        $validateNote = $this->noteValidation->validate($request);
        if($validateNote->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateNote->error,200);
        }
        $data = $this->noteRepo->update($request,$note);
        return $this->apiResponseData(new NoteResource($data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $response = $this->noteRepo->getNoteById($request->note_id);
        $this->noteRepo->delete($response->data);
        return $this->apiResponseMessage(1,'deleted successfully');
    }

}
