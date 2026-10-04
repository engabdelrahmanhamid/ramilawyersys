<?php

namespace App\Http\Controllers\Admin;

use App\Http\Collections\CaseCollection;
use App\Http\Collections\ClientCollection;
use App\Http\Collections\NumberCollection;
use App\Http\Collections\ServiceCollection;
use App\Http\Collections\SessionCollection;
use App\Http\Collections\SliderCollection;
use App\Http\Collections\TestimonialCollection;
use App\Http\Collections\WhyMarkoonCollection;
use App\Http\Resources\CaseResource;
use App\Http\Resources\HomeResource;
use App\Repos\AppointmentRepo;
use App\Repos\CaseRepo;
use App\Repos\ClientRepo;
use App\Repos\ReceiptRepo;
use App\Repos\ServiceReceiptRepo;
use App\Repos\ServiceRepo;
use App\Repos\SessionRepo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator, Auth, Artisan, Hash, File, Mail;

class HomeController extends Controller
{
    use \App\Traits\ApiResponseTrait;

    private $caseRepo;
    private $sessionRepo;
    private $clientRepo;
    private $receiptRepo;
    private $appointmentRepo;
    private $serviceRepo;
    private $serviceReceiptRepo;

    public function __construct(CaseRepo $caseRepo
        ,SessionRepo $sessionRepo
        ,ClientRepo $clientRepo
        ,ReceiptRepo $receiptRepo
        ,AppointmentRepo $appointmentRepo
        ,ServiceRepo $serviceRepo,ServiceReceiptRepo $serviceReceiptRepo)
    {
        $this->caseRepo = $caseRepo;
        $this->sessionRepo = $sessionRepo;
        $this->clientRepo = $clientRepo;
        $this->receiptRepo = $receiptRepo;
        $this->appointmentRepo = $appointmentRepo;
        $this->serviceRepo = $serviceRepo;
        $this->serviceReceiptRepo = $serviceReceiptRepo;


    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function home(Request $request)
    {
        $request['no_pagination'] = true;
        $request['limit'] = 6;
        $cases_count = $this->caseRepo->getReport($request)->count();
        $cases_data = $this->caseRepo->getReport($request);
        // Amounts include tax (total_amount) because deposits are paid against tax-inclusive receipts.
        $total_deposit = $this->caseRepo->getSumArray('deposit', clone $cases_data);
        $total_amount = $this->caseRepo->getSumArray('total_amount', clone $cases_data);
        $total_remaining = $total_amount - $total_deposit;

        $services_count = $this->serviceRepo->getReport($request)->count();
        // Outstanding = what is still unpaid on open receipts (partially paid receipts count only their remainder).
        $totalUnPaidCaseReceipts = $this->receiptRepo->getAllUnPaidReceipts($request)->sum('unpaid_amount');
        $totalUnPaidServiceReceipts = $this->serviceReceiptRepo->getAllUnPaidReceipts($request)->sum('unpaid_amount');
        $cases = $this->caseRepo->get($request);
        $sessions = $this->sessionRepo->get($request)->count();
        $clients_count = $this->clientRepo->getReport($request)->count();
        $clients= $this->clientRepo->get($request);
        $appointments = $this->appointmentRepo->getAll($request)->count();
        $appointments_percentage = $this->appointmentRepo->getAppointmentPercentage($appointments,$clients_count);
        $attended_appointments = $this->appointmentRepo->getAllAttended($request)->count();
        $percentage_attended = $this->appointmentRepo->getAttendedPercentage($appointments,$attended_appointments);
        $remainder_sessions = $this->home_remainder($request)->count();
        $incomplete_amount = $totalUnPaidCaseReceipts + $totalUnPaidServiceReceipts;
        $cases_services_percentage = $this->getCasesAndServicesPercentage($cases_count + $services_count, $attended_appointments);

        return $this->apiResponseData(new HomeResource([
            'cases' => new CaseCollection($cases),
            'cases_count' => $cases_count + $services_count ,
            'clients' => new ClientCollection($clients),
            'clients_count' => $clients_count,
            'sessions' => $sessions,
            'appointments' => $appointments,
            'attended_appointments' => $attended_appointments,
            'appointments_percentage' => $appointments_percentage,
            'attended_percentage' => $percentage_attended,
            'remainder_sessions' => $remainder_sessions,
            'incomplete_amount' => $incomplete_amount,
            'total_deposit' => $total_deposit,
            'total_amount' => $total_amount,
            'total_remaining' => $total_remaining,
            'cases_percentage' => $cases_services_percentage,
        ]));
    }

    private function getCasesAndServicesPercentage($cases_count, $attended_appointments)
    {
        if ($attended_appointments === 0) {
            return 0;
        }
        $percentage = ($cases_count / $attended_appointments) * 100;
        return $percentage;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    private function home_remainder(Request $request)
    {
        $request['no_pagination'] = true;
        $request['remainder'] = 1;
        $sessions = $this->sessionRepo->get($request);
        return $sessions;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    private function incomplete_amount(Request $request)
    {
        $request['no_pagination'] = true;
        $request['incomplete_amount'] = true;
        $case_receipts = $this->receiptRepo->get($request);
        $incomplete_amount_sum = $case_receipts->sum('amount');
        return $incomplete_amount_sum;
    }

    /**
     * @param Request $request
     * @return array[]
     */
    public function general_search(Request $request)
    {
        $cases = $this->caseRepo->get($request);
        $services = $this->serviceRepo->get($request);
        $clients = $this->clientRepo->get($request);

        $data = [
            'cases' => new CaseCollection($cases),
            'services' => new ServiceCollection($services),
            'clients' => new ClientCollection($clients),
        ];

        return $this->apiResponseData($data, 'success', 200);
    }





}
