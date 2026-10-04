<?php


// Read-only opponents directory aggregated from existing cases.
Route::prefix('opponent')
    ->middleware(['auth:api-Admin', 'branchMiddleware'])
    ->group(function () {
        Route::get('/get', [\App\Http\Controllers\Admin\OpponentController::class, 'get']);
        Route::get('/contact', [\App\Http\Controllers\Admin\OpponentController::class, 'contact']);
    });




Route::get('/convertToHigri', [\App\Http\Controllers\Admin\SessionController::class, 'convertToHigri']);
Route::middleware(['auth:api-Admin','branchMiddleware'])->group(function () {

        Route::prefix('auth')->group(function () {
            Route::get('/my_info', 'AuthController@my_info');
            Route::post('/edit_profile', [\App\Http\Controllers\Admin\AuthController::class, 'edit_profile']);
            Route::post('/logout', [\App\Http\Controllers\Admin\AuthController::class, 'logout']);
        });

        Route::prefix('type')->group(function () {
            Route::get('/get', [\App\Http\Controllers\Admin\TypeController::class, 'get']);
            Route::get('/single', [\App\Http\Controllers\Admin\TypeController::class, 'single']);
            Route::post('/create', [\App\Http\Controllers\Admin\TypeController::class, 'create']);
            Route::post('/update', [\App\Http\Controllers\Admin\TypeController::class, 'update']);
            Route::post('/delete', [\App\Http\Controllers\Admin\TypeController::class, 'delete']);
        });


        Route::prefix('job')->group(function () {
            Route::get('/get', [\App\Http\Controllers\Admin\JobController::class, 'get']);
            Route::get('/single', [\App\Http\Controllers\Admin\JobController::class, 'single']);
            Route::post('/create', [\App\Http\Controllers\Admin\JobController::class, 'create']);
            Route::post('/update', [\App\Http\Controllers\Admin\JobController::class, 'update']);
            Route::post('/delete', [\App\Http\Controllers\Admin\JobController::class, 'delete']);
        });


        Route::prefix('admin')->group(function () {
            Route::get('/get', [\App\Http\Controllers\Admin\AdminController::class, 'get']);
            Route::get('/get_permissions', [\App\Http\Controllers\Admin\AdminController::class, 'get_permissions']);
            Route::get('/single', [\App\Http\Controllers\Admin\AdminController::class, 'single']);
            Route::post('/create', [\App\Http\Controllers\Admin\AdminController::class, 'create'])->middleware('superAdmin');
            Route::post('/update', [\App\Http\Controllers\Admin\AdminController::class, 'update'])->middleware('superAdmin');
            Route::post('/delete', [\App\Http\Controllers\Admin\AdminController::class, 'delete'])->middleware('superAdmin');
        });

        Route::prefix('admin_file')->group(function () {

            Route::post('/create', [\App\Http\Controllers\Admin\AdminFileController::class, 'create']);
            Route::post('/update', [\App\Http\Controllers\Admin\AdminFileController::class, 'update']);
            Route::post('/delete', [\App\Http\Controllers\Admin\AdminFileController::class, 'delete']);
        });

        Route::prefix('client_status')->group(function () {
            Route::get('/get', [\App\Http\Controllers\Admin\ClientStatusController::class, 'get']);
            Route::get('/get_clients', [\App\Http\Controllers\Admin\ClientStatusController::class, 'get_clients']);
            Route::get('/single', [\App\Http\Controllers\Admin\ClientStatusController::class, 'single']);
            Route::post('/create', [\App\Http\Controllers\Admin\ClientStatusController::class, 'create']);
            Route::post('/update', [\App\Http\Controllers\Admin\ClientStatusController::class, 'update']);
            Route::post('/delete', [\App\Http\Controllers\Admin\ClientStatusController::class, 'delete']);
        });

        Route::prefix('country')->group(function () {
            Route::get('/get', [\App\Http\Controllers\Admin\CountryController::class, 'get']);
            Route::get('/single', [\App\Http\Controllers\Admin\CountryController::class, 'single']);
            Route::post('/create', [\App\Http\Controllers\Admin\CountryController::class, 'create']);
            Route::post('/update', [\App\Http\Controllers\Admin\CountryController::class, 'update']);
            Route::post('/delete', [\App\Http\Controllers\Admin\CountryController::class, 'delete']);
        });

        Route::prefix('city')->group(function () {
            Route::get('/get', [\App\Http\Controllers\Admin\CityController::class, 'get']);
            Route::get('/single', [\App\Http\Controllers\Admin\CityController::class, 'single']);
            Route::post('/create', [\App\Http\Controllers\Admin\CityController::class, 'create']);
            Route::post('/update', [\App\Http\Controllers\Admin\CityController::class, 'update']);
            Route::post('/delete', [\App\Http\Controllers\Admin\CityController::class, 'delete']);
        });

        Route::prefix('district')->group(function () {
            Route::get('/get', [\App\Http\Controllers\Admin\DistrictController::class, 'get']);
            Route::get('/single', [\App\Http\Controllers\Admin\DistrictController::class, 'single']);
            Route::post('/create', [\App\Http\Controllers\Admin\DistrictController::class, 'create']);
            Route::post('/update', [\App\Http\Controllers\Admin\DistrictController::class, 'update']);
            Route::post('/delete', [\App\Http\Controllers\Admin\DistrictController::class, 'delete']);
        });

        Route::prefix('branch')->group(function () {
            Route::get('/get', [\App\Http\Controllers\Admin\BranchController::class, 'get']);
            Route::get('/single', [\App\Http\Controllers\Admin\BranchController::class, 'single']);
            Route::post('/create', [\App\Http\Controllers\Admin\BranchController::class, 'create'])->middleware('superAdmin');
            Route::post('/update', [\App\Http\Controllers\Admin\BranchController::class, 'update'])->middleware('superAdmin');
            Route::post('/delete', [\App\Http\Controllers\Admin\BranchController::class, 'delete'])->middleware('superAdmin');
        });

        Route::prefix('case_status')->group(function () {
            Route::get('/get', [\App\Http\Controllers\Admin\CaseStatusController::class, 'get']);
            Route::get('/single', [\App\Http\Controllers\Admin\CaseStatusController::class, 'single']);
            Route::post('/create', [\App\Http\Controllers\Admin\CaseStatusController::class, 'create']);
            Route::post('/update', [\App\Http\Controllers\Admin\CaseStatusController::class, 'update']);
            Route::post('/delete', [\App\Http\Controllers\Admin\CaseStatusController::class, 'delete']);
        });


        Route::prefix('case_status_history')->group(function () {
            Route::get('/get', [\App\Http\Controllers\Admin\CaseStatusHistoryController::class, 'get']);

        });

        Route::prefix('client')->group(function () {
            Route::get('/get', [\App\Http\Controllers\Admin\ClientController::class, 'get']);
            Route::get('/single', [\App\Http\Controllers\Admin\ClientController::class, 'single']);
            Route::post('/create', [\App\Http\Controllers\Admin\ClientController::class, 'create']);
            Route::post('/update', [\App\Http\Controllers\Admin\ClientController::class, 'update']);
            Route::post('/delete', [\App\Http\Controllers\Admin\ClientController::class, 'delete']);
            Route::post('/change_status', [\App\Http\Controllers\Admin\ClientController::class, 'change_status']);
        });

        Route::prefix('client_file')->group(function () {
            Route::get('/get', [\App\Http\Controllers\Admin\ClientFileController::class, 'get']);
            Route::get('/single', [\App\Http\Controllers\Admin\ClientFileController::class, 'single']);
            Route::post('/create', [\App\Http\Controllers\Admin\ClientFileController::class, 'create']);
            Route::post('/update', [\App\Http\Controllers\Admin\ClientFileController::class, 'update']);
            Route::post('/delete', [\App\Http\Controllers\Admin\ClientFileController::class, 'delete']);
        });


        Route::prefix('case')->group(function () {
            Route::get('/get', [\App\Http\Controllers\Admin\CaseController::class, 'get']);
            Route::get('/single', [\App\Http\Controllers\Admin\CaseController::class, 'single']);
            Route::post('/create', [\App\Http\Controllers\Admin\CaseController::class, 'create']);
            Route::post('/update', [\App\Http\Controllers\Admin\CaseController::class, 'update']);
            Route::post('/delete', [\App\Http\Controllers\Admin\CaseController::class, 'delete']);
            Route::post('/change_status', [\App\Http\Controllers\Admin\CaseController::class, 'change_status']);
        });

        Route::prefix('payment_method')->group(function () {
            Route::get('/get', [\App\Http\Controllers\Admin\PaymentMethodController::class, 'get']);
            Route::get('/single', [\App\Http\Controllers\Admin\PaymentMethodController::class, 'single']);
            Route::post('/create', [\App\Http\Controllers\Admin\PaymentMethodController::class, 'create'])->middleware('superAdmin');
            Route::post('/update', [\App\Http\Controllers\Admin\PaymentMethodController::class, 'update'])->middleware('superAdmin');
            Route::post('/delete', [\App\Http\Controllers\Admin\PaymentMethodController::class, 'delete'])->middleware('superAdmin');
        });

        Route::prefix('case_file')->group(function () {
            Route::get('/get', [\App\Http\Controllers\Admin\CaseFileController::class, 'get']);
            Route::get('/single', [\App\Http\Controllers\Admin\CaseFileController::class, 'single']);
            Route::post('/create', [\App\Http\Controllers\Admin\CaseFileController::class, 'create']);
            Route::post('/update', [\App\Http\Controllers\Admin\CaseFileController::class, 'update']);
            Route::post('/delete', [\App\Http\Controllers\Admin\CaseFileController::class, 'delete']);
        });

        Route::prefix('session')->group(function () {
            Route::get('/get', [\App\Http\Controllers\Admin\SessionController::class, 'get']);
            Route::get('/single', [\App\Http\Controllers\Admin\SessionController::class, 'single']);
            Route::post('/create', [\App\Http\Controllers\Admin\SessionController::class, 'create']);
            Route::post('/update', [\App\Http\Controllers\Admin\SessionController::class, 'update']);
            Route::post('/delete', [\App\Http\Controllers\Admin\SessionController::class, 'delete']);
        });

        Route::prefix('session_status')->group(function () {
            Route::get('/get', [\App\Http\Controllers\Admin\SessionStatusController::class, 'get']);
            Route::get('/single', [\App\Http\Controllers\Admin\SessionStatusController::class, 'single']);
            Route::post('/create', [\App\Http\Controllers\Admin\SessionStatusController::class, 'create']);
            Route::post('/update', [\App\Http\Controllers\Admin\SessionStatusController::class, 'update']);
            Route::post('/delete', [\App\Http\Controllers\Admin\SessionStatusController::class, 'delete']);
        });


        Route::prefix('note')->group(function () {
            Route::post('/create', [\App\Http\Controllers\Admin\NoteController::class, 'create']);
            Route::post('/update', [\App\Http\Controllers\Admin\NoteController::class, 'update']);
            Route::post('/delete', [\App\Http\Controllers\Admin\NoteController::class, 'delete']);
        });

        Route::prefix('case_receipt')->group(function () {
            Route::post('/pay_receipt', [\App\Http\Controllers\Admin\CaseReceiptController::class, 'pay_receipt']);
            Route::get('/get', [\App\Http\Controllers\Admin\CaseReceiptController::class, 'get']);
        });

        Route::prefix('service_type')->group(function () {
            Route::get('/get', [\App\Http\Controllers\Admin\ServiceTypeController::class, 'get']);
            Route::get('/single', [\App\Http\Controllers\Admin\ServiceTypeController::class, 'single']);
            Route::post('/create', [\App\Http\Controllers\Admin\ServiceTypeController::class, 'create']);
            Route::post('/update', [\App\Http\Controllers\Admin\ServiceTypeController::class, 'update']);
            Route::post('/delete', [\App\Http\Controllers\Admin\ServiceTypeController::class, 'delete']);
        });


        Route::prefix('service')->group(function () {
            Route::get('/get', [\App\Http\Controllers\Admin\ServiceController::class, 'get']);
            Route::get('/single', [\App\Http\Controllers\Admin\ServiceController::class, 'single']);
            Route::post('/create', [\App\Http\Controllers\Admin\ServiceController::class, 'create']);
            Route::post('/update', [\App\Http\Controllers\Admin\ServiceController::class, 'update']);
            Route::post('/change_status', [\App\Http\Controllers\Admin\ServiceController::class, 'change_status']);
            Route::post('/delete', [\App\Http\Controllers\Admin\ServiceController::class, 'delete']);
        });

        /** service_receipt routes */
        Route::prefix('service_receipt')->group(function () {
            Route::post('/pay_service_receipt', [\App\Http\Controllers\Admin\ServiceReceiptController::class, 'pay_service_receipt']);
            Route::get('/get', [\App\Http\Controllers\Admin\ServiceReceiptController::class, 'get']);
        });

        Route::prefix('calender')->group(function () {
            Route::get('/get_calender', [\App\Http\Controllers\Admin\CalenderController::class, 'get_calender']);
        });

        Route::prefix('home')->group(function () {
            Route::get('/home', [\App\Http\Controllers\Admin\HomeController::class, 'home']);
            Route::get('/general_search', [\App\Http\Controllers\Admin\HomeController::class, 'general_search']);
        });

        Route::prefix('reports')->group(function () {
            Route::get('/cases', [\App\Http\Controllers\Admin\ReportController::class, 'cases']);
            Route::get('/case_receipts', [\App\Http\Controllers\Admin\ReportController::class, 'case_receipts']);
            Route::get('/services', [\App\Http\Controllers\Admin\ReportController::class, 'services']);
            Route::get('/service_receipts', [\App\Http\Controllers\Admin\ReportController::class, 'service_receipts']);
            Route::get('/tasks', [\App\Http\Controllers\Admin\ReportController::class, 'tasks']);
            Route::get('/sessions', [\App\Http\Controllers\Admin\ReportController::class, 'sessions']);
            Route::get('/clients', [\App\Http\Controllers\Admin\ReportController::class, 'clients']);
        });

        Route::prefix('replay')->group(function () {
            Route::get('/get', [\App\Http\Controllers\Admin\ReplayController::class, 'get']);
            Route::get('/single', [\App\Http\Controllers\Admin\ReplayController::class, 'single']);
            Route::post('/create', [\App\Http\Controllers\Admin\ReplayController::class, 'create']);
            Route::post('/update', [\App\Http\Controllers\Admin\ReplayController::class, 'update']);
            Route::post('/delete', [\App\Http\Controllers\Admin\ReplayController::class, 'delete']);
        });

        Route::prefix('appointment')->group(function () {
            Route::get('/get', [\App\Http\Controllers\Admin\AppointmentController::class, 'get']);
            Route::get('/single', [\App\Http\Controllers\Admin\AppointmentController::class, 'single']);
            Route::post('/create', [\App\Http\Controllers\Admin\AppointmentController::class, 'create']);
            Route::post('/update', [\App\Http\Controllers\Admin\AppointmentController::class, 'update']);
            Route::post('/delete', [\App\Http\Controllers\Admin\AppointmentController::class, 'delete']);
            Route::post('/is_attend', [\App\Http\Controllers\Admin\AppointmentController::class, 'is_attend']);
        });

        Route::prefix('contact_reason')->group(function () {
            Route::get('/get', [\App\Http\Controllers\Admin\ContactReasonController::class, 'get']);
            Route::get('/single', [\App\Http\Controllers\Admin\ContactReasonController::class, 'single']);
            Route::post('/create', [\App\Http\Controllers\Admin\ContactReasonController::class, 'create']);
            Route::post('/update', [\App\Http\Controllers\Admin\ContactReasonController::class, 'update']);
            Route::post('/delete', [\App\Http\Controllers\Admin\ContactReasonController::class, 'delete']);
        });

        Route::prefix('contact')->group(function () {
            Route::get('/get', [\App\Http\Controllers\Admin\ContactController::class, 'get']);
            Route::get('/single', [\App\Http\Controllers\Admin\ContactController::class, 'single']);
            Route::post('/create', [\App\Http\Controllers\Admin\ContactController::class, 'create']);
            Route::post('/update', [\App\Http\Controllers\Admin\ContactController::class, 'update']);
            Route::post('/delete', [\App\Http\Controllers\Admin\ContactController::class, 'delete']);
        });

        Route::prefix('device')->group(function () {
            Route::get('/get', [\App\Http\Controllers\Admin\DeviceController::class, 'get']);
            Route::get('/single', [\App\Http\Controllers\Admin\DeviceController::class, 'single']);
            Route::post('/create', [\App\Http\Controllers\Admin\DeviceController::class, 'create']);
            Route::post('/update', [\App\Http\Controllers\Admin\DeviceController::class, 'update']);
            Route::post('/delete', [\App\Http\Controllers\Admin\DeviceController::class, 'delete']);
        });

        Route::prefix('source')->group(function () {
            Route::get('/get', [\App\Http\Controllers\Admin\SourceController::class, 'get']);
            Route::get('/single', [\App\Http\Controllers\Admin\SourceController::class, 'single']);
            Route::post('/create', [\App\Http\Controllers\Admin\SourceController::class, 'create']);
            Route::post('/update', [\App\Http\Controllers\Admin\SourceController::class, 'update']);
            Route::post('/delete', [\App\Http\Controllers\Admin\SourceController::class, 'delete']);
        });

        Route::prefix('task')->group(function () {
            Route::get('/get', [\App\Http\Controllers\Admin\TaskController::class, 'get']);
            Route::get('/single', [\App\Http\Controllers\Admin\TaskController::class, 'single']);
            Route::post('/create', [\App\Http\Controllers\Admin\TaskController::class, 'create']);
            Route::post('/update', [\App\Http\Controllers\Admin\TaskController::class, 'update']);
            Route::post('/delete', [\App\Http\Controllers\Admin\TaskController::class, 'delete']);
            Route::post('/finish_task', [\App\Http\Controllers\Admin\TaskController::class, 'finish_task']);
        });




        Route::prefix('notification')->group(function () {
            Route::get('/get', [\App\Http\Controllers\Admin\NotificationController::class, 'get']);
            Route::get('/single', [\App\Http\Controllers\Admin\NotificationController::class, 'single']);
            Route::post('/read', [\App\Http\Controllers\Admin\NotificationController::class, 'read']);
        });

        Route::prefix('setting')->group(function () {
            Route::get('/get', [\App\Http\Controllers\Admin\SettingController::class, 'get']);
            Route::post('/update', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->middleware('superAdmin');
        });

        Route::prefix('date')->group(function () {
            Route::post('/convert_to_hijri', [\App\Http\Controllers\Admin\DateController::class, 'convertToHijri']);
        });
});

/** Auth_general */
Route::prefix('auth')->group(function () {
    Route::post('/login', [\App\Http\Controllers\Admin\AuthController::class, 'login']);

});


Route::prefix('export')->group(function () {
    Route::get('/clients', [\App\Http\Controllers\Admin\ExportController::class, 'clients']);
    Route::get('/cases', [\App\Http\Controllers\Admin\ExportController::class, 'cases']);
    Route::get('/case_receipts', [\App\Http\Controllers\Admin\ExportController::class, 'case_receipts']);
    Route::get('/services', [\App\Http\Controllers\Admin\ExportController::class, 'services']);
    Route::get('/service_receipts', [\App\Http\Controllers\Admin\ExportController::class, 'service_receipts']);
    Route::get('/sessions', [\App\Http\Controllers\Admin\ExportController::class, 'sessions']);
    Route::get('/admins', [\App\Http\Controllers\Admin\ExportController::class, 'admins']);
    Route::get('/task', [\App\Http\Controllers\Admin\ExportController::class, 'tasks']);
    Route::get('/appointments', [\App\Http\Controllers\Admin\ExportController::class, 'appointments']);
    Route::get('/contacts', [\App\Http\Controllers\Admin\ExportController::class, 'contacts']);
});

Route::prefix('page')->group(function () {
    Route::get('/case_receipt/single', [\App\Http\Controllers\Admin\PageController::class, 'case_receipt']);
    Route::get('/service_receipt/single', [\App\Http\Controllers\Admin\PageController::class, 'service_receipt']);
});

