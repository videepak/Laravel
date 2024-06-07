<?php

/*
  |--------------------------------------------------------------------------
  | Web Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register web routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | contains the "web" middleware group. Now create something great!
  |
 */
Route::get('uploads/{folder}/{filename}', 'GuestController@symbolicLink');

Route::post('/subscription_pack', 'PackageController@subscription_pack');
Route::post('/try_for_free', 'PackageController@try_for_free');
Route::post('/signup', 'PackageController@signup');
Route::get('/request-demo', 'PackageController@step1');
Route::get('/ntv-specialOffer', 'PackageController@nvtDemo');
Route::get('/free-trial', 'PackageController@step2');
Route::get('/plan-pricing', 'PackageController@step3');
Route::get('/plan-pricing-demo', 'PackageController@step3demo');
Route::get('/confirmation', 'PackageController@step4');
Route::get('/thanks', 'PackageController@step5');
Route::get('/my-login-welcome-page', 'PackageController@login_welcome');

Route::post('auto_billing_response', 'PackageController@auto_billing_response');

Route::get(
    '/',
    function () {
        return view('auth.login');
    }
);

Route::get(
    'login',
    function () {
        return view('auth.login');
    }
);

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::post('/chartajax', 'HomeController@chartajax');
Route::post('fillter/property/chartajax', 'HomeController@chartajax');
Route::post('efficiency/employee/chartajax', 'HomeController@chartajax');

Route::prefix('admin')->group(
    function () {
        Route::get('/login', 'Auth\AdminLoginController@showLoginForm')
                ->name('admin.login');
        Route::post('/login', 'Auth\AdminLoginController@login')
                ->name('admin.login.submit');
        Route::get('/', 'AdminController@index')->name('admin.dashboard');
        Route::post('get-subscriber', 'ManageSubscribers@getSubscriber');
        Route::get('super-admin', 'ManageSubscribers@superAdminIndex');
        Route::post('super-admin', 'ManageSubscribers@getSuperAdmin');
        Route::get('super-admin-delete/{id}', 'ManageSubscribers@superAdminDelete');
        Route::post('add-super-admin', 'ManageSubscribers@addSuperAdmin');
        Route::get('super-admin-detail/{id}', 'ManageSubscribers@getSuperDetail');
        Route::post('update/{id}', 'ManageSubscribers@updateSuperDetail');
        Route::post('resetSubscriberPassword', 'ManageSubscribers@resetSubscriberPassword');
        Route::get('users', 'ManageSubscribers@userIndex');
        Route::post('users', 'ManageSubscribers@getUser');
        Route::post('admin-user-delete/{id}', 'ManageSubscribers@adminUserDelete');
        Route::get('archive-user', 'ManageSubscribers@archiveUsers');
        Route::post('archive-users-list', 'ManageSubscribers@archiveUsersList');

       

        Route::get('view-comment/{id}', 'TicketsController@viewComment');
        Route::post('tickets-status', 'TicketsController@ticketStatus');
        Route::post('add-comment', 'TicketsController@addComment');
        Route::resource('tickets', 'TicketsController');

        Route::resource('reports', 'AdminReportsController');
        Route::post('violation-report', 'AdminReportsController@violationReport');
        Route::post('clock-report', 'AdminReportsController@clockReport');
        Route::post('unit-report', 'AdminReportsController@unitReport');
        Route::post('service-report', 'AdminReportsController@serviceReport');
        Route::get('reports-logs', 'AdminReportsController@showManageReports');
    }
);

/* Employee routes start  */
Route::get('employee/properties/{id}', 'EmployeeController@getassignedproperties');
Route::get('welcomeEmail/{id}', 'EmployeeController@welcomeEmail');
Route::post('employee-list/', 'EmployeeController@getEmployeelist');

/* Employee routes end  */

/* Role routes start */
Route::resource('role', 'RoleController');
Route::resource('reason', 'ReasonController');
/* Role routes end */

/* Property routes start  */
Route::post('property/customer/details/autopuplate', 'PropertyController@detailsautopuplate');
Route::post('assign/employee/property', 'PropertyController@assignempproperty');
Route::post('property/asssigned/employees/all', 'PropertyController@getassignedemployees');
Route::post('property/asssigned/employees/delete', 'PropertyController@deleteemployeeproperty');
Route::post('selected/property/ids', 'PropertyController@selectedids');
Route::get('add-builging-detail-manually', 'PropertyController@addBuilgingDetailManually');
Route::post('get-propertylist/', 'PropertyController@getPropertyList');
Route::resource('property', 'PropertyController', ['as' => 'Manage - Properties']);

