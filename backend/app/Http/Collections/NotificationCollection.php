<?php

namespace App\Http\Collections;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Auth;

class NotificationCollection extends ResourceCollection
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $unRead=Notification::where('admin_id',Auth::user()->id)->where('is_read',0)->count();
        $read=Notification::where('admin_id',Auth::user()->id)->where('is_read',1)->count();
        return [

            'data' =>NotificationResource::collection($this->collection),
            'pagination' => [
                'total' => $this->total(),
                'unReadCount' => $unRead,
                'read' => $read,
                'count' => $this->count(),
                'per_page' => (int)$this->perPage(),
                'current_page' => $this->currentPage(),
                'total_pages' => $this->lastPage(),
                'is_pagination' => $this->lastPage() <= $this->currentPage() ? false : true,
            ],


        ];
    }
}
