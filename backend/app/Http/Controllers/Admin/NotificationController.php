<?php

namespace App\Http\Controllers\Admin;


use App\Http\Collections\NotificationCollection;
use App\Http\Collections\TaskCollection;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\TaskResource;
use App\Models\Notification;
use App\Repos\NotificationRepo;
use App\Repos\TaskRepo;
use App\Validations\NotificationValidation;
use App\Validations\TaskValidation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Validator, Auth, Artisan, Hash, File, Mail;

class NotificationController extends Controller
{
    use \App\Traits\ApiResponseTrait;

    private $notificationRepo;
    private $notificationValidation;

    public function __construct(NotificationRepo $notificationRepo
        , NotificationValidation $notificationValidation)
    {
        $this->notificationRepo = $notificationRepo;
        $this->notificationValidation = $notificationValidation;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function get(Request $request)
    {
        $request['admin_id']=Auth::user()->id;
        $data = $this->notificationRepo->get($request);
        return $this->apiResponseData(new NotificationCollection($data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function single(Request $request)
    {
        $response = $this->notificationRepo->getNotificationById($request->notification_id);
        return $this->apiResponseData(new NotificationResource($response->data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function read(Request $request)
    {
        $admin = Auth::user();
        $response = $this->notificationRepo->getNotificationById($request->notification_id);
        $notification=$response->data;
        if ($notification->admin_id !== $admin->id)
            return $this->apiResponseMessage(0,__("responseMessage.this_task_not_belong_to you"));
        $data = $this->notificationRepo->read($notification);
        return $this->apiResponseData(new NotificationResource($data));
    }

}