//Route::get('payment-history', 'PaymentController@payment_history');
Route::get('payment-history/detail/{id}', 'PaymentController@payment_detail');

/* Customer routes start  */
Route::get('validate/customer/email', 'CustomerController@validateEmail');
Route::get('validate/customer/mobile', 'CustomerController@validateMobile');

/* Customer routes end  */

/* admin routs start */
Route::resource('admin/subscriptions', 'ManageSubscription', ['as' => 'Manage - Subscription']);
Route::get('admin/addsubscription', 'ManageSubscription@add_subscription');
Route::post('admin/addsubscription', 'ManageSubscription@subscription_add');
Route::get('admin/deletesubscription/{id}', 'ManageSubscription@subscription_delete');
Route::get('admin/viewsubscription/{id}', 'ManageSubscription@view_subscription');
Route::post('admin/updatesubscription/{id}', 'ManageSubscription@update_subscription');

Route::get('admin/subscribers/welcomeEmail/{id}', 'ManageSubscribers@welcomeEmail');
Route::resource('admin/subscribers', 'ManageSubscribers', ['as' => 'Manage - Subscriber']);
Route::get('admin/addsubscriber', 'ManageSubscribers@add_subscriber');
Route::get('validate/subscriber/email', 'ManageSubscribers@validateEmail');
Route::get('validate/subscriber/mobile', 'ManageSubscribers@validateMobile');
Route::post('admin/addsubscriber', 'ManageSubscribers@subscriber_add');
Route::get('admin/deletesubscriber/{id}', 'ManageSubscribers@subscriber_delete');
Route::get('admin/viewsubscriber/{id}', 'ManageSubscribers@view_subscriber');
Route::post('admin/updatesubscriber/{id}', 'ManageSubscribers@update_subscriber');
Route::get('admin/subscribers/residents-alert/{id}/{status}', 'ManageSubscribers@residentAlert');

Route::get('admin/forget/password', 'Auth\AdminLoginController@forgetPassword');
Route::post('admin/forgetpassword/checkemail', 'Auth\AdminLoginController@forgetCheckEmail');
Route::get('admin/reset/password', 'Auth\AdminLoginController@resetPassword');
Route::post('admin/setnew/password', 'Auth\AdminLoginController@adminPasswordReset');

/* admin routs end */

/* user routs start */
Route::get('changepassword', 'UserController@change_password');
Route::post('update_password', 'UserController@update_password');
Route::get('validate/email', 'BaseController@validateEmail');
Route::get('validate/mobile', 'BaseController@validateMobile');
Route::get('profile', 'UserController@profile');
Route::get('subscriber-profile', 'UserController@current_plan');
Route::get('manage-plan', 'UserController@manage_plan');
Route::get('payment-history', 'UserController@payment_history');

//Route::get('viewplan/{id}', 'UserController@subscription_info');
Route::get('upgradeplan/{id}', 'UserController@upgradeplan');
Route::get('thanks-for-payment/{id}', 'UserController@thankspayment');

Route::post('subsprofileupdate/{id}', 'UserController@subsprofileupdate');
Route::post('userprofileupdate/{id}', 'UserController@userprofileupdate');
Route::get('validate/useremail', 'UserController@validateUserEmail');

/* user routs end */
Route::post('pay/paysubscribe', 'StripeController@paysubscribe');

Route::post('pay/subscription', 'StripeController@paynow');
Route::post('pay/renew', 'StripeController@renew');
Route::post('pay/renewSubs', 'StripeController@renewSubs');

Route::post('getViolation', 'ViolationController@getViolation');
Route::post('get-note-list', 'ManageNotesController@getNoteList');
Route::post('getNote', 'ManageNotesController@getNote');
Route::post('change-violation-status', 'ViolationController@changeViolationStatus');
Route::post('get/manager/email/violation', 'ViolationController@getManagerEmailForViolation');
Route::post('/residentEmailViolation', 'ViolationController@residentEmailViolation');
Route::post('get-template-violation', 'ViolationController@residentTemplates');
Route::post('fetch-template', 'ViolationController@templateOnChange');
//Route::get('violation-filter-by/{id?}/{propertyId?}', 'ViolationController@violationFilterBy');
//Route::post('get-violation-for-edit', 'ViolationController@getViolationForEdit');

