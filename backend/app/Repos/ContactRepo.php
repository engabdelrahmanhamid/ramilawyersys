<?php

namespace App\Repos;

use App\Core\AppResult;
use App\Models\Appointment;
use App\Models\City;
use App\Models\Contact;
use App\Models\Replay;
use Carbon\Carbon;
use Validator, Auth, Artisan, Hash, File, Crypt;

class ContactRepo
{

    /**
     * @param $filter
     * @return mixed
     */
    public function get($filter)
    {
        $data = Contact::orderBy('id', 'desc');
        $limit = $filter->limit ? $filter->limit : 10;
        if ($filter->search_text) {
            $data->where(function ($query) use ($filter) {
                $query->whereHas('client', function ($query) use ($filter) {
                    $query->where('name', 'LIKE', '%' . $filter->search_text . '%')
                        ->orWhere('phone', 'LIKE', '%' . $filter->search_text . '%');
                })
                    ->orWhereHas('admin', function ($query) use ($filter) {
                        $query->where('name', 'LIKE', '%' . $filter->search_text . '%');
                    });
            });
        }
        if ($filter->branch_id) {
            $data = $data->where(function ($query) use ($filter) {
                $query->whereHas('admin', function ($q) use ($filter) {
                    $q->where('branch_id', $filter->branch_id);
                });
            });
        }
        if($filter->client_id)
            $data=$data->where('client_id',$filter->client_id);
        if($filter->admin_id)
            $data=$data->where('admin_id',$filter->admin_id);
        if($filter->method)
            $data=$data->where('method',$filter->method);
        if($filter->contact_reason_id)
            $data=$data->where('contact_reason_id',$filter->contact_reason_id);
        if($filter->contact_type_id)
            $data=$data->where('contact_type_id',$filter->contact_type_id);
        if ($filter->month) {
            $data = $data->whereMonth('date', $filter->month);
        }
        if ($filter->date_from) {
            $data = $data->whereDate('date', '>=', $filter->date_from);
        }
        if ($filter->date_to) {
            $data = $data->whereDate('date', '<=', $filter->date_to);
        }
        if($filter->export)
            return $data=$data->get();
        $data = $data->paginate($limit);
        return $data;
    }

    /**
     * @param $id
     * @return AppResult
     */
    public function getContactById($id)
    {
        $contact = Contact::findOrfail($id);
        return AppResult::success($contact);
    }

    /**
     * @param $payload
     * @return Contact
     */
    public function create($payload)
    {
        $admin = Auth::user();
        $contact = new Contact();
        $contact->date = isset($payload->date) ? $payload->date : Carbon::now();
        $contact->method = $payload->method;
        $contact->description = $payload->description;
        $contact->contact_reason_id = $payload->contact_reason_id;
        $contact->contact_type_id = $payload->contact_type_id;
        $contact->client_id = $payload->client_id;
        $contact->createdBy_id = $admin->id;
        $contact->admin_id = $payload->admin_id;
        $contact->save();
        return $contact;
    }

    /**
     * @param $payload
     *
     * @param $contact
     * @return mixed
     */
    public function update($payload, $contact)
    {
        if (isset($payload->date))
            $contact->date = $payload->date;
        if (isset($payload->method))
            $contact->method = $payload->method;
        if (isset($payload->description))
            $contact->description = $payload->description;
        if (isset($payload->contact_reason_id))
            $contact->contact_reason_id = $payload->contact_reason_id;
        if (isset($payload->contact_type_id))
            $contact->contact_type_id = $payload->contact_type_id;
        if (isset($payload->client_id))
            $contact->client_id = $payload->client_id;
        if (isset($payload->admin_id))
            $contact->admin_id = $payload->admin_id;
        $contact->save();
        return $contact;
    }


    /**
     * @param $contact
     * @return void
     */
    public function delete($contact)
    {
        $contact->delete();
    }

}
