<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AppSettingController extends BaseController
{
    public function __construct()
    {
        //$this->middleware('RoleAndPermission:admin');
        parent::__construct();
    }

    public function appSetting()
    {
        //Permission check (Only subscriber can access): Start
        // if ($this->user->is_admin != getAdminId()) {
        //     return redirect('unauthorized');
        // }
        //Permission check (Only subscriber can access): End
        $arr = [];
        //#1078: Unsubscribe from email notification : Start
        $notification = \App\UserNotification::select('email', 'sms', 'type')
            ->where('subscriber_id', $this->user->subscriber_id)
            ->where('user_id', $this->user->id)
            ->orderBy('type')
            ->get();
        
        foreach ($notification as $notify) {
            $arr[$notify->type] = $notify;
        }
        
        $this->data['notification'] = collect($arr);
        //dd($this->data['notification'][10]->type, $this->data['notification']);
        //#1078: Unsubscribe from email notification: End
        
        //#695: Manage Violations Report Functionality Enhancement: Start
        $appPermission = \App\AppPermission::where('subscriber_id', $this->user->subscriber_id)
        ->where('user_id', $this->user->id)
        ->first();

        $this->data['appPermission'] = $appPermission;
        //#695: Manage Violations Report Functionality Enhancement: End

        //#1263: Automated Service Report Syndication : Start
          $serviceReport = \App\UserNotification::select('day_frequency')
          ->where('subscriber_id', $this->user->subscriber_id)
          ->where('user_id', $this->user->id)
          ->where('type', 7)
          ->first();

        $this->data['serviceReport'] = $serviceReport;
        //#1263: Automated Service Report Syndication: End

        //#1314: Automated Report for Bin Tag List: Start
        $serviceUnitReport = \App\UserNotification::select('day_frequency')
        ->where('subscriber_id', $this->user->subscriber_id)
        ->where('user_id', $this->user->id)
        ->where('type', 8)
        ->first();

        $this->data['serviceUnitReport'] = $serviceUnitReport;
        //#1314: Automated Report for Bin Tag List: End
        
        //#1352: Automated Clock in/out report: Start
        $clockInOutReport = \App\UserNotification::select('day_frequency')
        ->where('subscriber_id', $this->user->subscriber_id)
        ->where('user_id', $this->user->id)
        ->where('type', 9)
        ->first();

        $this->data['clockInOutReport'] = $clockInOutReport;
        //#1352: Automated Clock in/out report: End

         //#1263: Automated Violation Report : Start
         $violationReport = \App\UserNotification::select('day_frequency')
         ->where('subscriber_id', $this->user->subscriber_id)
         ->where('user_id', $this->user->id)
         ->where('type', 10)
         ->first();

        $this->data['violationReport'] = $violationReport;

        #1614: Property Checkout Notification
        $checkOut = \App\UserNotification::select('day_frequency')
         ->where('subscriber_id', $this->user->subscriber_id)
         ->where('user_id', $this->user->id)
         ->where('type', 11)
         ->first();
    
        $this->data['checkOut'] = $checkOut;
        #1614: Property Checkout Notification

       return view('appsetting', $this->data);
    }

    public function defaultEmployeeSchedule(Request $request)
    {
        \App\Subscriber::where(
            [
                'id' => $this->user->subscriber_id,
            ]
        )
        ->update(
            [
                'service_in_time' => \Carbon\Carbon::parse($request->serviceInTime)->toTimeString(),
                'service_out_time' => \Carbon\Carbon::parse($request->serviceOutTime)->toTimeString(),
            ]
        );

        return redirect()->back()
            ->with(
                'status',
                [
                    'title' => 'App Setting',
                    'text' => 'You have successfully setup default employee schedule.',
                    'class' => 'success',
                ]
            );
    }

    public function dashboardSetting(Request $request)
    {
        //#695: Manage Violations Report Functionality Enhancement

        \App\AppPermission::updateOrCreate(
            [
                'subscriber_id' => $this->user->subscriber_id,
                'user_id' => $this->user->id,
            ],
            [
                'daliy_task_complete' => !empty($request->daliy_task_complete) ? 1 : 0,
                //'recycling_collected' => !empty($request->recycling_collected) ? 1 : 0,
                'units_serviced' => !empty($request->units_serviced) ? 1 : 0,
                'checkin_pending' => !empty($request->checkin_pending) ? 1 : 0,
                'violation' => !empty($request->violation) ? 1 : 0,
                'subscriber_id' => $this->user->subscriber_id,
                'user_id' => $this->user->id,
            ]
        );

        return redirect()->back()
            ->with(
                'status',
                [
                    'title' => 'App Setting',
                    'text' => 'Dashboard setting updated successfully.',
                    'class' => 'success',
                ]
            );
    }

    public function notificationSetting(Request $request)
    {
        foreach ($request->all() as $req) {
            if (is_array($req)) {
                $reques = (object) $req;

                \App\UserNotification::updateOrCreate(
                    [
                        'user_id' => $this->user->id,
                        'subscriber_id' => $this->user->subscriber_id,
                        'type' => $reques->type,
                    ],
                    [
                        'user_id' => $this->user->id,
                        'email' => !empty($reques->email) ? $reques->email : 0,
                        'sms' => !empty($reques->sms) ? $reques->sms : 0,
                        'type' => $reques->type,
                    ]
                );
            }
        }

        return redirect()->back()
            ->with(
                'status',
                [
                    'title' => 'App Setting',
                    'text' => 'Notification setting updated successfully.',
                    'class' => 'success',
                ]
            );
    }

    /*
    *
    * Task: #1263: Automated Service Report Syndication
    *
    */

    public function automatedServiceReport(Request $request)
    {
        \App\UserNotification::updateOrCreate(
            [
                'user_id' => $this->user->id,
                'subscriber_id' => $this->user->subscriber_id,
                'type' => 7,
            ],
            [
                'user_id' => $this->user->id,
                'day_frequency' => !empty($request->onoff) ? $request->frequency : 0,
                'type' => 7,
            ]
        );

        #1314: Automated Report for Bin Tag List:Start
        \App\UserNotification::updateOrCreate(
            [
                'user_id' => $this->user->id,
                'subscriber_id' => $this->user->subscriber_id,
                'type' => 8,
            ],
            [
                'user_id' => $this->user->id,
                'day_frequency' => !empty($request->onoffExcel) ? $request->frequency : 0,
                'type' => 8,
            ]
        );
        #1314: Automated Report for Bin Tag List: End

        #1352: Automated Clock in/out report:Start
        \App\UserNotification::updateOrCreate(
            [
                'user_id' => $this->user->id,
                'subscriber_id' => $this->user->subscriber_id,
                'type' => 9,
            ],
            [
                'user_id' => $this->user->id,
                'day_frequency' => !empty($request->onoffclockIn) ? $request->frequency : 0,
                'type' => 9,
            ]
        );
        #1352: Automated Clock in/out report: End

        #1352: Automated violation report: Start
        \App\UserNotification::updateOrCreate(
            [
                'user_id' => $this->user->id,
                'subscriber_id' => $this->user->subscriber_id,
                'type' => 10,
            ],
            [
                'user_id' => $this->user->id,
                'day_frequency' => !empty($request->onoffViolation) ? $request->frequency : 0,
                'type' => 10,
            ]
        );
        #1352: Automated violation report: End
        
         


        return redirect()->back()
            ->with(
                'status',
                [
                    'title' => 'App Setting',
                    'text' => 'Automated Report setting updated successfully.',
                    'class' => 'success',
                ]
            );
    }
}
