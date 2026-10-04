<?php

namespace App\Http\Controllers\Admin;

use App\Http\Collections\AppointmentCollection;
use App\Http\Collections\CaseCollection;
use App\Http\Collections\CaseReceiptCollection;
use App\Http\Collections\ServiceReceiptCollection;
use App\Http\Collections\SessionCollection;
use App\Http\Collections\TaskCollection;
use App\Repos\AppointmentRepo;
use App\Repos\CaseRepo;
use App\Repos\ReceiptRepo;
use App\Repos\ServiceReceiptRepo;
use App\Repos\SessionRepo;
use App\Repos\TaskRepo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator, Auth, Artisan, Hash, File, Mail;

class CalenderController extends Controller
{
    use \App\Traits\ApiResponseTrait;


    private $caseRepo;
    private $sessionRepo;
    private $appointmentRepo;
    private $taskRepo;
    private $receiptRepo;
    private $serviceReceiptRepo;


    public function __construct(CaseRepo $caseRepo
        ,SessionRepo $sessionRepo
        , AppointmentRepo $appointmentRepo
        , TaskRepo $taskRepo
        ,ReceiptRepo $receiptRepo
        ,ServiceReceiptRepo $serviceReceiptRepo)
    {
        $this->caseRepo = $caseRepo;
        $this->sessionRepo = $sessionRepo;
        $this->appointmentRepo = $appointmentRepo;
        $this->taskRepo = $taskRepo;
        $this->receiptRepo = $receiptRepo;
        $this->serviceReceiptRepo = $serviceReceiptRepo;

    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function get_calender(Request $request)
    {
        $cases = $this->caseRepo->get($request);
        $sessions = $this->sessionRepo->getOnlySessions($request);
        $revisions = $this->sessionRepo->getOnlyRevisions($request);
        $old_clients = $this->appointmentRepo->get_old_clients($request);
        $new_clients = $this->appointmentRepo->get_new_clients($request);
        $case_receipts = $this->receiptRepo->get($request);
        $service_receipts = $this->serviceReceiptRepo->get($request);
        $tasks = $this->taskRepo->get($request);
        return $this->apiResponseData([
            'cases' => new CaseCollection($cases),
            'sessions' => new SessionCollection($sessions),
            'revisions' => new SessionCollection($revisions),
            'appointments' => [
                'old_client' => new AppointmentCollection($old_clients),
                'new_client' => new AppointmentCollection($new_clients),
            ],
            'case_receipts' => new CaseReceiptCollection($case_receipts),
            'service_receipts' => new ServiceReceiptCollection($service_receipts),
            'tasks' => new TaskCollection($tasks),
        ]);
    }
}
