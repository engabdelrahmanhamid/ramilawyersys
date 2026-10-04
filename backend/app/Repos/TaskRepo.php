<?php

namespace App\Repos;

use App\Core\AppResult;
use App\Helpers\TaskHelper;
use App\Models\Service;
use App\Models\Task;
use App\Models\UserCase;
use Validator, Auth, Artisan, Hash, File, Crypt;

class TaskRepo
{

    /**
     * @param $filter
     * @return mixed
     */
    public function get($filter)
    {
        $tasks = Task::orderBy('id', 'desc')->filter($filter);
        if($filter->export)
            return $tasks=$tasks->get();
        $limit = $filter->limit ? $filter->limit : 10;
        return $tasks->paginate($limit);
    }

    /**
     * @param $filter
     * @return mixed
     */
    public function getReport($filter){
        return Task::orderBy('id', 'desc')->filter($filter);
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
     * @return AppResult
     */
    public function getTaskById($id)
    {
        $task = Task::findOrfail($id);
        return AppResult::success($task);
    }


    /**
     * @param $payload
     * @return Task
     */
    public function create($payload)
    {
        $admin = Auth::user();
        $task = new Task();
        $task->model_type = $payload->model_type;
        $task->model_id = $payload->model_id;
        $task->end_date = $payload->end_date;
        $task->description = $payload->description;
        $task->task_type_id = $payload->task_type_id;
        $task->created_by_id = $admin->id;
        $task->admin_id = $payload->admin_id;
        $relatedModel = TaskHelper::getInstance()->getRelatedModel($payload->model_type, $payload->model_id);
        if($relatedModel){
            $task->client_id = $relatedModel->client_id;
        }
        $task->save();
        return $task;
    }

    /**
     * @param $payload
     * @param $case
     * @return mixed
     */
    public function update($payload, $task)
    {
        $task->model_type = $payload->model_type;
        $task->model_id = $payload->model_id;
        $task->end_date = $payload->end_date;
        $task->description = $payload->description;
        $task->status = $payload->status;
        $task->task_type_id = $payload->task_type_id;
        $task->admin_id = $payload->admin_id;
        $task->save();
        return $task;
    }

    /**
     * @param $task
     * @param $payload
     * @return mixed
     */
    public function finishTask($task,$payload)
    {
        $current_time = now();
        $finish_time = $task->dueAt();
        if (!$finish_time || $current_time <= $finish_time) {
            $task->status = 2;
        } else {
            $task->status = 3;
        }
        $task->comment = $payload->comment;
        $task->save();
        return $task;
    }
    /**
     * @param $case
     * @return void
     */
    public function delete($case)
    {
        $case->delete();
    }




}
