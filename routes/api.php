<?php

use Illuminate\Http\Request;
/*
  |--------------------------------------------------------------------------
  | API Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register API routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | is assigned the "api" middleware group. Enjoy building your API!
  |
 */

Route::middleware('auth:api')->get(
    '/user',
    function (Request $request) {
        return $request->user();
    }
);

// Route::group(
//     [
//         'prefix' => 'stripe',
//     ],
//     function () {
//         Route::post('store/detail', 'API\Stripe\StripeController@storeDetail');
//         Route::post('check/packagename', 'API\Stripe\StripeController@checkPackageName');
//     }
// );

Route::group(
    [
        'prefix' => 'v9',
    ],
    function () {
        Route::middleware(['auth:api'])->group(
            function () {
                Route::post('getViolation', 'API\v9\UserController@getViolation');
                
                Route::post('getPortersDetails', 'API\v9\UserController@getPortersDetails');

                Route::post('getProperty', 'API\v9\UserController@getProperty');

                Route::post('getBuilding', 'API\v9\UserController@getBuilding');
                
                Route::post('getUnit', 'API\v9\UserController@getUnit');
                
                Route::post('propertyCheckout', 'API\v9\UserController@propertyCheckout');

                Route::post('taskSubmit', 'API\v9\UserController@taskSubmit');
                
                Route::post('task', 'API\v9\UserController@task');

                Route::post('issueReportCategory', 'API\v9\UserController@ticketCategory');

                Route::post('issueReport', 'API\v9\UserController@issueReport');

                Route::post('reRoute', 'API\v9\UserController@reRoute');
                
                Route::post('updateNote', 'API\v9\UserController@updateNote');

                Route::post('getNoteByUnit', 'API\v9\UserController@getNoteByUnit');

                Route::post('userProfile', 'API\v9\UserController@userProfile');

                Route::post('updateProfile', 'API\v9\UserController@updateProfile');

                Route::post('propertyDetail', 'API\v9\UserController@getPropertyDetail');

                Route::post('subProperties', 'API\v9\UserController@subProperties');

                Route::post('subProDetail', 'API\v9\UserController@subProDetail');

                Route::post('propertiesListV2', 'API\v9\UserController@propertiesListV2');

                Route::post('propertyCheckIn', 'API\v9\UserController@propertyCheckIn');

                Route::post('getActivityLog', 'API\v9\UserController@getActivityLog');

                Route::post('scanQrcode', 'API\v9\UserController@scanQrcode');

                Route::post('scanQrcodeV2', 'API\v9\UserController@scanQrcodeV2');

                Route::post('activateCode', 'API\v9\UserController@activateCode');

                Route::post('activateCodeV2', 'API\v9\UserController@activateCodeV2');

                Route::post('createViolation', 'API\v9\UserController@createViolation');

                Route::post('createViolationV1', 'API\v9\UserController@createViolationV1');

                Route::post('reportViolation', 'API\v9\UserController@reportViolation');

                Route::post('pickUp', 'API\v9\UserController@pickUp');

                Route::post('manuallyPickUp', 'API\v9\UserController@manuallyPickUp');

                Route::post('pickUp_V2', 'API\v9\UserController@pickUpV2');

                Route::post('scanRevockBarcode', 'API\v9\UserController@scanRevockBarcode');

                Route::post('note', 'API\v9\UserController@note');

                Route::post('noteReason', 'API\v9\UserController@noteReason');

                Route::post('changePassword', 'API\v9\UserController@changePassword');

                Route::post('getEmployeschedule', 'API\v9\UserController@getEmployeschedule');

                Route::post('getEmployescheduleV2', 'API\v9\UserController@getEmployescheduleV2');

                Route::post('getEmployescheduleV3', 'API\v9\UserController@getEmployescheduleV3');

                Route::post('getEmployescheduleV4', 'API\v9\UserController@getEmployescheduleV4');

                Route::post('workPlanFillterApi', 'API\v9\UserController@workPlanFillterApi');

                Route::post('workPlanFillterApiV2', 'API\v9\UserController@workPlanFillterApiV2');

                Route::post('addNoteSchedule', 'API\v9\UserController@addNoteSchedule');

                Route::post('addNoteScheduleV2', 'API\v9\UserController@addNoteScheduleV2');

                Route::post('addNoteScheduleV3', 'API\v9\UserController@addNoteScheduleV3');

                Route::post('walkThrough', 'API\v9\UserController@walkThrough');

                Route::post('getReportIssueReason', 'API\v9\UserController@getReportIssueReason');

                Route::post('reportIssue', 'API\v9\UserController@reportIssue');

                Route::post('getUserTime', 'API\v9\UserController@getUserTime');

                Route::post('stillWorkingUpdate', 'API\v9\UserController@stillWorkingUpdate');

                Route::post('uploadViolationImage', 'API\v9\UserController@uploadViolationImage');

                Route::post('clockInClockOut', 'API\v9\UserController@clockInClockOut');

                Route::post('routeCheck', 'API\v9\UserController@routeCheck');

                Route::post('clockInClockOutDetail', 'API\v9\UserController@clockInClockOutDetail');

                Route::post('logout', 'API\v9\UserController@logout');
            }
        );

        Route::post('forgotPassword', 'API\v9\UserController@forgotPassword');

        Route::post('login', 'API\v9\UserController@login');
    }
);


