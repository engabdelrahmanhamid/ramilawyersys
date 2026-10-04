<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AdminExport;
use App\Exports\AppointmentExport;
use App\Exports\CaseExport;
use App\Exports\CaseReceiptExport;
use App\Exports\ClientExport;
use App\Exports\ContactExport;
use App\Exports\ServiceExport;
use App\Exports\ServiceReceiptExport;
use App\Exports\SessionExport;
use App\Exports\TaskExport;
use App\Http\Collections\ClientCollection;
use App\Http\Resources\ClientResource;
use App\Repos\AdminRepo;
use App\Repos\AppointmentRepo;
use App\Repos\CaseRepo;
use App\Repos\ClientRepo;
use App\Repos\ContactRepo;
use App\Repos\LogRepo;
use App\Repos\ReceiptRepo;
use App\Repos\ServiceReceiptRepo;
use App\Repos\ServiceRepo;
use App\Repos\SessionRepo;
use App\Repos\TaskRepo;
use App\Validations\ClientValidation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Validator, Auth, Artisan, Hash, File, Mail,Excel;

class ExportController extends Controller
{
    use \App\Traits\ApiResponseTrait;

    /**
     * @param Request $request
     * @param ClientRepo $clientRepo
     * @return mixed
     */
    public function clients(Request $request,ClientRepo $clientRepo)
    {
        $request['export']=1;
        $data = $clientRepo->get($request);
        return Excel::download(new ClientExport($data), now() . '.xlsx');
    }

    /**
     * @param Request $request
     * @param CaseRepo $caseRepo
     * @return mixed
     */
    public function cases(Request $request,CaseRepo $caseRepo)
    {
        $request['export']=1;
        $data = $caseRepo->get($request);
        return Excel::download(new CaseExport($data), now() . '.xlsx');
    }

    /**
     * @param Request $request
     * @param ReceiptRepo $caseReceiptRepo
     * @return mixed
     */
    public function case_receipts(Request $request,ReceiptRepo $caseReceiptRepo)
    {
        $request['export']=1;
        $data = $caseReceiptRepo->get($request);
        return Excel::download(new CaseReceiptExport($data), now() . '.xlsx');
    }

    /**
     * @param Request $request
     * @param ServiceRepo $serviceRepo
     * @return mixed
     */
    public function services(Request $request,ServiceRepo $serviceRepo)
    {
        $request['export']=1;
        $data = $serviceRepo->get($request);
        return Excel::download(new ServiceExport($data), now() . '.xlsx');
    }

    /**
     * @param Request $request
     * @param ServiceReceiptRepo $serviceReceiptRepo
     * @return mixed
     */
    public function service_receipts(Request $request,ServiceReceiptRepo $serviceReceiptRepo)
    {
        $request['export']=1;
        $data = $serviceReceiptRepo->get($request);
        return Excel::download(new ServiceReceiptExport($data), now() . '.xlsx');
    }

    /**
     * @param Request $request
     * @param SessionRepo $sessionRepo
     * @return mixed
     */
    public function sessions(Request $request,SessionRepo $sessionRepo)
    {
        $request['export']=1;
        $sessions = $sessionRepo->get($request);
        return Excel::download(new SessionExport($sessions), now() . '.xlsx');
    }

    /**
     * @param Request $request
     * @param AdminRepo $adminRepo
     * @return mixed
     */
    public function admins(Request $request,AdminRepo $adminRepo)
    {
        $request['export']=1;
        $data = $adminRepo->get($request);
        return Excel::download(new AdminExport($data), now() . '.xlsx');
    }


    /**
     * @param Request $request
     * @param TaskRepo $taskRepo
     * @return mixed
     */
    public function tasks(Request $request,TaskRepo $taskRepo)
    {
        $request['export']=1;
        $data = $taskRepo->get($request);
        return Excel::download(new TaskExport($data), now() . '.xlsx');
    }

    /**
     * @param Request $request
     * @param AppointmentRepo $appointmentRepo
     * @return mixed
     */
    public function appointments(Request $request,AppointmentRepo $appointmentRepo)
    {
        $request['export']=1;
        $data = $appointmentRepo->get($request);
        return Excel::download(new AppointmentExport($data), now() . '.xlsx');
    }

    /**
     * @param Request $request
     * @param ContactRepo $contactRepo
     * @return mixed
     */
    public function contacts(Request $request,ContactRepo $contactRepo)
    {
        $request['export']=1;
        $data = $contactRepo->get($request);
        return Excel::download(new ContactExport($data), now() . '.xlsx');
    }


}
