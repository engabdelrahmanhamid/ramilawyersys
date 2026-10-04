<?php

namespace App\Repos;

use App\Core\AppResult;
use App\Helpers\PriceHelper;
use App\Models\CaseReceipt;
use App\Models\Receipt;
use App\Models\ServiceReceipt;
use Validator,Auth,Artisan,Hash,File,Crypt;

class ServiceReceiptRepo{

    use \App\Traits\ApiResponseTrait;

    /**
     * @param $filter
     * @return mixed
     */
    public function get($filter){
        $data=ServiceReceipt::orderBy('id','desc')->filter($filter);
        if($filter->export)
            return $data=$data->get();
        $limit = $filter->limit ? $filter->limit : 10;
        return $data->paginate($limit);
    }


    /**
     * @param $filter
     * @return mixed
     */
    public function getReport($filter){
        return ServiceReceipt::orderBy('id', 'desc')->filter($filter);
    }

    /**
     * @param $filter
     * @return mixed
     */
    public function getAllUnPaidReceipts($filter){
        return ServiceReceipt::orderBy('id', 'desc')->filter($filter)->where('payment_status',0);
    }

    /**
     * @param $column
     * @param $data
     * @return mixed
     */
    public function getSumArray($column,$data){
        return $data->sum($column);
    }



    /**
     * @param $id
     * @return mixed
     */
    public function single($id){
        return ServiceReceipt::findOrFail($id);
    }

    /**
     * @param $payload
     * @param $service
     * @return void
     */
    public function save_service_receipts($payload, $service)
    {
        foreach ($payload['makeServiceReceipts'] as $index => $row) {
            $this->create($row, $service);
        }
    }

    /**
     * @param $row
     * @param $service
     * @return ServiceReceipt
     */
    public function create($row, $service)
    {
        $settingRepo = new SettingRepo();
        $service_receipt = new ServiceReceipt();
        $service_receipt->service_id = $service->id;
        $service_receipt->date = $row['date'];
        $service_receipt->amount = $row['amount'];
        $service_receipt->tax_amount = $this->calculateTaxPrice($row['amount'], $service->tax);
        $service_receipt->total_amount = $service_receipt->amount + $service_receipt->tax_amount;
        $service_receipt->unpaid_amount = $service_receipt->total_amount;
        $service_receipt->receipt_number = $settingRepo->getFirstSetting()->current_service_number + $settingRepo->getFirstSetting()->increase_amount_service_receipt;
        $service_receipt->save();
        $settingRepo->current_service_number();
        return $service_receipt;
    }

    /**
     * @param $row
     * @param $service
     * @return ServiceReceipt
     */
    public function createReceipt($row, $service)
    {
        $settingRepo=new SettingRepo();
        $service->payment_status = 1;
        $service->save();
        $service_receipt = new ServiceReceipt();
        $service_receipt->service_id = $service->id;
        $service_receipt->date = $service->created_at;
        $service_receipt->amount = $service->amount;
        $service_receipt->tax_amount = $service->tax_amount;
        $service_receipt->total_amount = $service->total_amount;
        $service_receipt->paid_amount = $service_receipt->total_amount;
        $service_receipt->unpaid_amount = 0;
        $service_receipt->payment_status = 1;
        $service_receipt->receipt_number = $settingRepo->getFirstSetting()->current_service_number + $settingRepo->getFirstSetting()->increase_amount_service_receipt;
        $service_receipt->save();
        $settingRepo->current_case_number();
        return $service_receipt;
    }

    /**
     * @param $amount
     * @param $tax
     * @return float|int
     */
    private function calculateTaxPrice($amount , $tax)
    {
        $tax_price = PriceHelper::getInstance()->calDiscountPrice($amount , $tax , 2);
        return $tax_price;
    }

    /**
     * @param $payload
     * @return int|mixed
     */
    public function checkServiceReceiptValue($payload)
    {
        $serviceReceiptsAmount = 0;
        foreach ($payload->makeServiceReceipts as $serviceReceipt) {
            $serviceReceiptsAmount += $serviceReceipt['amount'];
        }
        return $serviceReceiptsAmount;
    }

    /**
     * @param $id
     * @return AppResult
     */
    public function getServiceReceiptById($id)
    {
        $service_receipt = ServiceReceipt::findOrfail($id);
        return AppResult::success($service_receipt);
    }

    /**
     * @param $service_receipt
     * @return void
     */
    public function changeReceiptPaymentStatus($service_receipt,$payload)
    {
        $service_receipt->payment_status = $service_receipt->paid_amount == $service_receipt->total_amount ? 1 : 0;
        $service_receipt->payment_method_id=$payload->payment_method_id;
        $service_receipt->paid_to=$payload->paid_to;
        $service_receipt->paid_at=now();
        $service_receipt->save();
    }

    /**
     * @param $service_receipt
     * @param $payload
     * @return void
     */
    public function cal_paid_and_unpaid_amount($service_receipt, $payload)
    {
        $service_receipt->paid_amount += $payload->pay_amount;
        $service_receipt->unpaid_amount -= $payload->pay_amount;
        $service_receipt->save();
    }

//    /**
//     * @param $service_receipt
//     * @param $payload
//     */
//    public function makeServiceReceiptPaid($service_receipt,$payload){
//        $service_receipt->payment_status=1;
//        $service_receipt->payment_method_id=$payload->payment_method_id;
//        $service_receipt->save();
//    }




}