Route::group(
    [
        'prefix' => 'v8',
    ],
    function () {
        Route::middleware(['auth:api'])->group(
            function () {
                Route::post('issueReportCategory', 'API\v8\UserController@ticketCategory');

                Route::post('issueReport', 'API\v8\UserController@issueReport');

                Route::post('reRoute', 'API\v8\UserController@reRoute');
                
                Route::post('updateNote', 'API\v8\UserController@updateNote');

                Route::post('getNoteByUnit', 'API\v8\UserController@getNoteByUnit');

                Route::post('userProfile', 'API\v8\UserController@userProfile');

                Route::post('updateProfile', 'API\v8\UserController@updateProfile');

                Route::post('propertyDetail', 'API\v8\UserController@getPropertyDetail');

                Route::post('subProperties', 'API\v8\UserController@subProperties');

                Route::post('subProDetail', 'API\v8\UserController@subProDetail');

                Route::post('propertiesListV2', 'API\v8\UserController@propertiesListV2');

                Route::post('propertyCheckIn', 'API\v8\UserController@propertyCheckIn');

                Route::post('getActivityLog', 'API\v8\UserController@getActivityLog');

                Route::post('scanQrcode', 'API\v8\UserController@scanQrcode');

                Route::post('scanQrcodeV2', 'API\v8\UserController@scanQrcodeV2');

                Route::post('activateCode', 'API\v8\UserController@activateCode');

                Route::post('activateCodeV2', 'API\v8\UserController@activateCodeV2');

                Route::post('createViolation', 'API\v8\UserController@createViolation');

                Route::post('createViolationV1', 'API\v8\UserController@createViolationV1');

                Route::post('reportViolation', 'API\v8\UserController@reportViolation');

                Route::post('pickUp', 'API\v8\UserController@pickUp');

                Route::post('manuallyPickUp', 'API\v8\UserController@manuallyPickUp');

                Route::post('pickUp_V2', 'API\v8\UserController@pickUpV2');

                Route::post('scanRevockBarcode', 'API\v8\UserController@scanRevockBarcode');

                Route::post('note', 'API\v8\UserController@note');

                Route::post('noteReason', 'API\v8\UserController@noteReason');

                Route::post('changePassword', 'API\v8\UserController@changePassword');

                Route::post('getEmployeschedule', 'API\v8\UserController@getEmployeschedule');

                Route::post('getEmployescheduleV2', 'API\v8\UserController@getEmployescheduleV2');

                Route::post('getEmployescheduleV3', 'API\v8\UserController@getEmployescheduleV3');

                Route::post('getEmployescheduleV4', 'API\v8\UserController@getEmployescheduleV4');

                Route::post('workPlanFillterApi', 'API\v8\UserController@workPlanFillterApi');

                Route::post('workPlanFillterApiV2', 'API\v8\UserController@workPlanFillterApiV2');

                Route::post('addNoteSchedule', 'API\v8\UserController@addNoteSchedule');

                Route::post('addNoteScheduleV2', 'API\v8\UserController@addNoteScheduleV2');

                Route::post('addNoteScheduleV3', 'API\v8\UserController@addNoteScheduleV3');

                Route::post('walkThrough', 'API\v8\UserController@walkThrough');

                Route::post('getReportIssueReason', 'API\v8\UserController@getReportIssueReason');

                Route::post('reportIssue', 'API\v8\UserController@reportIssue');

                Route::post('getUserTime', 'API\v8\UserController@getUserTime');

                Route::post('stillWorkingUpdate', 'API\v8\UserController@stillWorkingUpdate');

                Route::post('uploadViolationImage', 'API\v8\UserController@uploadViolationImage');

                Route::post('clockInClockOut', 'API\v8\UserController@clockInClockOut');

                Route::post('routeCheck', 'API\v8\UserController@routeCheck');

                Route::post('clockInClockOutDetail', 'API\v8\UserController@clockInClockOutDetail');

                Route::post('logout', 'API\v8\UserController@logout');
            }
        );

        Route::post('forgotPassword', 'API\v8\UserController@forgotPassword');

        Route::post('login', 'API\v8\UserController@login');
    }
);

