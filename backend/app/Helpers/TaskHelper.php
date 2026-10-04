<?php

namespace App\Helpers;

use App\Http\Collections\CaseReceiptCollection;
use App\Http\Resources\CaseResource;
use App\Http\Resources\ClientResource;
use App\Http\Resources\ServiceResource;
use App\Http\Resources\SessionResource;
use App\Models\Client;
use App\Models\Service;
use App\Models\Session;
use App\Models\UserCase;

class TaskHelper
{
    private static $instance = null;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param $modelType
     * @param $modelId
     * @return null
     */
    public function getRelatedModel($model_type, $model_id)
    {
        switch ($model_type) {
            case 'case':
                return UserCase::find($model_id);
            case 'session':
                return Session::find($model_id);
            case 'service':
                return Service::find($model_id);
            case 'client':
                return Client::find($model_id);
            default:
                return null;
        }
    }

    public function getRelationModel($task)
    {
        switch ($task->model_type) {
            case('case');
                return new CaseResource($task->userCase);
                break;
            case ('session');
                return new SessionResource($task->session);
                break;
            case ('service');
                return new ServiceResource($task->service);
                break;
            case ('client');
                return new ClientResource($task->client);
            default;
            return null;
        }
    }

    public function getRelationForExport($task)
    {
        switch ($task->model_type) {
            case 'case':
                return $task->userCase->name ?? '';
            case 'session':
                return $task->session->link ?? '';
            case 'service':
                return $task->service->name ?? '';
            case 'client':
                return $task->client->name ?? '';
            default:
                return '';
        }
    }

    public static function dispose()
    {
        self::$instance = null;
    }
}