Route::get('manage-violation-action', 'ViolationController@manageViolationAction');
Route::get('create-violation-action', 'ViolationController@violationActionCreate');
Route::post('violation-send-mail', 'ViolationController@violationSendMail');
Route::get('violation-detail-by-link/{id}/{subId}', 'GuestController@violationDetailByLink');
Route::post('action-store', 'ViolationController@actionStore');
Route::get('action-edit/{id}', 'ViolationController@actionEdit');
Route::post('action-update/', 'ViolationController@actionUpdate');
Route::get('action-destroy/{id}', 'ViolationController@actionDestroy');
/* violation route */
//Route::get('violation/{status}', 'ViolationController@index');
Route::resource('violation/', 'ViolationController');
//Manage Violation Template
Route::get('violation-templates', 'ViolationController@manageTemplate');
Route::get('set-template-status/{id}', 'ViolationController@setTemplateStatus');
Route::post('add-template', 'ViolationController@addTemplate');
Route::get('delete-template/{id}', 'ViolationController@deleteTemplate');
Route::get('violation/template/detail/{id}', 'ViolationController@getTemplateDetail');
Route::post('update/template/{id}', 'ViolationController@updateTemplateDetail');

Route::get('recycle-report', 'ManageReportController@index');
Route::get('delivery-report', 'ManageReportController@deliverychart');
Route::post('delivery-data', 'ManageReportController@deliveryData');

Route::get('fillter/{condition}/{id}', 'ManageReportController@index');

Route::get('efficiency/{condition?}/{id?}', 'ManageefficiencyController@index');

//Route::get('property-manager/violation/{id?}', 'PropertyController@propertyManageViolationList');
Route::post('uploadunits', 'PropertyController@importProperties');

Route::post('note/change-status', 'ManageNotesController@updateNotesStatus');
Route::get('note', 'ManageNotesController@index');

Route::post('sendcontact', 'Auth\AdminLoginController@contactUs');
Route::get('voilationlist', 'CronController@newViolationListForPropertyManager');
Route::get('violation-nofication', 'CronController@getCountOlderday');
Route::get('insertUserTable', 'CronController@insertUserTable');

Route::get('reported-issue', 'ReportIssueController@index');
Route::get('mark-issue-exclude/{id}', 'ReportIssueController@markIssueExclude');

Route::get('top-violation/{id?}', 'PropertyManagerController@index');

Route::get('create-report-issue-reason', 'ReportIssueController@createIssueReason');
Route::post('store-issue-reason', 'ReportIssueController@storeIssueReason');
Route::get('report-issue-reason', 'ReportIssueController@listReportIssueReason');
Route::get('destory-issue-reason/{id}', 'ReportIssueController@issueReasonDestory');
Route::get('edit-issue-reason/{id}', 'ReportIssueController@editIssueReason');
Route::post('update-issue-reason/{id}', 'ReportIssueController@updateIssueReason');

//Note Reason Route
Route::resource('note-reason', 'NoteReasonController');
//Note Reason Route


// Route::get('barcode/filter/{id?}/{status?}/', 'BarcodeController@index');
// Route::get('notPickupList/', 'BarcodeController@notPickupList');
// Route::get('notPickupList/filter/{id?}', 'BarcodeController@notPickupList');
// Route::post('get-barcode-list', 'BarcodeController@getBarcodeList');
// Route::resource('barcode', 'BarcodeController', ['as' => 'Manage - Barcodes']);

Route::group(
    [
        'prefix' => 'customer',
    ],
    function () {
        Route::get('{customer}/property', 'CustomerController@property');
        Route::get('{customer}/details', 'CustomerController@details');
        Route::post('exsiting/', 'CustomerController@exsitingCustomer');
    }
);
Route::resource('customer', 'CustomerController', ['as' => 'Manage - Customers']);

Route::group(
    [
        'prefix' => 'barcodes',
    ],
    function () {
        //Route::get('barcode/filter/{id?}/{status?}/', 'BarcodeController@index');
        Route::get('notPickupList/', 'BarcodeController@notPickupList');
        Route::post('deactivation', 'BarcodeController@deactivation');
        Route::post('bulk-activation', 'BarcodeController@bulkActivation');
        Route::post('make-route', 'BarcodeController@makeRouteCheckpoint');
        Route::get('notPickupList/filter/{id?}', 'BarcodeController@notPickupList');
        Route::post('get-barcode-list', 'BarcodeController@getBarcodeList');
    }
);
Route::post('barcode/{id}', 'BarcodeController@destroy');
Route::resource('barcode', 'BarcodeController', ['as' => 'Manage - Barcodes']);

