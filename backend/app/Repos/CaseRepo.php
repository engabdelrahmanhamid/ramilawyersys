<?php

namespace App\Repos;

use App\Core\AppResult;
use App\Helpers\DateHelper;
use App\Helpers\PriceHelper;
use App\Models\UserCase;
use Validator, Auth, Artisan, Hash, File, Crypt;

class CaseRepo
{

    /**
     * @param $filter
     * @return mixed
     */
    public function get($filter)
    {
        $cases = UserCase::orderBy('id', 'desc')->filter($filter);
        if($filter->export)
            return $cases=$cases->get();
        $limit = $filter->limit ? $filter->limit : 10;
        return $cases->paginate($limit);
    }

    /**
     * @param $filter
     * @return mixed
     */
    public function getReport($filter){
        return UserCase::orderBy('id', 'desc')->filter($filter);
    }

    public function getSumArray($column,$data){
        return $data->sum($column);
    }

    /**
     * @param $id
     * @return AppResult
     */
    public function getCaseById($id)
    {
        $case = UserCase::findOrfail($id);
        return AppResult::success($case);
    }

    /**
     * @param $payload
     * @return UserCase
     */
    public function create($payload)
    {
        $case = new UserCase();
        $case->name = $payload->name;
        $case->address = $payload->address;
        $case->number = $payload->number;
        $case->gregorian_date = $payload->gregorian_date;
        $case->hijri_date = DateHelper::getInstance()->gregorianToHijri($payload->gregorian_date);
        $case->desc = $payload->desc;
        $case->court_name = $payload->court_name;
        $case->court_city = $payload->court_city;
        $case->court_circle = $payload->court_circle;
        $case->court_degree = $payload->court_degree;
        $case->opponent_name = $payload->opponent_name;
        $case->client_characteristic = $payload->client_characteristic;
        $case->payment_type = $payload->payment_type;
        $case->payment_status = $payload->payment_type == 1 ? 1 : 0;
        $case->payment_method_id = $payload->payment_method_id;
        $case->amount = $payload->amount;
        $case->tax = $payload->tax;
        $case->tax_amount = $this->calculateTaxPrice($payload->amount , $payload->tax);
        $case->total_amount = $case->amount + $case->tax_amount;
        $case->opponent_type_id = $payload->opponent_type_id;
        $case->case_type_id = $payload->case_type_id;
        $case->case_status_id = $payload->case_status_id;
        $case->client_id = $payload->client_id;
        $case->branch_id = $payload->branch_id;
        $case->admin_id = $payload->admin_id;
        $case->save();
        return $case;
    }

    /**
     * @param $payload
     * @param $case
     * @return mixed
     */
    public function update($payload, $case)
    {
        $case->name = $payload->name;
        $case->address = $payload->address;
        $case->number = $payload->number;
        $case->gregorian_date = $payload->gregorian_date;
        $case->hijri_date = DateHelper::getInstance()->gregorianToHijri($payload->gregorian_date);
        $case->desc = $payload->desc;
        $case->court_name = $payload->court_name;
        $case->court_city = $payload->court_city;
        $case->court_circle = $payload->court_circle;
        $case->court_degree = $payload->court_degree;
        $case->opponent_name = $payload->opponent_name;
        $case->client_characteristic = $payload->client_characteristic;
        $case->payment_type = $payload->payment_type;
        $case->payment_method_id = $payload->payment_method_id;
        $case->amount = $payload->amount;
        $case->tax = $payload->tax;
        if(isset($payload->amount) || isset($payload->tax)){
            $case->tax_amount = $this->calculateTaxPrice($case->amount , $case->tax);
            $case->total_amount =$case->amount + $case->tax_amount;
        }
        $case->opponent_type_id = $payload->opponent_type_id;
        $case->case_type_id = $payload->case_type_id;
        $case->case_status_id = $payload->case_status_id;
        $case->client_id = $payload->client_id;
        $case->branch_id = $payload->branch_id;
        $case->admin_id = $payload->admin_id;
        $case->save();
        return $case;
    }

    /**
     * @param $amount
     * @param $tax
     * @return float|int
     */
    private function calculateTaxPrice($amount , $tax)
    {
        return PriceHelper::getInstance()->calDiscountPrice($amount , $tax , 2);
    }


    /**
     * @param $payload
     * @param $case
     * @return mixed
     */
    public function changeStatus($payload, $case)
    {
        $case->case_status_id = $payload->case_status_id;
        $case->save();
        return $case;
    }


    /**
     * @param $case
     * @return void
     */
    public function delete($case)
    {
        $case->delete();
    }


    /**
     * @param $case
     * @return void
     */
    public function changePaymentStatus($case)
    {
        $case->payment_status = round($case->deposit, 2) >= round($case->total_amount, 2) ? 1 : 0;
        $case->save();
    }

    /**
     * @param $case
     * @param $amount
     */
    public function changeDeposit($case, $payload)
    {
        $case->deposit += $payload->pay_amount;
        $case->save();
    }


}
