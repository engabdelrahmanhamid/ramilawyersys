<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class HomeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'name' =>  'رامي الحامد',
            'cases' => $this->resource['cases'],
            'cases_count' => $this->resource['cases_count'],
            'clients' => $this->resource['clients'],
            'clients_count' => $this->resource['clients_count'],
            'sessions' => $this->resource['sessions'],
            'appointments' => $this->resource['appointments'],
            'attended_appointments' => $this->resource['attended_appointments'],
            'appointments_percentage' => $this->resource['appointments_percentage'],
            'attended_percentage' => $this->resource['attended_percentage'],
            'remainder_sessions' => $this->resource['remainder_sessions'],
            'incomplete_amount' => $this->resource['incomplete_amount'],
            'cases_percentage' => $this->resource['cases_percentage'],
            'deposit' => $this->resource['total_deposit'],
            'allMoney' => $this->resource['total_amount'],
            'remaining' => $this->resource['total_remaining'],
        ];
    }
}