Route::group(
    [
        'prefix' => 'tasks',
    ],
    function () {
        Route::post('get-task', 'TasksController@getTask');
        Route::post('update-task/{id}', 'TasksController@updateTask');
        Route::get('task/detail/{id}', 'TasksController@getTaskDetail');
    }
);
Route::resource('/tasks', 'TasksController', ['as' => 'Manage - Tasks']);

Route::group(
    [
        'prefix' => 'employee',
    ],
    function () {
        Route::get('misspickup', 'EmployeeController@misspickupEmployeList');
    }
);
Route::resource('employee', 'EmployeeController', ['as' => 'Manage - Employee']);

Route::group(
    [
        'prefix' => 'dashboard',
    ],
    function () {
        Route::post('metrix', 'HomeController@dashboardMetrix');
        Route::post('daliy-reports', 'HomeController@daliyReports');
        Route::post('daliy-report-remote', 'HomeController@daliyReportRemote');
        Route::post('daliy-status', 'HomeController@daliyStatus');
    }
);

Route::group(
    [
        'prefix' => 'settings',
    ],
    function () {
        Route::get('app-setting', 'AppSettingController@appSetting');
        Route::post('default-employee-schedule', 'AppSettingController@defaultEmployeeSchedule');
        Route::post('dashboard-setting', 'AppSettingController@dashboardSetting');
        Route::post('notification-setting', 'AppSettingController@notificationSetting');
        Route::post('automated-service', 'AppSettingController@automatedServiceReport');
    }
);

Route::group(
    [
        'prefix' => 'violation',
    ],
    function () {
        Route::post('getViolations', 'ViolationController@getViolations');
        Route::post('get-violation-for-update', 'ViolationController@getViolationForUpdate');
        Route::post('get-violation-for-spacial-notes', 'ViolationController@getSpacialNoteUpdate');
        Route::post('comment', 'ViolationController@updateComment');
        Route::post('violation-reminder', 'ViolationController@violationReminder');
    }
);

Route::group(
    [
        'prefix' => 'report',
    ],
    function () {
        Route::post('cal-pickup', 'ManageReportController@calPickup');
        Route::get('manage-task', 'ManageReportController@manageTask');
        Route::post('task-data', 'ManageReportController@taskData');
        Route::post('getHistoricalReport', 'ManageReportController@getHistoricalReport');
        Route::get('historical-report', 'ManageReportController@historicalCheckInOut');
        Route::get('manage-routecheckpoints', 'ManageReportController@routeCheckpoints');
        Route::post('get-list', 'ManageReportController@routeCheckPoint');
        Route::get('routecheckpoint-excel', 'ManageReportController@routeCheckpointExcel');
    }
);

Route::group(
    [
        'prefix' => 'property',
    ],
    function () {
        Route::post('add-more-unit', 'PropertyController@addMoreUnit');
        Route::get('qrcode-generate/{id}', 'PropertyController@generateQrCode');
        Route::get('download-excel/{id}', 'PropertyController@getSample');
        Route::post('bulding-remove', 'PropertyController@buildingDetele');
        Route::get('building-list', 'PropertyController@buildingList');
        Route::get('residents-alert/{id}/{status}', 'PropertyController@residentAlert');
        Route::get('getQrCodeProperty/{propertyId}', 'PropertyController@getQrCodeProperty');
    }
);
Route::get('building-list', 'PropertyController@buildingList');
Route::post('getBuilding', 'PropertyController@getBuilding');


Route::group(
    [
        'prefix' => 'clockinout',
    ],
    function () {
        Route::get('report', 'ClockInOutController@clockInOutDetail');
        Route::post('getReport', 'ClockInOutController@getReport');
        Route::post('resetDateTime', 'ClockInOutController@resetDateTime');
    }
);

//Route check point Task: #1040: Start

Route::group(
    [
        'prefix' => 'routecheck-point',
    ],
    function () {
        Route::post('get-list', 'RouteCheckPointController@routeCheckPoint');
        Route::post('print-barcodes', 'RouteCheckPointController@printBarcodes');
        Route::get('violation', 'RouteCheckPointController@violation');
        Route::get('qrcode', 'RouteCheckPointController@checkPointQrCode');
        Route::post('make-checkpoint', 'RouteCheckPointController@makeCheckpoint');
        Route::post('change-name', 'RouteCheckPointController@changeName');
    }
);

