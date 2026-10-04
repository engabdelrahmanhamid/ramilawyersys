<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\DateHelper;
use App\Http\Collections\CaseCollection;
use App\Http\Collections\ClientCollection;
use App\Http\Collections\SessionCollection;
use App\Http\Resources\CaseResource;
use App\Http\Resources\HomeResource;
use App\Repos\AppointmentRepo;
use App\Repos\CaseRepo;
use App\Repos\ClientRepo;
use App\Repos\ReceiptRepo;
use App\Repos\ServiceReceiptRepo;
use App\Repos\SessionRepo;
use App\Repos\TaskRepo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator, Auth, Artisan, Hash, File, Mail;

class DateController extends Controller
{
    use \App\Traits\ApiResponseTrait;
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function convertToHijri(Request $request)
    {
        $gregorianDate = $request->gregorian_date;
        $hijriDate = DateHelper::getInstance()->gregorianToHijri($gregorianDate);
        return response()->json([
            'status' => 1,
            'message' => 'date converted successfully.',
            'data' => [
                'gregorian_date' => $gregorianDate,
                'hijri_date' => $hijriDate,
            ],
        ]);
    }




}
