<?php

namespace App\Http\Controllers\Admin;


use App\Http\Collections\TaskCollection;
use App\Http\Resources\TaskResource;
use App\Repos\LogRepo;
use App\Repos\NotificationRepo;
use App\Repos\TaskRepo;
use App\Validations\TaskValidation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Validator, Auth, Artisan, Hash, File, Mail;

class TaskController extends Controller
{
    use \App\Traits\ApiResponseTrait;

    private $taskRepo;
    private $taskValidation;

    public function __construct(TaskRepo $taskRepo
        , TaskValidation $taskValidation)
    {
        $this->taskRepo = $taskRepo;
        $this->taskValidation = $taskValidation;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function get(Request $request)
    {
        $admin = Auth::user();

        if ($admin->super != 1) {
            $request['admin_id'] = $admin->id;
            $request['created_by_id'] = $admin->id;
        }
        $tasks = $this->taskRepo->get($request);
        return $this->apiResponseData(new TaskCollection($tasks));
    }
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function single(Request $request)
    {
        $response = $this->taskRepo->getTaskById($request->task_id);
        $request['logs']=1;
        return $this->apiResponseData(new TaskResource($response->data));
    }
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        App::setLocale($request->header('lang'));
        $validateTask = $this->taskValidation->validate($request);
        if($validateTask->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateTask->error,200);
        }
        $data = $this->taskRepo->create($request);
        LogRepo::create($data,'task','add');
        NotificationMethods::senNotificationToSingleUser($data->admin,'مهمة جديدة','لديك مهمة جديدة',$data->id,'task');
        return $this->apiResponseData(new TaskResource($data));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $response = $this->taskRepo->getTaskById($request->task_id);
        $task=$response->data;
        $validateTask = $this->taskValidation->validate($request);
        if($validateTask->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateTask->error,200);
        }
        $data = $this->taskRepo->update($request,$task);
        LogRepo::create($data,'task','update');
        return $this->apiResponseData(new TaskResource($data));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function finish_task(Request $request)
    {
        $admin = Auth::user();
        $response = $this->taskRepo->getTaskById($request->task_id);
        $task = $response->data;
        if ($task->admin_id !== $admin->id)
            return $this->apiResponseMessage(0,__("responseMessage.this_task_not_belong_to you"));
        $data = $this->taskRepo->finishTask($task,$request);
        return $this->apiResponseData(new TaskResource($data));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        App::setLocale($request->header('lang'));
        $response = $this->taskRepo->getTaskById($request->task_id);
        $this->taskRepo->delete($response->data);
        return $this->apiResponseMessage(1,'deleted successfully');
    }

}
