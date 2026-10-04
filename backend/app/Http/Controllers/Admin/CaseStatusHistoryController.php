<?php

namespace App\Http\Controllers\Admin;

use App\Http\Collections\CaseStatusHistoryCollection;
use App\Http\Collections\CountryCollection;
use App\Http\Resources\CaseStatusHistoryResource;
use App\Http\Resources\CountryResource;
use App\Repos\CaseStatusHistoryRepo;
use App\Repos\CountryRepo;
use App\Validations\CountryValidation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Validator, Auth, Artisan, Hash, File, Mail;

class CaseStatusHistoryController extends Controller
{
    use \App\Traits\ApiResponseTrait;

    private $caseStatusHistoryRepo;

    public function __construct(CaseStatusHistoryRepo $caseStatusHistoryRepo )
    {
        $this->caseStatusHistoryRepo = $caseStatusHistoryRepo;

    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function get(Request $request)
    {
        $data = $this->caseStatusHistoryRepo->get($request);
        return $this->apiResponseData(new CaseStatusHistoryCollection($data));
    }

}