Route::group(
    [
        'prefix' => 'v7',
    ],
    function () {
        Route::middleware(['auth:api'])->group(
            function () {
                Route::post('updateNote', 'API\v7\UserController@updateNote');

                Route::post('getNoteByUnit', 'API\v7\UserController@getNoteByUnit');

                Route::post('userProfile', 'API\v7\UserController@userProfile');

                Route::post('updateProfile', 'API\v7\UserController@updateProfile');

                Route::post('propertyDetail', 'API\v7\UserController@getPropertyDetail');

                Route::post('subProperties', 'API\v7\UserController@subProperties');

                Route::post('subProDetail', 'API\v7\UserController@subProDetail');

                Route::post('propertiesListV2', 'API\v7\UserController@propertiesListV2');

                Route::post('propertyCheckIn', 'API\v7\UserController@propertyCheckIn');

                Route::post('getActivityLog', 'API\v7\UserController@getActivityLog');

                Route::post('scanQrcode', 'API\v7\UserController@scanQrcode');

                Route::post('scanQrcodeV2', 'API\v7\UserController@scanQrcodeV2');

                Route::post('activateCode', 'API\v7\UserController@activateCode');

                Route::post('activateCodeV2', 'API\v7\UserController@activateCodeV2');

                Route::post('createViolation', 'API\v7\UserController@createViolation');

                Route::post('createViolationV1', 'API\v7\UserController@createViolationV1');

                Route::post('reportViolation', 'API\v7\UserController@reportViolation');

                Route::post('pickUp', 'API\v7\UserController@pickUp');

                Route::post('manuallyPickUp', 'API\v7\UserController@manuallyPickUp');

                Route::post('pickUp_V2', 'API\v7\UserController@pickUp_V2');

                Route::post('scanRevockBarcode', 'API\v7\UserController@scanRevockBarcode');

                Route::post('note', 'API\v7\UserController@note');

                Route::post('noteReason', 'API\v7\UserController@note_reason');

                Route::post('changePassword', 'API\v7\UserController@changePassword');

                Route::post('getEmployeschedule', 'API\v7\UserController@getEmployeschedule');

                Route::post('getEmployescheduleV2', 'API\v7\UserController@getEmployescheduleV2');

                Route::post('getEmployescheduleV3', 'API\v7\UserController@getEmployescheduleV3');

                Route::post('getEmployescheduleV4', 'API\v7\UserController@getEmployescheduleV4');

                Route::post('workPlanFillterApi', 'API\v7\UserController@workPlanFillterApi');

                Route::post('workPlanFillterApiV2', 'API\v7\UserController@workPlanFillterApiV2');

                Route::post('addNoteSchedule', 'API\v7\UserController@addNoteSchedule');

                Route::post('addNoteScheduleV2', 'API\v7\UserController@addNoteScheduleV2');

                Route::post('addNoteScheduleV3', 'API\v7\UserController@addNoteScheduleV3');

                Route::post('walkThrough', 'API\v7\UserController@walkThrough');

                Route::post('getReportIssueReason', 'API\v7\UserController@getReportIssueReason');

                Route::post('reportIssue', 'API\v7\UserController@reportIssue');

                Route::post('getUserTime', 'API\v7\UserController@getUserTime');

                Route::post('stillWorkingUpdate', 'API\v7\UserController@stillWorkingUpdate');

                Route::post('uploadViolationImage', 'API\v7\UserController@uploadViolationImage');

                Route::post('clockInClockOut', 'API\v7\UserController@clockInClockOut');

                Route::post('clockInClockOutDetail', 'API\v7\UserController@clockInClockOutDetail');

                Route::post('logout', 'API\v7\UserController@logout');
            }
        );

        Route::post('forgotPassword', 'API\v7\UserController@forgotPassword');

        Route::post('login', 'API\v7\UserController@login');
    }
);

