<?php

namespace App\Repos;

use App\Core\AppResult;
use App\Models\ContactReason;
use App\Models\PaymentMethod;
use Validator, Auth, Artisan, Hash, File, Crypt;

class ContactReasonRepo
{

    /**
     * @param $filter
     * @return mixed
     */
    public function get($filter)
    {
        $data = ContactReason::orderBy('id', 'desc');
        $limit = $filter->limit ? $filter->limit : 10;
        $data = $data->paginate($limit);
        return $data;
    }


    /**
     * @param $id
     * @return AppResult
     */
    public function getContactReasonById($id)
    {
        $contact_reason = ContactReason::findOrfail($id);
        return AppResult::success($contact_reason);
    }

    /**
     * @param $payload
     * @return ContactReason
     */
    public function create($payload)
    {
        $contact_reason = new ContactReason();
        $contact_reason->name = $payload->name;
        $contact_reason->save();
        return $contact_reason;
    }

    /**
     * @param $payload
     * @param $contact_reason
     * @return mixed
     */
    public function update($payload, $contact_reason)
    {
        $contact_reason->name = $payload->name;
        $contact_reason->save();
        return $contact_reason;
    }

    /**
     * @param $contact_reason
     * @return void
     */
    public function delete($contact_reason)
    {
        $contact_reason->delete();
    }


}