Route::resource('routecheck-point', 'RouteCheckPointController');
//Route check point Task: #1040: End

Route::group(
    [
        'prefix' => 'guest',
    ],
    function () {
        Route::get('automated-service-report/{id}', 'GuestController@automatedServiceReport');
        Route::get('automated-unit-report/{id}', 'GuestController@automatedUnitReport');
        Route::get('automated-clockinout-report/{id}', 'GuestController@automatedClockinoutReport');
        Route::get('automated-violation-report/{id}', 'GuestController@automatedViolationReport');
        Route::get('residents/{id}', 'GuestController@residents');
        Route::post('get-residents', 'GuestController@getResidents');
        Route::post('update-residents', 'GuestController@updateResidents');
    }
);

Route::group(
    [
        'prefix' => 'activity',
    ],
    function () {
        Route::get('logs', 'ActivitylogsController@propertyManagerLog');
        Route::post('get-activitylog', 'ActivitylogsController@logPropertyManager');
        Route::get('all-activity-logs', 'ActivitylogsController@allActivitiesLog');
    }
);
Route::resource('activitylogs', 'ActivitylogsController');

Route::group(
    [
        'prefix' => 'property-manager',
    ],
    function () {
        Route::get('top-violation/{id?}', 'PropertyManagerController@index');
        Route::post('get-violation', 'PropertyManagerController@getViolation');
        Route::get('search-unit', 'PropertyManagerController@searchUnit');
        Route::get('unit-history/{id}', 'PropertyManagerController@unitHistory');
        Route::get('tasks/', 'PropertyManagerController@taskList');
        Route::post('get-task/', 'PropertyManagerController@getTask');
        Route::get('reported-issue', 'ReportIssueController@index');
        Route::get('units-serviced', 'PropertyManagerController@unitsServiced');
        Route::post('serviced-list', 'PropertyManagerController@getUnitsServiced');
        Route::get('edit-property', 'PropertyManagerController@editProperty');
        Route::post('update-property/{id}', 'PropertyManagerController@updateProperty');

        Route::get('/', 'CustomerController@propertyManagerIndex');
        Route::get('create', 'CustomerController@propertyManagerCreate');
        Route::post('add', 'CustomerController@propertyManagerStore');
        Route::delete('destory/{id}', 'CustomerController@propertyManagerDestory');
        Route::get('edit/{id}', 'CustomerController@propertyManagerEdit');
        Route::put('update/{id}', 'CustomerController@propertyManagerUpdate');
        Route::get('resident-templates', 'ViolationController@manageTemplate');
        //Route::get('activitylogs/{id}', 'ActivitylogsController@propertyManagerLog');
        //Route::get('activitylogs', 'ActivitylogsController@propertyManagerLog');
        //Route::post('get-activitylog', 'ActivitylogsController@logPropertyManager');
        //Route::get('/resident-list', 'ResidentController@residentIndex');
        Route::post('/get-resident', 'ResidentController@getResidentList');
        Route::resource('/resident', 'ResidentController');
        Route::post('/get-template', 'ResidentController@getResidentTemplate');
        Route::post('/resident-send-mail', 'ResidentController@residentSendMail');
        //Route::get('/product-export/{type}', 'ResidentController@residentExport');
        Route::post('/resident-import', 'ResidentController@residentsImport')->name('residents.import');
        Route::get('/download-resident', 'ResidentController@getDownload');
        Route::post('/get-email-history', 'ResidentController@showEmailHistory');
        Route::get('/email-history/{id?}', 'ResidentController@emailHistoryIndex');
        Route::post('/get-residentunit', 'ResidentController@getResidentLogs');
        Route::post('/change-moveoutdate', 'ResidentController@changeMoveOutDate');
        Route::post('/update-unit', 'ResidentController@updateUnitEdit');
        // Route::get('/createResident/{id?}', 'ResidentController@create');
        // Route::get('/editResident/{id?}', 'ResidentController@edit');
	    // Route::post('/storeResident/{id?}', 'ResidentController@store');
    }
);

Route::get('violationimages/{violation_id}', 'PropertyController@getImages');
Route::get('violationdetails/{violation_id}', 'PropertyController@getviolationdetails');
Route::post('violation/print', 'PropertyController@getViolationPrint');
Route::get('check-in-property-pending', 'PropertyController@checkInPending');
Route::get('get-employee/{id}', 'PropertyController@getEmployee');
Route::post('check-in-sms', 'PropertyController@sendCheckInSms');
Route::post('send-sms-employee', 'PropertyController@sendSmsEmployee');
Route::post('download/violation/pdf', 'ViolationController@downloadViolationPdf');

