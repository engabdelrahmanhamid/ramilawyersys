<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ReportHelper;
use App\Http\Collections\CaseCollection;
use App\Http\Collections\CaseReceiptCollection;
use App\Http\Collections\CityCollection;
use App\Http\Collections\ClientCollection;
use App\Http\Collections\ServiceCollection;
use App\Http\Collections\ServiceReceiptCollection;
use App\Http\Collections\SessionCollection;
use App\Http\Collections\TaskCollection;
use App\Http\Resources\CityResource;
use App\Repos\CaseRepo;
use App\Repos\CityRepo;
use App\Repos\ClientRepo;
use App\Repos\ReceiptRepo;
use App\Repos\ServiceReceiptRepo;
use App\Repos\ServiceRepo;
use App\Repos\SessionRepo;
use App\Repos\TaskRepo;
use App\Validations\CityValidation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Validator, Auth, Artisan, Hash, File, Mail;

class ReportController extends Controller
{
    use \App\Traits\ApiResponseTrait;

    public function __construct()
    {

    }

    /**
     * @param Request $request
     * @param CaseRepo $caseRepo
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function cases(Request $request, CaseRepo $caseRepo)
    {
        $cases = $caseRepo->get($request);
        $request->year = $request->year ? $request->year : date("Y");
        $count_array = [];
        $amount_array = [];
        $deposit_array = [];
        $remaining_array = [];
        $x = [];
        $values = [];
        $array = $request->month ? ReportHelper::getInstance()->monthly() : ReportHelper::getInstance()->yearly();
        $title = $request->month ? 'تقرير شهري' : 'تقرير سنوي';
        $type = $request->month ? 1 : 2;
        foreach ($array as $key => $row) {
            if ($type == 1) {
                $request['date_from'] = $request->year . '-' . $request->month . '-' . $row['start'];
                $request['date_to'] = $request->year . '-' . $request->month . '-' . $row['end'];
            } else {
                $request['month'] = '0' . $key;
            }
            $x[] = $row['title'];
            $data = $caseRepo->getReport($request);
            $values[] = $data->count();
            $count_array[] = $data->count();
            $deposit_array[] = $caseRepo->getSumArray('deposit', $data);
            $amount_array[] = $caseRepo->getSumArray('amount', $data);
            $remaining_array[] = $caseRepo->getSumArray('amount', $data) - $caseRepo->getSumArray('deposit', $data);
        }
        $sum = [array_sum($values), $count_array];
        $total_deposit = [array_sum($deposit_array), $deposit_array];
        $total_amount = [array_sum($amount_array), $amount_array];
        $total_remaining = [array_sum($remaining_array), $remaining_array];
        $data =
            [
            'reports' =>
                ['x' => $x, 'y' => $values,
                'title' => $title, 'all_cases' => $sum, 'total_amount' => $total_amount, 'total_deposit' => $total_deposit,
                'total_remaining' => $total_remaining],
            'cases' => new CaseCollection($cases),
        ];
        return $this->apiResponseData($data);
    }

    /**
     * @param Request $request
     * @param ClientRepo $clientRepo
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function clients(Request $request, ClientRepo $clientRepo)
    {
        $clients = $clientRepo->get($request);
        $request->year = $request->year ? $request->year : date("Y");;
        $x = [];
        $values = [];
        $clients_array = [];
        $company_array = [];
        $individual_array = [];
        $array = $request->month ? ReportHelper::getInstance()->monthly() : ReportHelper::getInstance()->yearly();
        $title = $request->month ? 'تقرير شهري' : 'تقرير سنوي';
        $type = $request->month ? 1 : 2;
            foreach ($array as $key => $row) {
                if ($type == 1) {
                    $request['date_from'] = $request->year . '-' . $request->month . '-' . $row['start'];
                    $request['date_to'] = $request->year . '-' . $request->month . '-' . $row['end'];
                } else {
                    $request['month'] = '0' . $key;
                }
                $request['type_id']=null;
                $data = $clientRepo->getReport($request);
                $x[] = $row['title'];
                $values[] = $data->count();
                $clients_array[] = $data->count();
                $request['type_id']=14;
                $individual=$clientRepo->getReport($request);
                $individual_array[] =$individual->count();
                $request['type_id']=15;
                $company=$clientRepo->getReport($request);
                $company_array[] = $company->count();
            }
        $all_clients = [array_sum($values), $clients_array];
        $company_values = [array_sum($company_array), $company_array];
        $individual_value = [array_sum($individual_array), $individual_array];
        $data = [
            'reports' => ['x' => $x, 'y' => $values, 'title' => $title, 'all_clients' => $all_clients,
                'company_values'=>$company_values,'individual_value'=>$individual_value],
            'clients' => new ClientCollection($clients),
        ];
        return $this->apiResponseData($data);
    }


    /**
     * @param Request $request
     * @param ServiceRepo $serviceRepo
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function services(Request $request, ServiceRepo $serviceRepo)
    {
        $services = $serviceRepo->get($request);
        $request->year = $request->year ? $request->year : date("Y");
        $count_array = [];
        $amount_array = [];
        $deposit_array = [];
        $remaining_array = [];
        $x = [];
        $values = [];
        $array = $request->month ? ReportHelper::getInstance()->monthly() : ReportHelper::getInstance()->yearly();
        $title = $request->month ? 'تقرير شهري' : 'تقرير سنوي';
        $type = $request->month ? 1 : 2;
        foreach ($array as $key => $row) {
            if ($type == 1) {
                $request['date_from'] = $request->year . '-' . $request->month . '-' . $row['start'];
                $request['date_to'] = $request->year . '-' . $request->month . '-' . $row['end'];
            } else {
                $request['month'] = '0' . $key;
            }
            $x[] = $row['title'];
            $data = $serviceRepo->getReport($request);
            $values[] = $data->count();
            $count_array[] = $data->count();
            $deposit_array[] = $serviceRepo->getSumArray('deposit', $data);
            $amount_array[] = $serviceRepo->getSumArray('amount', $data);
            $remaining_array[] = $serviceRepo->getSumArray('amount', $data) - $serviceRepo->getSumArray('deposit', $data);

        }


        $sum = [array_sum($values), $count_array];
        $total_deposit = [array_sum($deposit_array), $deposit_array];
        $total_amount = [array_sum($amount_array), $amount_array];
        $total_remaining = [array_sum($remaining_array), $remaining_array];
        $data =
            [
                'reports' =>
                    ['x' => $x, 'y' => $values,
                        'title' => $title, 'all_services' => $sum, 'total_amount' => $total_amount, 'total_deposit' => $total_deposit,
                        'total_remaining' => $total_remaining],
                'services' => new ServiceCollection($services),
            ];
        return $this->apiResponseData($data);
    }

    /**
     * @param Request $request
     * @param TaskRepo $taskRepo
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function tasks(Request $request, TaskRepo $taskRepo)
    {
        $tasks = $taskRepo->get($request);
        $request->year = $request->year ? $request->year : date("Y");;
        $count_array = [];
        $new_tasks_array = [];
        $ended_tasks_array = [];
        $ended_tasks_after_date_array = [];
        $x = [];
        $values = [];
        $array = $request->month ? ReportHelper::getInstance()->monthly() : ReportHelper::getInstance()->yearly();
        $title = $request->month ? 'تقرير شهري' : 'تقرير سنوي';
        $type = $request->month ? 1 : 2;
        foreach ($array as $key => $row) {
            if ($type == 1) {
                $request['date_from'] = $request->year . '-' . $request->month . '-' . $row['start'];
                $request['date_to'] = $request->year . '-' . $request->month . '-' . $row['end'];
            } else {
                $request['month'] = '0' . $key;
            }
            $x[] = $row['title'];
            $data = $taskRepo->getReport($request);
            $values[] = $data->count();
            $count_array[] = $data->count();
            $new_tasks_array[] = (clone $data)->where('status', 1)->count();
            $ended_tasks_array[] = (clone $data)->where('status', 2)->count();
            $ended_tasks_after_date_array[] = (clone $data)->where('status', 3)->count();

        }
        $sum = [array_sum($values), $count_array];
        $total_new_tasks = [array_sum($new_tasks_array), $new_tasks_array];
        $total_ended_tasks = [array_sum($ended_tasks_array), $ended_tasks_array];
        $total_tasks_after_date = [array_sum($ended_tasks_after_date_array), $ended_tasks_after_date_array];
        $data =
            [
                'reports' =>
                    ['x' => $x, 'y' => $values,
                        'title' => $title, 'all_tasks' => $sum, 'total_new_tasks' => $total_new_tasks, 'total_ended_tasks' => $total_ended_tasks,
                        'total_tasks_after_date' => $total_tasks_after_date],
                'tasks' => new TaskCollection($tasks),
            ];
        return $this->apiResponseData($data);
    }

    /**
     * @param Request $request
     * @param SessionRepo $sessionRepo
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function sessions(Request $request, SessionRepo $sessionRepo)
    {
        $sessions = $sessionRepo->get($request);
        $request->year = $request->year ? $request->year : date("Y");;
        $count_array = [];
        $sessions_array = [];
        $revisions_array = [];
        $x = [];
        $values = [];
        $array = $request->month ? ReportHelper::getInstance()->monthly() : ReportHelper::getInstance()->yearly();
        $title = $request->month ? 'تقرير شهري' : 'تقرير سنوي';
        $type = $request->month ? 1 : 2;
        foreach ($array as $key => $row) {
            if ($type == 1) {
                $request['date_from'] = $request->year . '-' . $request->month . '-' . $row['start'];
                $request['date_to'] = $request->year . '-' . $request->month . '-' . $row['end'];
            } else {
                $request['month'] = '0' . $key;
            }
            $x[] = $row['title'];
            $data = $sessionRepo->getReport($request);
            $values[] = $data->count();
            $count_array[] = $data->count();
            $sessions_array[] = (clone $data)->where('type', 1)->count();
            $revisions_array[] = (clone $data)->where('type', 2)->count();

        }
        $sum = [array_sum($values), $count_array];
        $total_sessions = [array_sum($sessions_array), $sessions_array];
        $total_revisions = [array_sum($revisions_array), $revisions_array];
        $data =
            [
                'reports' =>
                    ['x' => $x, 'y' => $values,
                        'title' => $title, 'all_sessions_and_revisions' => $sum, 'total_sessions' => $total_sessions, 'total_revisions' => $total_revisions],
                'sessions_and_revisions' => new SessionCollection($sessions),
            ];
        return $this->apiResponseData($data);
    }

    /**
     * @param Request $request
     * @param ReceiptRepo $receiptRepo
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function case_receipts(Request $request, ReceiptRepo $receiptRepo)
    {
        $case_receipts = $receiptRepo->get($request);
        $request->year = $request->year ? $request->year : date("Y");;
        $count_array = [];
        $amount_array = [];
        $tax_amount_array = [];
        $total_amount_array = [];
        $paid_amount_array = [];
        $unpaid_amount_array = [];
        $x = [];
        $values = [];
        $array = $request->month ? ReportHelper::getInstance()->monthly() : ReportHelper::getInstance()->yearly();
        $title = $request->month ? 'تقرير شهري' : 'تقرير سنوي';
        $type = $request->month ? 1 : 2;
        foreach ($array as $key => $row) {
            if ($type == 1) {
                $request['date_from'] = $request->year . '-' . $request->month . '-' . $row['start'];
                $request['date_to'] = $request->year . '-' . $request->month . '-' . $row['end'];
            } else {
                $request['month'] = '0' . $key;
            }
            $x[] = $row['title'];
            $data = $receiptRepo->getReport($request);
            $values[] = $data->count();
            $count_array[] = $data->count();
            $amount_array[] = $receiptRepo->getSumArray('amount', $data);
            $tax_amount_array[] = $receiptRepo->getSumArray('tax_amount', $data);
            $total_amount_array[] = $receiptRepo->getSumArray('total_amount', $data);
            $paid_amount_array[] = $receiptRepo->getSumArray('paid_amount', $data);
            $unpaid_amount_array[] = $receiptRepo->getSumArray('unpaid_amount', $data);
        }
        $sum = [array_sum($values), $count_array];
        $total_amount = [array_sum($amount_array), $amount_array];
        $total_tax_amount = [array_sum($tax_amount_array), $tax_amount_array];
        $sum_total_amount = [array_sum($total_amount_array), $total_amount_array];
        $total_paid_amount = [array_sum($paid_amount_array), $paid_amount_array];
        $total_unpaid_amount = [array_sum($unpaid_amount_array), $unpaid_amount_array];

        $data =
            [
                'reports' =>
                    ['x' => $x, 'y' => $values,
                        'title' => $title, 'all_receipts' => $sum, 'total_amount' => $total_amount, 'total_tax_amount' => $total_tax_amount,
                        'sum_total_amount' => $sum_total_amount , 'total_paid_amount'=>$total_paid_amount, 'total_unpaid_amount'=>$total_unpaid_amount,],
                'case_receipts' => new CaseReceiptCollection($case_receipts),
            ];
        return $this->apiResponseData($data);
    }

    /**
     * @param Request $request
     * @param ServiceReceiptRepo $serviceReceiptRepo
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function service_receipts(Request $request, ServiceReceiptRepo $serviceReceiptRepo)
    {
        $service_receipts = $serviceReceiptRepo->get($request);
        $request->year = $request->year ? $request->year : date("Y");;
        $count_array = [];
        $amount_array = [];
        $tax_amount_array = [];
        $total_amount_array = [];
        $paid_amount_array = [];
        $unpaid_amount_array = [];
        $x = [];
        $values = [];
        $array = $request->month ? ReportHelper::getInstance()->monthly() : ReportHelper::getInstance()->yearly();
        $title = $request->month ? 'تقرير شهري' : 'تقرير سنوي';
        $type = $request->month ? 1 : 2;
        foreach ($array as $key => $row) {
            if ($type == 1) {
                $request['date_from'] = $request->year . '-' . $request->month . '-' . $row['start'];
                $request['date_to'] = $request->year . '-' . $request->month . '-' . $row['end'];
            } else {
                $request['month'] = '0' . $key;
            }
            $x[] = $row['title'];
            $data = $serviceReceiptRepo->getReport($request);
            $values[] = $data->count();
            $count_array[] = $data->count();
            $amount_array[] = $serviceReceiptRepo->getSumArray('amount', $data);
            $tax_amount_array[] = $serviceReceiptRepo->getSumArray('tax_amount', $data);
            $total_amount_array[] = $serviceReceiptRepo->getSumArray('total_amount', $data);
            $paid_amount_array[] = $serviceReceiptRepo->getSumArray('paid_amount', $data);
            $unpaid_amount_array[] = $serviceReceiptRepo->getSumArray('unpaid_amount', $data);
        }
        $sum = [array_sum($values), $count_array];
        $total_amount = [array_sum($amount_array), $amount_array];
        $total_tax_amount = [array_sum($tax_amount_array), $tax_amount_array];
        $sum_total_amount = [array_sum($total_amount_array), $total_amount_array];
        $total_paid_amount = [array_sum($paid_amount_array), $paid_amount_array];
        $total_unpaid_amount = [array_sum($unpaid_amount_array), $unpaid_amount_array];

        $data =
            [
                'reports' =>
                    ['x' => $x, 'y' => $values,
                        'title' => $title, 'all_service_receipts' => $sum, 'total_amount' => $total_amount, 'total_tax_amount' => $total_tax_amount,
                        'sum_total_amount' => $sum_total_amount , 'total_paid_amount'=>$total_paid_amount, 'total_unpaid_amount'=>$total_unpaid_amount,],
                'case_receipts' => new ServiceReceiptCollection($service_receipts),
            ];
        return $this->apiResponseData($data);
    }

}