Route::group(
    [
        'prefix' => 'v6',
    ],
    function () {
        Route::middleware(['auth:api'])->group(
            function () {
                Route::post('userProfile', 'API\v6\UserController@userProfile');

                Route::post('updateProfile', 'API\v6\UserController@updateProfile');

                Route::post('propertyDetail', 'API\v6\UserController@getPropertyDetail');

                Route::post('propertiesList', 'API\v6\UserController@propertiesList');

                Route::post('propertiesListV2', 'API\v6\UserController@propertiesListV2');

                Route::post('propertyCheckIn', 'API\v6\UserController@propertyCheckIn');

                Route::post('getActivityLog', 'API\v6\UserController@getActivityLog');

                Route::post('scanQrcode', 'API\v6\UserController@scanQrcode');

                Route::post('scanQrcodeV2', 'API\v6\UserController@scanQrcodeV2');

                Route::post('activateCode', 'API\v6\UserController@activateCode');

                Route::post('activateCodeV2', 'API\v6\UserController@activateCodeV2');

                Route::post('createViolation', 'API\v6\UserController@createViolation');

                Route::post('createViolationV1', 'API\v6\UserController@createViolationV1');

                Route::post('reportViolation', 'API\v6\UserController@reportViolation');

                Route::post('pickUp', 'API\v6\UserController@pickUp');

                Route::post('manuallyPickUp', 'API\v6\UserController@manuallyPickUp');

                Route::post('pickUp_V2', 'API\v6\UserController@pickUp_V2');

                Route::post('scanRevockBarcode', 'API\v6\UserController@scanRevockBarcode');

                Route::post('note', 'API\v6\UserController@note');

                Route::post('noteReason', 'API\v6\UserController@note_reason');

                Route::post('changePassword', 'API\v6\UserController@changePassword');

                Route::post('getEmployeschedule', 'API\v6\UserController@getEmployeschedule');

                Route::post('getEmployescheduleV2', 'API\v6\UserController@getEmployescheduleV2');

                Route::post('getEmployescheduleV3', 'API\v6\UserController@getEmployescheduleV3');

                Route::post('workPlanFillterApi', 'API\v6\UserController@workPlanFillterApi');

                Route::post('workPlanFillterApiV2', 'API\v6\UserController@workPlanFillterApiV2');

                Route::post('addNoteSchedule', 'API\v6\UserController@addNoteSchedule');

                Route::post('addNoteScheduleV2', 'API\v6\UserController@addNoteScheduleV2');

                Route::post('addNoteScheduleV3', 'API\v6\UserController@addNoteScheduleV3');

                Route::post('walkThrough', 'API\v6\UserController@walkThrough');

                Route::post('getReportIssueReason', 'API\v6\UserController@getReportIssueReason');

                Route::post('reportIssue', 'API\v6\UserController@reportIssue');

                Route::post('getUserTime', 'API\v6\UserController@getUserTime');

                Route::post('uploadViolationImage', 'API\v6\UserController@uploadViolationImage');
            }
        );

        Route::post('forgotPassword', 'API\v6\UserController@forgotPassword');

        Route::post('login', 'API\v6\UserController@login');
    }
);