Route::get('clockinout-detail', 'GuestController@clockInOutDetail');

// Route::get(
//     'route-check-point',
//     function () {
//         return view('property.routecheckpoint');
//     }
// );

Route::get(
    'privacy-policy',
    function () {
        return view('privacypolicy');
    }
);

Route::get(
    'terms-and-condition',
    function () {
        return view('termsandcondition');
    }
);
 
//Create url for clock-in and clock-out push notification (Tasks: 1144) Start
Route::get(
    '/push-notification',
    function () {
        return Artisan::call('command:AutomatedServiceReportDaliy');
    }
);
//Create url for clock-in and clock-out push notification (Tasks: 1144) End

Route::get(
    '/run-migrations',
    function () {
        return Artisan::call('migrate', ['--force' => true]);
    }
);

Route::get(
    '/run-schedule',
    function () {
        return Artisan::call('schedule:run');
    }
);

Route::get(
    '/run-migration-rollback',
    function () {
        return Artisan::call('migrate:rollback', ['--force' => true]);
    }
);

Route::get(
    'unauthorized',
    function () {
        return view('errors.unauthorized');
    }
);

Route::get(
    'unauthorized',
    function () {
        return view('errors.unauthorized');
    }
);

Route::get(
    'files',
    function () {
        return redirect('https://trashscanstaging.s3.amazonaws.com/uploads/user/1666950694disolvingmantarget.png');
    }
);

Route::get(
    'run-script',
    function () {
        $v = \App\Violation::withTrashed()
            //->where('property_id', 0)
            ->where('building_id', 0)
            ->get();
        
        foreach ($v as $vo) {
            $vo->timestamps = false;
            $u = \App\Units::where('barcode_id', $vo->barcode_id)
                ->withTrashed()->first();

            $vo->property_id = empty($u->property_id) ?: $u->property_id;
            $vo->building_id = empty($u->building_id) ?: $u->building_id;
            $vo->save();
        }

        // $v = \App\Violation::withTrashed()->latest()->get();

        // foreach ($v as $vo) {
        //     try {
        //         $vo->timestamps = false;

        //         if ($vo->type) {
        //             $p = \App\RouteCheckIn::withTrashed()->where('barcode_id', $vo->barcode_id)->first();
        //         } else {
        //             $p = \App\Units::withTrashed()->where('barcode_id', $vo->barcode_id)->first();
        //         }
        //         $vo->property_id = $p->property_id;
        //         $vo->building_id = $p->building_id;
        //         $vo->save();
        //     } catch (Exception $e) {
        //         echo 'Message: '.$e->getMessage();
        //     }
        // }

        // $v = \App\Violation::withTrashed()->whereNotIn('status', [0])->get();

        // foreach ($v as $vo) {
        //     $vo->timestamps = false;

        //     if ($vo->status == 2) {
        //         $vo->manager_status = 0;
        //     } else {
        //         $vo->manager_status = $vo->status;
        //     }

        //     $vo->save();
        // }

        // $a = \App\Activitylogs::where(
        //         function ($query) {
        //             $query->whereNull('property_id')
        //             ->orWhereNull('building_id');
        //         }
        //     )
        //     ->where('type', '1')
        //     ->whereNotNull('barcode_id')
        //     //->groupBy('barcode_id')
        //     ->withTrashed()
        //     //->limit(1)
        //     ->latest()
        //     ->get();
           
        // foreach ($a as $as) {
        //     $p = \App\Units::where('barcode_id', $as->barcode_id)
        //         ->withTrashed()->first();

        //     if (!is_null($p)) {
        //         $as->timestamps = false;
        //         $as->property_id = $p->property_id;
        //         if (!empty($p->building_id)) {
        //             $as->building_id = $p->building_id;
        //         }
        //         $as->save();
        //     }
        // }

        // $a = \App\RouteCheckIn::all();
        
        // foreach ($a as $as) {
        //     $u = new \App\Units();
        //     $u->unit_number = $as->name;
        //     $u->address1 = $as->address1;
        //     $u->address2 = $as->address2;
        //     $u->barcode_id = $as->barcode_id;
        //     $u->building_id = $as->building_id;
        //     $u->property_id = $as->property_id;
        //     $u->deleted_at = $as->deleted_at;
        //     $u->is_route = 1;
        //     $u->save();
        // }
    }
);
