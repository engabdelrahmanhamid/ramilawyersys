<?php

namespace App\Repos;

use App\Core\AppResult;
use App\Helpers\PriceHelper;
use App\Models\CaseReceipt;
use App\Models\UserCase;
use Validator,Auth,Artisan,Hash,File,Crypt;

class ReceiptRepo{

    use \App\Traits\ApiResponseTrait;

    protected $settingRepo;

    public function __construct(SettingRepo $settingRepo)
    {
        $this->settingRepo = $settingRepo;
    }
    /**
     * @param $filter
     * @return mixed
     */
    public function get($filter){
        $data=CaseReceipt::orderBy('id','desc')->filter($filter);
        if($filter->export)
            return $data=$data->get();
        if (isset($filter->no_pagination) && $filter->no_pagination == true) {
            return $data->get();
        } else {
            $limit = $filter->limit ? $filter->limit : 10;
            return $data->paginate($limit);
        }
    }

    /**
     * @param $filter
     * @return mixed
     */

    public function getReport($filter){
        return CaseReceipt::orderBy('id', 'desc')->filter($filter);
    }

    public function getAllUnPaidReceipts($filter){
        return CaseReceipt::orderBy('id', 'desc')->filter($filter)->where('payment_status',0);
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
        return CaseReceipt::findOrFail($id);
    }

    /**
     * @param $payload
     * @param $case
     */
    public function save_receipts($payload, $case)
    {
        foreach ($payload['makeReceipts'] as $index => $row) {
            $this->create($row, $case);
        }
    }

    /**
     * @param $row
     * @param $case_id
     * @return CaseReceipt
     */
    public function create($row, $case)
    {
        $settingRepo=new SettingRepo();
        $receipt = new CaseReceipt();
        $receipt->case_id = $case->id;
        $receipt->date = $row['date'];
        $receipt->amount = $row['amount'];
        $receipt->tax_amount = $this->calculateTaxPrice($row['amount'], $case->tax);
        $receipt->total_amount = $receipt->amount + $receipt->tax_amount;
        $receipt->unpaid_amount = $receipt->total_amount;
        $receipt->receipt_number = $settingRepo->getFirstSetting()->current_case_number + $settingRepo->getFirstSetting()->increase_amount_case_receipt;
        $receipt->save();
        $settingRepo->current_case_number();
        return $receipt;
    }

    /**
     * @param $row
     * @param $case
     * @return CaseReceipt
     */
    public function createReceipt($row, $case)
    {
        $settingRepo=new SettingRepo();
        $receipt = new CaseReceipt();
        $receipt->case_id = $case->id;
        $receipt->date = $case->created_at;
        $receipt->amount = $case->amount;
        $receipt->tax_amount = $case->tax_amount;
        $receipt->total_amount = $case->total_amount;
        $receipt->paid_amount = $receipt->total_amount;
        $receipt->unpaid_amount = 0;
        $receipt->payment_status = 1;
        $receipt->receipt_number = $settingRepo->getFirstSetting()->current_case_number + $settingRepo->getFirstSetting()->increase_amount_case_receipt;
        $receipt->save();
        $settingRepo->current_case_number();
        $case->deposit = $case->total_amount;
        $case->payment_status = 1;
        $case->save();
        return $receipt;
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
     * @param $amount
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|void
     */
    public function checkReceiptValue($payload)
    {
        $receiptsAmount = 0;
        foreach ($payload->makeReceipts as $receipt) {
            $receiptsAmount += $receipt['amount'];
        }
        return $receiptsAmount;
    }

    /**
     * @param $id
     * @return AppResult
     */
    public function getReceiptById($id)
    {
        $receipt = CaseReceipt::findOrfail($id);
        return AppResult::success($receipt);
    }

    public function changeReceiptPaymentStatus($receipt,$payload)
    {
        $receipt->payment_status = round($receipt->paid_amount, 2) >= round($receipt->total_amount, 2) ? 1 : 0;
        $receipt->payment_method_id=$payload->payment_method_id;
        $receipt->paid_to=$payload->paid_to;
        $receipt->paid_at=now();
        $receipt->save();
    }

    /**
     * @param $receipt
     * @param $payload
     * @return void
     */
    public function cal_paid_and_unpaid_amount($receipt, $payload)
    {
        $receipt->paid_amount += $payload->pay_amount;
        $receipt->unpaid_amount -= $payload->pay_amount;
        $receipt->save();
    }



    /**
     * @param $receipt
     * @param $payload
     */
    public function makeReceiptPaid($receipt,$payload){
        $receipt->payment_status=1;
        $receipt->payment_method_id=$payload->payment_method_id;
        $receipt->save();
    }






}