Route::group(
    [
        'prefix' => 'v5',
    ],
    function () {
        Route::middleware(
            [
                'auth:api',
            ]
        )->group(
            function () {
                Route::post('userProfile', 'API\v5\UserController@userProfile');

                Route::post('updateProfile', 'API\v5\UserController@updateProfile');

                Route::post('propertyDetail', 'API\v5\UserController@getPropertyDetail');

                Route::post('propertiesList', 'API\v5\UserController@propertiesList');

                Route::post('propertiesListV2', 'API\v5\UserController@propertiesListV2');

                Route::post('propertyCheckIn', 'API\v5\UserController@propertyCheckIn');

                Route::post('getActivityLog', 'API\v5\UserController@getActivityLog');

                Route::post('scanQrcode', 'API\v5\UserController@scanQrcode');

                Route::post('scanQrcodeV2', 'API\v5\UserController@scanQrcodeV2');

                Route::post('activateCode', 'API\v5\UserController@activateCode');

                Route::post('activateCodeV2', 'API\v5\UserController@activateCodeV2');

                Route::post('createViolation', 'API\v5\UserController@createViolation');

                Route::post('createViolationV1', 'API\v5\UserController@createViolationV1');

                Route::post('reportViolation', 'API\v5\UserController@reportViolation');

                Route::post('pickUp', 'API\v5\UserController@pickUp');

                Route::post('manuallyPickUp', 'API\v5\UserController@manuallyPickUp');

                Route::post('pickUp_V2', 'API\v5\UserController@pickUp_V2');

                Route::post('scanRevockBarcode', 'API\v5\UserController@scanRevockBarcode');

                Route::post('note', 'API\v5\UserController@note');

                Route::post('noteReason', 'API\v5\UserController@note_reason');

                Route::post('changePassword', 'API\v5\UserController@changePassword');

                Route::post('getEmployeschedule', 'API\v5\UserController@getEmployeschedule');

                Route::post('getEmployescheduleV2', 'API\v5\UserController@getEmployescheduleV2');

                Route::post('getEmployescheduleV3', 'API\v5\UserController@getEmployescheduleV3');

                Route::post('workPlanFillterApi', 'API\v5\UserController@workPlanFillterApi');

                Route::post('workPlanFillterApiV2', 'API\v5\UserController@workPlanFillterApiV2');

                Route::post('addNoteSchedule', 'API\v5\UserController@addNoteSchedule');

                Route::post('addNoteScheduleV2', 'API\v5\UserController@addNoteScheduleV2');

                Route::post('addNoteScheduleV3', 'API\v5\UserController@addNoteScheduleV3');

                Route::post('walkThrough', 'API\v5\UserController@walkThrough');

                Route::post('getReportIssueReason', 'API\v5\UserController@getReportIssueReason');

                Route::post('reportIssue', 'API\v5\UserController@reportIssue');

                Route::post('getUserTime', 'API\v5\UserController@getUserTime');
            }
        );

        Route::post('forgotPassword', 'API\v5\UserController@forgotPassword');

        Route::post('login', 'API\v5\UserController@login');
    }
);
