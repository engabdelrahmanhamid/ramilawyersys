<?php

namespace App\Repos;

use App\Core\AppResult;
use App\Models\PaymentMethod;
use Validator, Auth, Artisan, Hash, File, Crypt;

class PaymentMethodRepo
{

    /**
     * @param $filter
     * @return mixed
     */
    public function get($filter)
    {
        $payment_methods = PaymentMethod::orderBy('id', 'desc');
        $limit = $filter->limit ? $filter->limit : 10;
        $payment_methods = $payment_methods->paginate($limit);
        return $payment_methods;
    }


    /**
     * @param $id
     * @return AppResult
     */
    public function getPaymentMethodById($id)
    {
        $payment_method = PaymentMethod::findOrfail($id);
        return AppResult::success($payment_method);
    }

    /**
     * @param $payload
     * @return PaymentMethod
     */
    public function create($payload)
    {
        $payment_method = new PaymentMethod();
        $payment_method->name = $payload->name;
        $payment_method->save();
        return $payment_method;
    }

    /**
     * @param $payload
     * @param $payment_method
     * @return mixed
     */
    public function update($payload, $payment_method)
    {
        $payment_method->name = $payload->name;
        $payment_method->save();
        return $payment_method;
    }

    /**
     * @param $payment_method
     * @return void
     */
    public function delete($payment_method)
    {
        $payment_method->delete();
    }


}
