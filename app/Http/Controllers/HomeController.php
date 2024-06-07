<?php

namespace App\Http\Controllers;

use App\Property;
use App\Service;
use App\Units;
use App\User;
use App\Violation;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        //  $this->middleware('RoleAndPermission:customers')
        //  ->only(['index', 'create', 'create', 'store', 'edit', 'update', 'destroy']);
        // if ($this->user->is_admin != 1) {
        //     return redirect('home');
        //  }
    }

    public function index()
    {
        $arr[] = 0;

        if ($this->user->role_id == getPropertyManageId()) {
            // $this->data['ifPropertiesManager'] = 0;
            // $this->data['vioName'] = 'Submitted';
            // $this->data['url'] = url('property-manager/violation');
            $unitservice = [];

            //#1047: Property Manger Portal - Drill down on units serviced Comment #3
            // $start = \Carbon\Carbon::parse($this->usertime->startTime)->subDays(1)->addHours(10)->toDateTimeString();

            // $end = \Carbon\Carbon::parse($this->usertime->endTime)->subDays(1)->addHours(10)->toDateTimeString();

            $start = $this->usertime->startTime;
            $end = $this->usertime->endTime;

            //#695: Manage Violations Report Functionality Enhancement: Start
            $appPermission = \App\AppPermission::where('subscriber_id', $this->user->subscriber_id)
                ->where('user_id', $this->user->id)
                ->first();

            $this->data['appPermission'] = $appPermission;
            //#695: Manage Violations Report Functionality Enhancement: End

            $property = $this->propertyList()
                ->withCount(
                    [
                        'getUnit' => function ($query) {
                            $query->where('is_route', 0);
                        },
                        'checkInProperty' => function ($query) use ($start, $end) {
                            $query->whereBetween(
                                \DB::raw("convert_tz(created_at,'UTC','" . getUserTimezone() . "')"),
                                [
                                    $this->usertime->startTime,
                                    $this->usertime->endTime,
                                ]
                            );
                        },
                    ]
                )
                ->with(
                    [
                        'getUnit' => function ($query) {
                            $query->where('is_route', 0);
                        },
                        'service',
                    ]
                )
                ->whereHas(
                    'service',
                    function ($query) {
                        $query->where('pickup_start', '<=', $this->usertime->startTime)
                            ->where('pickup_finish', '>=', $this->usertime->endTime);
                    }
                )
            ->get();

            $userProperty = $property;
            $unitsArray = [];

            //Number Of Units Serviced :Start
            if (!is_null($appPermission) && !empty($appPermission->units_serviced))
            {
                foreach ($property as $prope) {
                    $activity = \App\Activitylogs::query()
                        ->where(
                            function ($query) {
                                $query->where('wast', 1)
                                    ->orWhere('recycle', 1);
                            }
                        )
                        ->where('type', 2)
                        ->whereIn('barcode_id', $prope->getUnit->pluck('barcode_id'))
                        ->whereBetween(
                            \DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                            [
                                $start,
                                $end,
                            ]
                        )
                    ->get();

                $unitservice[] = $activity->count();
                $this->data['unitservice'] = array_sum($unitservice);
            }
        }
            //Number Of Units Serviced : End
       
            //Violation Count For Property Manager: Start
            if (!is_null($appPermission) && !empty($appPermission->violation))
            {
                $proViolation = \App\Violation::whereIn(
                    'barcode_id',
                    function ($query) use ($property) {
                        $query->select('barcode_id')
                            ->from('units')
                            ->whereIn('property_id', $property->pluck('id'))
                            ->whereNull('deleted_at');
                    }
                )
                ->whereBetween(
                    DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                    [
                        $start,
                        $end,
                    ]
                )
                ->whereIn('manager_status', [0])
                ->get();

                $this->data['proViolation'] = $proViolation->count();
            }
            //Violation Count For Property Manager: End

            //Violation Count For Property Manager: Start
            if (!is_null($appPermission) && !empty($appPermission->daliy_task_complete))
            {
                $task = \App\Activitylogs::query()
                    ->where('type', 13)
                    ->whereIn('property_id', $property->pluck('id'))
                    ->whereBetween(
                        DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                        [
                            $start,
                            $end,
                        ]
                    )
                ->get();

                $this->data['proTask'] = $task->count();
            }
            //Violation Count For Property Manager: End

            //Check in count: Start
            if (!is_null($appPermission) && !empty($appPermission->checkin_pending))
            {
                $checkInCount = $property;

                $checkinPending = $checkInCount->where('check_in_property_count', 0)
                    ->count();

                $this->data['checkInCount'] = $checkinPending;
            }
            //Check in count: End

            $rCheckpoint = \App\Activitylogs::query()
                    ->where('type', 11)
                    ->whereIn('property_id', $property->pluck('id'))
                    ->whereBetween(
                        DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                        [
                            $start,
                            $end,
                        ]
                    )
                ->get();

            $this->data['rCheckpoint'] = $rCheckpoint->count();
        }

        return view('home', $this->data);
    }

    private function pendingCheckIn()
    {
        $property = $this->propertyList();

        $checkInCount = $property->whereHas(
            'getUnit',
            function ($query) {
                $query->where('is_active', 1)
                    ->where('is_route', 0);
            }
        )
        ->whereHas(
            'service',
            function ($query) {
                $query->where('pickup_start', '<=', $this->usertime->startTime)
                    ->where('pickup_finish', '>=', $this->usertime->endTime);
            }
        )
        ->withCount(
            [
                'checkInProperty' => function ($query) {
                    $query->whereBetween(
                        \DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                        [
                            $this->usertime->startTime,
                            $this->usertime->endTime,
                        ]
                    );
                },
            ]
        )
        ->get();

        $checkingPending = $checkInCount->where('check_in_property_count', 0)
            ->count();

        $this->data['propertyCheckIn'] = $checkingPending;
    }

    private function serviceQualityScore()
    {
        $bulidingCount = $sumWalkThroughDone = $sumWalkThroughNotDone = $total = 0;
        $arr = $totalBuilding = $walkThroughDone = $excPropertyDate = $excBuildingDate = $excBuildingid = $buildId = [];

        $getProperty = $this->propertyList();
        //Get service start date: Start
        $startDate = \App\Service::select('pickup_start')
            ->whereIn('property_id', $getProperty->pluck('id'))
            ->orderBy('pickup_start')->first();
        //Get service start date: End
        //Get property detail: Start
        $properties = $getProperty
            ->whereHas(
                'getUnit',
                function ($query) {
                    $query->select('id')
                        ->where('is_active', 1)
                        ->where('is_route', 0);
                }
            )
            ->withCount('getBuildingIsActiveUnit')
            ->with(
                [
                    'todayHasProperty' => function ($query) {
                        $query->select('id', 'property_id', 'day');
                    },
                    'getBuildingIsActiveUnit' => function ($query) {
                        $query->select('id', 'property_id');
                    },
                ]
            )
        ->get();
        //Get property detail: End
        //dd($properties);

        if (isset($properties) && $properties->isNotEmpty()) {
            $startOfWeek = \Carbon\Carbon::parse($startDate->pickup_start)
                    ->format('Y-m-d H:i:s');

            $endOfWeek = \Carbon\Carbon::now()->addDays(1)
                    ->format('Y-m-d') . ' 05:59:59';

            $userStartTime = \Carbon\Carbon::now()
                    ->timezone(getUserTimezone())->format('Y-m-d H:i:s');

            foreach ($properties as $property) {
                //Get property days: Start
                $todayHasProperty = $property->todayHasProperty;

                $day = $todayHasProperty->pluck('day');
                //Get property days: End

                $service = $property->service;

                $serviceStartDate = \Carbon\Carbon::parse($service->pickup_start)
                        ->format('Y-m-d H:i:s');

                $serviceEndDate = \Carbon\Carbon::parse($service->pickup_finish)
                        ->format('Y-m-d H:i:s');

                while ($startOfWeek <= $endOfWeek) {
                    $dayNumber = $this->getCurrentDayCount($startOfWeek);

                    $startEnd = \Carbon\Carbon::parse($startOfWeek)->addDays(1)
                            ->format('Y-m-d') . ' 05:59:59';

                    if (isset($service) && in_array($dayNumber, $day->toArray())
                         && ($serviceStartDate <= $startOfWeek
                            && $serviceEndDate >= $startEnd)
                                && ($startOfWeek <= $userStartTime
                                    && $startEnd >= $startOfWeek)) {
                        $excludedProperties = \App\ExcludedProperty::select('id')
                                ->where('property_id', $property->id)
                                ->whereBetween(
                                    DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                                    [$startOfWeek, $startEnd]
                                )
                                ->get();

                        if ($excludedProperties->isEmpty()) {
                            if ($property->type == 1 || $property->type == 3
                                || $property->type == 4) {
                                $walkThrough = \App\walkThroughRecord::select('id')
                                    ->where('property_id', $property->id)
                                    ->whereBetween(
                                        DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                                        [$startOfWeek, $startEnd]
                                    )->get();

                                $totalBuilding[] = 1;
                                $walkThroughDone[] = $walkThrough->count();
                            } elseif ($property->type == 2) {
                                $getBuildings = $property->getBuildingIsActiveUnit;

                                foreach ($getBuildings as $getBuilding) {
                                    $excbuild = \App\ExcludedProperty::select('id')
                                            ->where('property_id', $property->id)
                                            ->where('building_id', $getBuilding->id)
                                            ->whereBetween(
                                                DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                                                [$startOfWeek, $startEnd]
                                            )
                                            ->get();

                                    if ($excbuild->isEmpty()) {
                                        $walkThrough = \App\walkThroughRecord::select('id')
                                            ->where('property_id', $property->id)
                                            ->where('building_id', $getBuilding->id)
                                            ->whereBetween(
                                                DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                                                [
                                                    $startOfWeek,
                                                    $startEnd
                                                ]
                                            )
                                        ->get();

                                        $totalBuilding[] = 1;
                                        $walkThroughDone[] = $walkThrough->count();
                                    }
                                }
                            }
                        }

                        $excludedProperties = \App\ExcludedProperty::select('id')
                                ->where('property_id', $property->id)
                                ->where(
                                    function ($query) {
                                        $query->where('building_id', '!=', '0')
                                            ->whereNotNull('building_id');
                                    }
                                )
                                ->whereBetween(
                                    DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                                    [$startOfWeek, $startEnd]
                                )
                                ->get();

                        if ($excludedProperties->isNotEmpty()) {
                            $getBuildings = $property->getBuildingIsActiveUnit;

                            foreach ($getBuildings as $getBuilding) {
                                $excbuild = \App\ExcludedProperty::select('id')
                                    ->where('property_id', $property->id)
                                    ->where('building_id', $getBuilding->id)
                                    ->whereBetween(
                                        DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                                        [$startOfWeek, $startEnd]
                                    )->get();

                                if ($excbuild->isEmpty()) {
                                    $walkThrough = \App\walkThroughRecord::select('id')
                                        ->where('property_id', $property->id)
                                        ->where('building_id', $getBuilding->id)
                                        ->whereBetween(
                                            DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                                            [
                                                $startOfWeek,
                                                $startEnd
                                            ]
                                        )
                                    ->get();

                                    $totalBuilding[] = 1;
                                    $walkThroughDone[] = $walkThrough->count();
                                }
                            }
                        }
                    }

                    $startOfWeek = \Carbon\Carbon::parse($startOfWeek)->addDays(1);
                }

                $startOfWeek = \Carbon\Carbon::parse($startDate->pickup_start);
            }

            $sumWalkThroughDone = array_sum($walkThroughDone);
            $sumWalkThroughNotDone = array_sum($totalBuilding);

            if ($sumWalkThroughDone != 0 && $sumWalkThroughNotDone != 0) {
                $total = ($sumWalkThroughDone / $sumWalkThroughNotDone) * 100;
            }
        }

        $this->data['workPerDay'] = number_format((float) $total, 1, '.', '');
    }

    public function daliyStatus(Request $request)
    {
        $merged = collect([2,3,4,5,6,7,8,9,10,11]);
        $daliyStatus = [$this->user->daliy_status];
        $unchecked = [];

        if ($request->isUpdate) {
            $status = !is_null($request->id) ? $request->id : [2,3,4,5,6];
                
            if (!is_null($request->id)) {
                foreach ($merged as $merge) {
                    if (!in_array($merge, $request->id)) {
                        $unchecked[] = $merge;
                    }
                }
            }

            \App\User::where('id', $this->user->id)
                ->update(
                    [
                        //'daliy_status' => json_encode($request->id)
                        'daliy_status' => is_null($request->id) ? null : collect($request->id)->implode(',')
                    ]
                );

            return response()
                ->json(
                    [
                        'message' => 'success',
                        'checked' => $request->id,
                        'unchecked' => !empty($unchecked) ? $unchecked : $daliyStatus
                    ]
                );
        } else {
            //Call when page load.
            $status = !is_null($this->user->daliy_status)
                ? explode(',', $this->user->daliy_status) : [0,1];

            foreach ($merged as $merge) {
                if (!in_array($merge, $status)) {
                    $unchecked[] = $merge;
                }
            }

            return response()
                ->json(
                    [
                        'message' => 'success',
                        'checked' => $status,
                        'unchecked' => $unchecked
                    ]
                );
        }
    }

    public function daliyReportRemote(Request $request)
    {
        $delivery2 = [];
        $dates = [];
        $start = \Carbon\Carbon::parse($request->startTime, getUserTimezone())->copy();
        $end = \Carbon\Carbon::parse($request->endTime, getUserTimezone())->copy();
        $current = strtotime($start);
        $end = strtotime($end);
        $daliyStatus = !is_null($this->user->daliy_status)
            ? explode(',', $this->user->daliy_status) : null;

        while ($current <= $end) {
            $dates[] = date('Y-m-d', $current);
            $current = strtotime('+1 days', $current);
        }
        
        foreach ($dates as $date) {
            $startDate = \Carbon\Carbon::parse($date, getUserTimezone())->copy()->addHours(6)->toDateTimeString();
            $endDate = \Carbon\Carbon::parse($date, getUserTimezone())->copy()->addDays(1)->addHours(5)->addMinutes(59)->addSeconds(59)->toDateTimeString();
            
            $records = \App\DaliyRecords::query()
                ->where('property_id', $request->id)
                ->latest()
                ->whereBetween(
                    'record_date',
                    [
                        $startDate,
                        $endDate,
                    ]
                )
                ->with(
                    [
                        'property' => function ($query) {
                            $query->select('id', 'name');
                        }
                    ]
                )
            ->first();

            if (!is_null($records)) {
                $value = \Carbon\Carbon::parse($records->checkinout_duration)->format('H:i');
                $parts = explode(':', $value);
                $checkinoutDuration = $parts[0] + floor(($parts[1] / 60) * 100) / 100;
            }

            $delivery2[$startDate][] = date('m-d-Y', strtotime($startDate));

            if (in_array(2, $daliyStatus)) {
                $delivery2[$startDate][] = $records->pickup_completed ?? 0;
            }

            if (in_array(3, $daliyStatus)) {
                $delivery2[$startDate][] = $records->active_units ?? 0;
            }

            if (in_array(4, $daliyStatus)) {
                $delivery2[$startDate][] = $records->route_checkpoints_scanned ?? 0;
            }

            if (in_array(5, $daliyStatus)) {
                $delivery2[$startDate][] = $records->checkpoints_by_property ?? 0;
            }

            if (in_array(6, $daliyStatus)) {
                $delivery2[$startDate][] = $records->building_walk_throughs ?? 0;
            }

            if (in_array(7, $daliyStatus)) {
                $delivery2[$startDate][] = $records->active_building ?? 0;
            }

            if (in_array(8, $daliyStatus)) {
                $delivery2[$startDate][] = $checkinoutDuration ?? 0;
            }

            if (in_array(9, $daliyStatus)) {
                $delivery2[$startDate][] = $records->total_tasks_completed ?? 0;
            }

            if (in_array(10, $daliyStatus)) {
                $delivery2[$startDate][] = $records->total_tasks ?? 0;
            }

            if (in_array(11, $daliyStatus)) {
                $delivery2[$startDate][] = $records->missed_property_checkouts ?? 0;
            }
        }

        $arr[] = 'Genre';

        if (in_array(2, $daliyStatus)) {
            $arr[] = 'Units Serviced';
        }
        
        if (in_array(3, $daliyStatus)) {
            $arr[] = 'Total Active Units';
        }

        if (in_array(4, $daliyStatus)) {
            $arr[] = 'Route Checkpoints Completed';
        }

        if (in_array(5, $daliyStatus)) {
            $arr[] = 'Total Route Checkpoints';
        }

        if (in_array(6, $daliyStatus)) {
            $arr[] = 'Buildings Serviced';
        }

        if (in_array(7, $daliyStatus)) {
            $arr[] = 'Total Buildings';
        }

        if (in_array(8, $daliyStatus)) {
            $arr[] = 'Last Day Serviced Duration';
        }

        if (in_array(9, $daliyStatus)) {
            $arr[] = 'Tasks Completed';
        }

        if (in_array(10, $daliyStatus)) {
            $arr[] = 'Total Tasks';
        }

        if (in_array(11, $daliyStatus)) {
            $arr[] = 'Incomplete Service Checkouts';
        }

        $finalArray = array_values($delivery2);
        
        array_unshift($finalArray, $arr);

        $this->data['deliverychart'] = json_encode($finalArray);
        $this->data['tokenid'] = 4;

        return view('chartajax', $this->data);
    }

    public function chartajax(Request $request)
    {
        $start = date('Y-m-d', strtotime($request->formdate)); //start date
        $end = date('Y-m-d', strtotime($request->todate)); //end date

        $dates = [];
        $current = strtotime($start);
        $end = strtotime($end);

        while ($current <= $end) {
            $dates[] = date('Y-m-d', $current);
            $current = strtotime('+1 days', $current);
        }

        $user_ids = User::when(
            $this->user->role_id == 1,
            function ($query) {
                $query->where('subscriber_id', $this->user->subscriber_id);
            },
            function ($query) {
                $query->where('id', $this->user->id);
            }
        )
        ->pluck('id');
            
        if ($request->tokenid == 1) {
            $delivery2 = [];
            
            foreach ($dates as $daterow) {
                $pickupdatedata = $this->pickupWasteCal1($daterow, $user_ids);
                $recycledatedata = $this->recycleDeliveryWastCal1($daterow, $user_ids);
                $notedatedata = $this->notesWasteCal1($daterow, $user_ids);
                $violationdatedata = $this->violationWasteCal1($daterow, $user_ids);

                $delivery2[] = [
                    date('m-d-Y', strtotime($daterow)),
                    $pickupdatedata['pickup'],
                    $recycledatedata['recycles'],
                    $notedatedata['notes'],
                    $violationdatedata['violation'],
                ];
            }

            array_unshift($delivery2, ['Genre', 'Waste Collected', 'Recycle Collected', 'Notes', 'Violations']);
            $this->data['deliverychart'] = json_encode($delivery2);
            $this->data['tokenid'] = $request->tokenid;

            return view('chartajax', $this->data);
        } elseif ($request->tokenid == 2) {
            $recyclereport = [];
            $pro_id = str_replace('property/', '', $request->property);
            $first_day_this_month = date('Y-m-01 00:00:00'); // hard-coded '01' for first day
            $last_day_this_month = date('Y-m-d 12:59:59');
            $monthly_data = $this->recycleWasteCalMonthly($first_day_this_month, $last_day_this_month, $user_ids, $pro_id);
            if (empty($monthly_data)) {
                $monthly_data['average'] = 0;
            }
            foreach ($dates as $daterow) {
                $recycledata = $this->recycleWasteCal($daterow, $user_ids, $pro_id);
                $recyclereport[] = [
                    date('m-d-Y', strtotime($daterow)),
                    $recycledata['waste'],
                    $recycledata['recycle'],
                    $monthly_data['average'], ];
            }

            array_unshift($recyclereport, ['Genre', 'Waste Collected', 'Recycle Collected', 'Average',
            ]);

            $this->data['chartarr'] = json_encode($recyclereport);
            $this->data['tokenid'] = $request->tokenid;

            return view('chartajax', $this->data);
        } elseif ($request->tokenid == 3) {
            $newdate = [];
            foreach ($dates as $daterow) {
                $newdate[] = date('m-d-Y', strtotime($daterow));
            }

            if (empty($request->empid)) {
                $seriess = [];
                $loggedin_user = User::find($this->user->id);
                $employees = User::where('is_admin', 0)->where('subscriber_id', $loggedin_user->subscriber_id)->get();
                if (count($employees) > 0) {
                    $all_emp = [];
                    $count = $i = 1;
                    foreach ($employees as $employee) {
                        $all_emp = $employee->firstname;
                        if ($count == $i) {
                            $pickupcount_sunday = [];
                            foreach ($dates as $daterow) {
                                $pickupcount_sunday[] = $employee->logs()
                                        ->where('user_id', $employee->id)
                                        ->where('type', 2)
                                        ->whereDate('updated_at', $daterow)
                                        ->count();
                            }
                            ++$i;
                        }
                        $seriess[] = [
                            'name' => $all_emp,
                            'data' => $pickupcount_sunday,
                        ];
                        ++$count;
                    }
                }
            } else {
                $id = str_replace('employee/', '', $request->empid);
                $employee = User::find($id);
                $all_emp = $employee->firstname;
                $pickupcount_emp = [];
                foreach ($dates as $daterow) {
                    $pickupcount_emp[] = $employee->logs()->where('user_id', $employee->id)->where('type', 2)
                                    ->whereDate('updated_at', $daterow)->count();
                }

                $seriess[] = [
                    'name' => $all_emp,
                    'data' => $pickupcount_emp,
                ];
            }

            $this->data['chartseries'] = json_encode($seriess);
            $this->data['chart_dates'] = json_encode($newdate);
            $this->data['tokenid'] = $request->tokenid;

            return view('chartajax', $this->data);
        }
    }

    public function getEfficiencyLastWeek()
    {
        $user_detail = User::find($this->user->id);
        $user_ids = User::select('id')
                        ->where('subscriber_id', $user_detail->subscriber_id)
                        ->pluck('id');

        $previous_week = strtotime('-1 week +1 day');
        $start_week = strtotime('last sunday midnight', $previous_week);
        $monday = strtotime('monday', $start_week);
        $tuesday = strtotime('tuesday', $monday);
        $wednesday = strtotime('wednesday', $tuesday);
        $thursday = strtotime('thursday', $wednesday);
        $friday = strtotime('friday', $thursday);
        $end_week = strtotime('next saturday', $friday);

        $start_week = date('Y-m-d', $start_week);
        $monday = date('Y-m-d', $monday);
        $tuesday = date('Y-m-d', $tuesday);
        $wednesday = date('Y-m-d', $wednesday);
        $thursday = date('Y-m-d', $thursday);
        $friday = date('Y-m-d', $friday);
        $end_week = date('Y-m-d', $end_week);

        //////////////////02-05-2018////////////////////
        $pickupstart_week_data = $this->pickupWasteCal($start_week, $user_ids);
        $pickupmonday_data = $this->pickupWasteCal($monday, $user_ids);
        $pickuptuesday_data = $this->pickupWasteCal($tuesday, $user_ids);
        $pickupwednesday_data = $this->pickupWasteCal($wednesday, $user_ids);
        $pickupthursday_data = $this->pickupWasteCal($thursday, $user_ids);
        $pickupfriday_data = $this->pickupWasteCal($friday, $user_ids);
        $pickupend_week_data = $this->pickupWasteCal($end_week, $user_ids);

        $recyclestart_week_data = $this->recycleDeliveryWastCal($start_week, $user_ids);
        $recyclemonday_data = $this->recycleDeliveryWastCal($monday, $user_ids);
        $recycletuesday_data = $this->recycleDeliveryWastCal($tuesday, $user_ids);
        $recyclewednesday_data = $this->recycleDeliveryWastCal($wednesday, $user_ids);
        $recyclethursday_data = $this->recycleDeliveryWastCal($thursday, $user_ids);
        $recyclefriday_data = $this->recycleDeliveryWastCal($friday, $user_ids);
        $recycleend_week_data = $this->recycleDeliveryWastCal($end_week, $user_ids);

        $viostart_week_data = $this->violationWasteCal($start_week, $user_ids);
        $viomonday_data = $this->violationWasteCal($monday, $user_ids);
        $viotuesday_data = $this->violationWasteCal($tuesday, $user_ids);
        $viowednesday_data = $this->violationWasteCal($wednesday, $user_ids);
        $viothursday_data = $this->violationWasteCal($thursday, $user_ids);
        $viofriday_data = $this->violationWasteCal($friday, $user_ids);
        $vioend_week_data = $this->violationWasteCal($end_week, $user_ids);

        $notestart_week_data = $this->notesWasteCal($start_week, $user_ids);
        $notemonday_data = $this->notesWasteCal($monday, $user_ids);
        $notetuesday_data = $this->notesWasteCal($tuesday, $user_ids);
        $notewednesday_data = $this->notesWasteCal($wednesday, $user_ids);
        $notethursday_data = $this->notesWasteCal($thursday, $user_ids);
        $notefriday_data = $this->notesWasteCal($friday, $user_ids);
        $noteend_week_data = $this->notesWasteCal($end_week, $user_ids);

        $delivery = [['Genre', 'Waste Collected', 'Recycle Collected', 'Notes', 'Violations'],
            [
                date(
                    'm-d-Y',
                    strtotime($start_week)
                ),
                $pickupstart_week_data['pickup'],
                $recyclestart_week_data['recycles'],
                $notestart_week_data['notes'],
                $viostart_week_data['violation'], ],
            [
                date(
                    'm-d-Y',
                    strtotime($monday)
                ),
                $pickupmonday_data['pickup'],
                $recyclemonday_data['recycles'],
                $notemonday_data['notes'],
                $viomonday_data['violation'], ],
            [
                date(
                    'm-d-Y',
                    strtotime($tuesday)
                ),
                $pickuptuesday_data['pickup'],
                $recycletuesday_data['recycles'],
                $notetuesday_data['notes'],
                $viotuesday_data['violation'], ],
            [
                date(
                    'm-d-Y',
                    strtotime($wednesday)
                ),
                $pickupwednesday_data['pickup'],
                $recyclewednesday_data['recycles'],
                $notewednesday_data['notes'],
                $viowednesday_data['violation'], ],
            [
                date(
                    'm-d-Y',
                    strtotime($thursday)
                ),
                $pickupthursday_data['pickup'],
                $recyclethursday_data['recycles'],
                $notethursday_data['notes'],
                $viothursday_data['violation'], ],
            [
                date(
                    'm-d-Y',
                    strtotime($friday)
                ),
                $pickupfriday_data['pickup'],
                $recyclefriday_data['recycles'],
                $notefriday_data['notes'],
                $viofriday_data['violation'], ],
            [
                date(
                    'm-d-Y',
                    strtotime($end_week)
                ),
                $pickupend_week_data['pickup'],
                $recycleend_week_data['recycles'],
                $noteend_week_data['notes'],
                $vioend_week_data['violation'],
                ], ];

        $this->data['deliverychart'] = json_encode($delivery);
    }

    private function violation()
    {
        $status = $this->user->role_id == getPropertyManageId() ? [2, 3, 4, 5, 6] : [0];

        $property = $this->propertyList()->get();

        $violationCount = \App\Violation::whereIn(
            'barcode_id',
            function ($query) use ($property) {
                $query->select('barcode_id')
                    ->from('units')
                    ->whereIn('property_id', $property->pluck('id'))
                    ->whereNull('deleted_at');
            }
        )
        ->whereIn('status', $status)
        ->get();

        $this->data['violation'] = $violationCount->count();
    }

    private function total_employee()
    {
        for ($i = 4; $i > -1; --$i) {
            $days[] = \Carbon\Carbon::now()->subDays($i)->format('Y-m-d');
        }

        $empolyeId = [];
        $empolyee = User::when($this->user->role_id == 1, function ($query) {
            $query->where('subscriber_id', Auth::user()->subscriber_id);
        })
                ->when($this->user->role_id != 1, function ($query) {
                    $query->where('user_id', Auth::user()->id);
                })
                ->where('is_admin', '!=', 1)
                ->whereNotIn('role_id', [10])
                ->get();

        foreach ($days as $day) {
            foreach ($empolyee as $empolye) {
                $assignedProperty = $empolye->assignedproperties;

                foreach ($assignedProperty as $assignedId) {
                    $today = \Carbon\Carbon::parse($day)->format('Y-m-d') . ' 06:00:00';
                    $addDay = \Carbon\Carbon::parse($day)->addDay(1)->format('Y-m-d') . ' 05:59:59';
                    $dayNumber = \Carbon\Carbon::parse($day)->format('w');
                    $hasProperty = \App\PropertyFrequencies::select('day')
                            ->where('property_id', $assignedId->property_id)
                            ->where('day', $dayNumber)
                            ->get();

                    if (isset($assignedId->property_id) && $hasProperty->isNotEmpty()) {
                        $propertyType = $assignedId->getPropertyDetail->service->pickup_type;
                        $unit = $assignedId->getUnitDetail->where('is_active', 1)->where('is_route', 0);

                        foreach ($unit as $units) {
                            $pickCount = \App\Activitylogs::where('barcode_id', $units->barcode_id)
                                ->whereBetween(
                                    'created_at',
                                    [
                                        $today,
                                        $addDay
                                    ]
                                )
                                // ->when(
                                //     $propertyType == 1,
                                //     function ($query) {
                                //         $query->where('wast', 1);
                                //     }
                                // )
                                // ->when(
                                //     $propertyType == 2,
                                //     function ($query) {
                                //         $query->where('recycle', 1);
                                //     }
                                // )
                                // ->when(
                                //     $propertyType == 3,
                                //     function ($query) {
                                //         $query->where('wast', 1);
                                //         $query->where('recycle', 1);
                                //     }
                                // )
                                ->where(
                                    function ($query) {
                                        $query->where('wast', 1)
                                            ->orWhere('recycle', 1);
                                    }
                                )
                            ->get();

                            if ($pickCount->isEmpty()) {
                                $empolyeId[] = $empolye->id;
                                break 2;
                            }
                        }
                    }
                }
            }
        }
        //dd($empolyeId);
        $this->data['total_employee'] = count(array_unique($empolyeId));
    }

    private function pickedup_dates()
    {
        $subscriber_id = $this->subscriber_id;

        $activity = \App\Activitylogs::when(
            $this->user->role_id == 1,
            function ($query) use ($subscriber_id) {
                $query->where(
                    function ($query) use ($subscriber_id) {
                        $query->whereIn(
                            'user_id',
                            function ($query) use ($subscriber_id) {
                                $query->select('id')
                                    ->from('users')
                                    ->where('subscriber_id', $subscriber_id)
                                    ->whereNull('deleted_at');
                            }
                        )
                        ->orWhereIn(
                            'updated_by',
                            function ($query) use ($subscriber_id) {
                                $query->select('id')
                                    ->from('users')
                                    ->whereNull('deleted_at')
                                    ->where('subscriber_id', $subscriber_id);
                            }
                        );
                    }
                );
            }
        )
        ->when(
            $this->user->role_id != 1,
            function ($query) {
                $query->where('user_id', Auth::user()->id)
                    ->orWhere('updated_by', Auth::user()->id);
            }
        )
        ->whereIn('type', [2])
        ->get();

        $this->data['pickedup_dates'] = $activity->count();
    }

    private function pendingPickup()
    {
        $startDate = $this->usertime->startTime;
        $endDate = $this->usertime->endTime;

        $subscriber_id = $this->subscriber_id;
        $buildingPending = 0;

        $properties = Property::select('id', 'name', 'type')
            ->when(
                $this->user->role_id != 1,
                function ($query) {
                    $query->where(
                        function ($query) {
                            $query->whereIn(
                                'id',
                                function ($query) {
                                    $query->select('property_id')
                                    ->from('user_properties')
                                    ->where('user_id', $this->user->id)
                                    ->whereNull('deleted_at');
                                }
                            )
                            ->orWhereIn(
                                'id',
                                function ($query) {
                                    $query->select('id')
                                    ->from('properties')
                                    ->where('user_id', $this->user->id)
                                    ->whereNull('deleted_at');
                                }
                            );
                        }
                    );
                }
            )
            ->when(
                $this->user->role_id == 1,
                function ($query) {
                    $query->whereIn(
                        'id',
                        function ($query) {
                            $query->select('id')
                                ->from('properties')
                                ->where('subscriber_id', $this->user->subscriber_id)
                                ->whereNull('deleted_at');
                        }
                    );
                }
            )
            ->whereHas(
                'getUnit',
                function ($query) {
                    $query->where('is_active', 1)
                    ->where('is_route', 0);
                }
            )
            ->whereHas(
                'todayHasProperty',
                function ($query) {
                    $query->where('day', getCurrentDay());
                }
            )
            ->whereHas(
                'service',
                function ($query) use ($startDate, $endDate) {
                    $query->where('pickup_start', '<=', $startDate)
                        ->where('pickup_finish', '>=', $endDate);
                }
            )
            ->with('getBuildingIsActiveUnit')
            ->latest()
        ->get();
        
        $propertiesId = $properties->map(
            function ($val, $key) {
                return $val->id;
            }
        );

        foreach ($properties as $property) {
            $buildingId = !empty($property->getBuildingIsActiveUnit[0]->id) ? $property->getBuildingIsActiveUnit[0]->id : '';
            $units = $property->getUnit->where('is_active', 1)->where('is_route', 0);
            $service = $property->service;

            //Check Building Walkthrough: Start
            $checkWalkthrough = \App\Activitylogs::where('type', 8)
                    ->when(!empty($property) && ($property->type == 1 || $property->type == 4), function ($query) use ($property) {
                        $query->where('property_id', $property->id);
                    })
                    ->when($property->type == 3 && !empty($buildingId), function ($query) use ($buildingId, $property) {
                        $query->where('property_id', $property->id)
                        ->where('building_id', $buildingId);
                    })
                    ->whereBetween(DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"), [$startDate, $endDate])
                    ->get();
            //Check Building Walkthrough: End

            if ($checkWalkthrough->isNotEmpty() && $property->type != 2) {
                continue;
            }

            // dump($units, $property->id);
            if ($property->type == 1) {
                $checkBarcode = \App\Activitylogs::query()
                    // ->when(
                    //     $service->pickup_type == 1,
                    //     function ($query) {
                    //         $query->where('wast', 1);
                    //     }
                    // )
                    // ->when(
                    //     $service->pickup_type == 2,
                    //     function ($query) {
                    //         $query->where('recycle', 1);
                    //     }
                    // )
                    // ->when(
                    //     $service->pickup_type == 3,
                    //     function ($query) {
                    //         $query->where('recycle', 1)
                    //             ->where('wast', 1);
                    //     }
                    // )
                    ->where('wast', 1)
                    ->where('type', 2)
                    ->where('barcode_id', $units[0]->barcode_id)
                    ->whereBetween(
                        DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                        [
                            $startDate,
                            $endDate
                        ]
                    )
                ->get();

                if ($checkBarcode->isEmpty()) {
                    ++$buildingPending;
                }
            } elseif ($property->type == 3 || $property->type == 4) {
                $i = 0;
                $unitCount = $units->count();

                foreach ($units as $unit) {
                    $checkBarcode = \App\Activitylogs::query()
                        // ->when(
                        //     $service->pickup_type == 1,
                        //     function ($query) {
                        //         $query->where('wast', 1);
                        //     }
                        // )
                        // ->when(
                        //     $service->pickup_type == 2,
                        //     function ($query) {
                        //         $query->where('recycle', 1);
                        //     }
                        // )
                        // ->when(
                        //     $service->pickup_type == 3,
                        //     function ($query) {
                        //         $query->where('recycle', 1)
                        //             ->where('wast', 1);
                        //     }
                        // )
                        ->where(
                            function ($query) {
                                $query->where('wast', 1)
                                    ->orWhere('recycle', 1);
                            }
                        )
                        ->where('type', 2)
                        ->where('barcode_id', $unit->barcode_id)
                        ->whereBetween(
                            DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                            [
                                $startDate,
                                $endDate
                            ]
                        )
                    ->get();

                    if ($checkBarcode->isNotEmpty()) {
                        ++$i;
                    }
                }
                //echo $i . '!=' . $unitCount . "<br/>";
                if ($i != $unitCount) {
                    ++$buildingPending;
                }
            } elseif ($property->type == 2) {
                $buildings = $units->mapToGroups(function ($val, $key) {
                    if (!empty($val->id)) {
                        return [$val->building => $val];
                    } else {
                        return false;
                    }
                });

                foreach ($buildings as $building) {
                    //Check Building Walkthrough for Garden Style: Start
                    $checkWalkthrough = \App\Activitylogs::where('type', 8)
                            ->where('property_id', $property->id)
                            ->where('building_id', $building[0]->building_id)
                            ->whereBetween(DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"), [$startDate, $endDate])
                            ->get();
                    //Check Building Walkthrough for Garden Style: End
                    if ($checkWalkthrough->isNotEmpty()) {
                        continue;
                    }

                    $i = 0;
                    $unitCount = $building->count(); //Get active unit count

                    foreach ($building as $unit) {
                        $checkBarcode = \App\Activitylogs::query()
                            // ->when(
                            //     $service->pickup_type == 1,
                            //     function ($query) {
                            //         $query->where('wast', 1);
                            //     }
                            // )
                            // ->when(
                            //     $service->pickup_type == 2,
                            //     function ($query) {
                            //         $query->where('recycle', 1);
                            //     }
                            // )
                            // ->when(
                            //     $service->pickup_type == 3,
                            //     function ($query) {
                            //         $query->where('recycle', 1)
                            //             ->where('wast', 1);
                            //     }
                            // )
                            ->where(
                                function ($query) {
                                    $query->where('wast', 1)
                                        ->orWhere('recycle', 1);
                                }
                            )
                            ->where('type', 2)
                            ->where('barcode_id', $unit->barcode_id)
                            ->whereBetween(
                                DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                                [
                                    $startDate,
                                    $endDate
                                ]
                            )
                        ->get();

                        if ($checkBarcode->isNotEmpty()) {
                            ++$i;
                        }
                    }

                    if ($i != $unitCount) {
                        ++$buildingPending;
                    }
                }
            }
        }

        $this->data['notPickup'] = $buildingPending;
    }

    public function pickupWasteCal($matchdate, $userIds)
    {
        $startDate = \Carbon\Carbon::parse($matchdate)->addHour(6);
        $endDate = \Carbon\Carbon::parse($matchdate)
                ->addDay(1)->addHour(5)
                ->addMinute(59)->addSecond(59);

        $overallPickup = 0;
        $pickup = \App\Activitylogs::select('id')
                ->where('type', '2')
                ->whereIn('user_id', $userIds)
                ->where('barcode_id', '!=', null)
                ->where('wast', '1')
                ->whereBetween(
                    DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                    [$startDate, $endDate]
                )
                ->get();

        $overallPickup = $pickup->count();
        $array = ['pickup' => $overallPickup];

        return $array;
    }

    public function recycleDeliveryWastCal($matchdate, $userIds)
    {
        $startDate = \Carbon\Carbon::parse($matchdate)->addHour(6);
        $endDate = \Carbon\Carbon::parse($matchdate)->addDay(1)
                ->addHour(5)->addMinute(59)->addSecond(59);

        $overallRecycle = 0;
        $recycle = \App\Activitylogs::select('id')
                ->where('type', '2')
                ->whereIn('user_id', $userIds)
                ->where('barcode_id', '!=', null)
                ->where('wast', '1')
                //->where('recycle', '1')
                ->whereBetween(
                    DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                    [$startDate, $endDate]
                )
                ->get();

        $overallRecycle = $recycle->count();
        $array = [
            'recycles' => $overallRecycle,
        ];

        return $array;
    }

    public function violationWasteCal($matchdate, $userIds)
    {
        $startDate = \Carbon\Carbon::parse($matchdate)->addHour(6);
        $endDate = \Carbon\Carbon::parse($matchdate)
                ->addDay(1)->addHour(5)->addMinute(59)->addSecond(59);

        $overallViolation = 0;

        $violation = \App\Activitylogs::select('id')
                ->where('type', '3')
                ->whereIn('user_id', $userIds)
                ->where('barcode_id', '!=', null)
                // ->whereBetween(
                //     DB::raw("convert_tz(updated_at,'UTC','".getUserTimezone()."')"),
                //     [$startDate, $endDate]
                // )
                ->get();

        $overallViolation = $violation->count();
        $array = ['violation' => $overallViolation];

        return $array;
    }

    public function notesWasteCal($matchdate, $userIds)
    {
        $startDate = \Carbon\Carbon::parse($matchdate)->addHour(6);
        $endDate = \Carbon\Carbon::parse($matchdate)
                ->addDay(1)->addHour(5)->addMinute(59)->addSecond(59);

        $overallNotes = 0;
        $notes = \App\Activitylogs::select('id')
                ->where('type', '6')
                ->whereIn('user_id', $userIds)
                ->where('barcode_id', '!=', null)
                ->whereBetween(
                    DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                    [$startDate, $endDate]
                )
                ->get();

        $overallNotes = $notes->count();
        $array = [
            'notes' => $overallNotes,
        ];

        return $array;
    }

    //////////////////////////////////////////////////////////////////////
    public function pickupWasteCal1($matchdate, $userIds)
    {
        $startDate = \Carbon\Carbon::parse($matchdate)->addHour(6);
        $endDate = \Carbon\Carbon::parse($matchdate)->addDay(1)
            ->addHour(5)->addMinute(59)->addSecond(59);

        $overallPickup = 0;
        $pickup = \App\Activitylogs::select('id')
            ->where('type', '2')
            ->where(
                function ($query) use ($userIds) {
                    $query->whereIn('user_id', $userIds)
                        ->orWhereIn('updated_by', $userIds);
                }
            )
            ->whereNotNull('barcode_id')
            ->where('wast', '1')
            ->whereBetween(
                DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                [
                    $startDate,
                    $endDate,
                ]
            )
        ->get();

        $overallPickup = $pickup->count();
        $array = [
            'pickup' => $overallPickup,
        ];

        return $array;
    }

    public function recycleDeliveryWastCal1($matchdate, $userIds)
    {
        $startDate = \Carbon\Carbon::parse($matchdate)->addHour(6);
        $endDate = \Carbon\Carbon::parse($matchdate)
            ->addDay(1)->addHour(5)->addMinute(59)->addSecond(59);

        $overallRecycle = 0;
        $recycle = \App\Activitylogs::select('id')
            ->where('type', '2')
            ->where(
                function ($query) use ($userIds) {
                    $query->whereIn('user_id', $userIds)
                        ->orWhereIn('updated_by', $userIds);
                }
            )
            ->whereNotNull('barcode_id')
            ->where('wast', '1')
            //->where('recycle', '1')
            ->whereBetween(
                DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                [
                    $startDate,
                    $endDate,
                ]
            )
        ->get();

        $overallRecycle = $recycle->count();
        $array = [
            'recycles' => $overallRecycle,
        ];

        return $array;
    }

    public function violationWasteCal1($matchdate, $userIds)
    {
        $startDate = \Carbon\Carbon::parse($matchdate)->addHour(6);
        $endDate = \Carbon\Carbon::parse($matchdate)->addDay(1)
                ->addHour(5)->addMinute(59)->addSecond(59);

        $overallViolation = 0;

        $violation = \App\Activitylogs::select('id')
            ->where('type', '3')
            ->where(
                function ($query) use ($userIds) {
                    $query->whereIn('user_id', $userIds)
                        ->orWhereIn('updated_by', $userIds);
                }
            )
            ->whereNotNull('barcode_id')
            ->whereBetween(
                DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                [$startDate, $endDate]
            )
        ->get();

        $overallViolation = $violation->count();

        $array = [
            'violation' => $overallViolation,
        ];

        return $array;
    }

    public function notesWasteCal1($matchdate, $userIds)
    {
        $startDate = \Carbon\Carbon::parse($matchdate)->addHour(6);
        $endDate = \Carbon\Carbon::parse($matchdate)
            ->addDay(1)->addHour(5)->addMinute(59)->addSecond(59);

        $overallNotes = 0;
        $notes = \App\Activitylogs::select('id')
            ->where('type', '6')
            ->where(
                function ($query) use ($userIds) {
                    $query->whereIn('user_id', $userIds)
                        ->orWhereIn('updated_by', $userIds);
                }
            )
            ->whereNotNull('barcode_id')
            ->whereBetween(
                DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                [$startDate, $endDate]
            )
        ->get();

        $overallNotes = $notes->count();
        $array = [
            'notes' => $overallNotes,
        ];

        return $array;
    }

    public function recycleWasteCal($matchdate, $userIds, $proId)
    {
        $startDate = \Carbon\Carbon::parse($matchdate)->addHour(6);
        $endDate = \Carbon\Carbon::parse($matchdate)
                ->addDay(1)->addHour(5)->addMinute(59)->addSecond(59);

        $matchdate;
        $overallWaste = $overallRecyle = $wasteTarget = 0;

        $pkptueday = \App\Activitylogs::select('id')
                ->where('type', '2')
                ->where(
                    function ($query) use ($userIds) {
                        $query->whereIn('user_id', $userIds)
                        ->orWhereIn('updated_by', $userIds);
                    }
                )
                ->where('barcode_id', '!=', null)
                ->whereBetween('updated_at', [$startDate, $endDate])
                ->get();

        if (count($pkptueday) > 0) {
            foreach ($pkptueday as $val) {
                $units = Units::select('property_id')
                    ->where('is_route', 0)
                    ->when(
                        empty($proId),
                        function ($query) use ($val) {
                            $query->where('barcode_id', $val->barcode_id);
                        },
                        function ($query) use ($val, $proId) {
                            $query->where('barcode_id', $val->barcode_id)
                                ->where('property_id', $proId);
                        }
                    )
                ->first();

                if ($units) {
                    $services = Service::where('property_id', $units->property_id)
                            ->first();

                    if ($val->type == 2 && $val->wast == 1 && $val->recycle == null) {
                        $overallWaste += $services->waste_weight;
                    }
                    if ($val->type == 2 && $val->recycle == 1 && $val->wast == null) {
                        $overallRecyle += $services->recycle_weight;
                    }
                    if ($val->type == 2 && $val->recycle == 1 && $val->wast == 1) {
                        $overallWaste += $services->waste_weight;
                        $overallRecyle += $services->recycle_weight;
                    }

                    $wasteTarget = $services->waste_reduction_target;
                }
            }
        }

        $array = [
            'waste' => $overallWaste,
            'recycle' => $overallRecyle,
            'waste_target' => $wasteTarget,
        ];

        return $array;
    }

    public function recycleWasteCalMonthly($firstDayThisMonth, $lastDayThisMonth, $userIds, $id)
    {
        $matchdate;
        $overallWaste = $overallRecyle = $overallAvrage = $totalwaste = $getwaste = 0;

        $pkptueday = \App\Activitylogs::where('type', '2')
                ->where(
                    function ($query) use ($userIds) {
                        $query->whereIn('user_id', $userIds)
                        ->orWhereIn('updated_by', $userIds);
                    }
                )
                ->where('barcode_id', '!=', null)
                ->whereBetween(
                    'updated_at',
                    [
                        $firstDayThisMonth, $lastDayThisMonth,
                    ]
                )
                ->get();

        $propertyunit = Property::select('id', 'units')
                ->where('id', $id)->first();

        if (count($propertyunit) > 0) {
            $newservices = Service::where('property_id', $id)->first();

            if (count($newservices) > 0) {
                $totalwaste = $propertyunit->units * $newservices->waste_weight;
                $getwaste = ($totalwaste * $newservices->waste_reduction_target) / 100;
                $overallAvrage = round($totalwaste - $getwaste);

                if (count($pkptueday) > 0) {
                    foreach ($pkptueday as $val) {
                        $units = Units::select('property_id')
                            ->where('property_id', $id)
                            ->whereNotNull('barcode_id')
                            ->where('is_route', 0)
                            ->where('barcode_id', $val->barcode_id)
                            ->first();
                        
                        if (count($units) > 0) {
                            $services = Service::where('property_id', $units->property_id)->first();
                            if ($val->type == 2 && $val->wast == 1 && $val->recycle == null) {
                                $overallWaste += $services->waste_weight;
                            }
                            if ($val->type == 2 && $val->recycle == 1 && $val->wast == null) {
                                $overallRecyle += $services->recycle_weight;
                            }
                            if ($val->type == 2 && $val->recycle == 1 && $val->wast == 1) {
                                $overallRecyle += $services->recycle_weight;
                                $overallWaste += $services->waste_weight;
                            }
                        }
                    }
                }
            }

            $array = [
                'waste' => $overallWaste,
                'recycle' => $overallRecyle,
                'average' => $overallAvrage,
            ];

            return $array;
        }
    }

    public function dashboardMetrix(Request $request)
    {
        $subscriber_id = $this->subscriber_id;

        $start = \Carbon\Carbon::parse($request->range, getUserTimezone())->copy()->addHours(6)->toDateTimeString();

        $end = \Carbon\Carbon::parse($request->range, getUserTimezone())->copy()->addDays(1)->addHours(5)->addMinutes(59)->addSeconds(59)->toDateTimeString();

        $property = $this->propertyList()
            ->withCount(
                [
                    'checkInProperty' => function ($query) use ($start, $end) {
                        $query->whereBetween(
                            \DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                            [
                                $start,
                                $end,
                            ]
                        );
                    }
                ]
            )
            ->with(
                [
                    'getBuildingIsActiveUnit',
                    'service',
                    'getUnit' => function ($query) {
                        $query->select('id', 'property_id', 'building_id', 'building', 'type', 'is_active', 'barcode_id')
                            ->where('is_route', 0);
                            //->where('is_active', 1);
                    }
                ]
            )
            ->whereHas(
                'getUnit',
                function ($query) {
                    $query->where('is_active', 1)
                        ->where('is_route', 0);
                }
            )
            ->whereHas(
                'service',
                function ($query) use ($start, $end) {
                    $query->where('pickup_start', '<=', $start)
                        ->where('pickup_finish', '>=', $end);
                }
            )
            ->whereHas(
                'todayHasProperty',
                function ($query) use ($start) {
                    $query->where('day', $this->getCurrentDayCount($start));
                }
            )
        ->get();

        if ($property->isEmpty()) {
            return view('layouts/dashboardmetric', $this->data);
        }
        // For Pickup Pending: Start
        $checkingPending = $property->where('check_in_property_count', 0)->count();
        $this->data['propertyCheckIn'] = $checkingPending;
        // For Pick up Pending: End

        // For Violation: Start
        $violationCount = \App\Violation::select('id')
            ->where('status', 0)
            ->whereBetween(
                \DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                [
                    $start,
                    $end,
                ]
            )
            ->whereRaw("barcode_id in (select `barcode_id` from `units` where `property_id` in (" . $property->pluck('id')->implode(', ') . ") and `deleted_at` is null)")
            ->get();

        $this->data['violation'] = $violationCount->count();
        // For Violation: End

        //Total Pickup: Start
        $activity = \App\Activitylogs::select('id')
        ->when(
            $this->user->role_id == 1,
            function ($query) use ($subscriber_id) {
                $query->whereRaw("user_id in (select `id` from `users` where `subscriber_id` = $subscriber_id and `deleted_at` is null)");
            },
            function ($query) {
                $query->where('user_id', Auth::user()->id)
                    ->orWhere('updated_by', Auth::user()->id);
            }
        )
        ->whereBetween(
            \DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
            [
                $start,
                $end,
            ]
        )
        ->where('type', 2)
        ->get();

        $this->data['pickedup_dates'] = $activity->count();
        //Total Pickup: End

        // For Building Pending: Start
        $startDate = $start;
        $endDate = $end;
        $buildingPending = 0;

        // $propertiesId = $property->map(
        //     function ($val, $key) {
        //         return $val->id;
        //     }
        // );

        foreach ($property as $prop) {
            $buildingId = !empty($prop->getBuildingIsActiveUnit[0]->id) ? $prop->getBuildingIsActiveUnit[0]->id : '';

            $units = $prop->getUnit;
            $service = $prop->service;

            //Check Building Walkthrough: Start
            $checkWalkthrough = \App\Activitylogs::select('id')
                ->where('type', 8)
                ->when(
                    !empty($prop) && ($prop->type == 1 || $prop->type == 4),
                    function ($query) use ($prop) {
                        $query->where('property_id', $prop->id);
                    }
                )
                ->when(
                    $prop->type == 3 && !empty($buildingId),
                    function ($query) use ($buildingId, $prop) {
                        $query->where('property_id', $prop->id)
                            ->where('building_id', $buildingId);
                    }
                )
                ->whereBetween(
                    DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                    [
                            $startDate,
                            $endDate,
                        ]
                )
            ->get();
            //Check Building Walkthrough: End

            if ($checkWalkthrough->isNotEmpty() && $prop->type != 2) {
                continue;
            }

            // dump($units, $property->id);
            if ($prop->type == 1) {
                $checkBarcode = \App\Activitylogs::select('id')
                // ->when(
                //     $service->pickup_type == 1,
                //     function ($query) {
                //         $query->where('wast', 1);
                //     }
                // )
                // ->when(
                //     $service->pickup_type == 2,
                //     function ($query) {
                //         $query->where('recycle', 1);
                //     }
                // )
                // ->when(
                //     $service->pickup_type == 3,
                //     function ($query) {
                //         $query->where('recycle', 1)
                //         ->where('wast', 1);
                //     }
                // )
                ->where(
                    function ($query) {
                        $query->where('wast', 1)
                            ->orWhere('recycle', 1);
                    }
                )
                ->where('type', 2)
                ->where('barcode_id', $units[0]->barcode_id)
                ->whereBetween(
                    DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                    [
                        $startDate,
                        $endDate,
                    ]
                )
                ->get();

                if ($checkBarcode->isEmpty()) {
                    ++$buildingPending;
                }
            } elseif ($prop->type == 3 || $prop->type == 4) {
                $i = 0;
                $unitCount = $units->count();
               
                $checkBarcode = \App\Activitylogs::select('id')
                    // ->when(
                    //     $service->pickup_type == 1,
                    //     function ($query) {
                    //         $query->where('wast', 1);
                    //     }
                    // )
                    // ->when(
                    //     $service->pickup_type == 2,
                    //     function ($query) {
                    //         $query->where('recycle', 1);
                    //     }
                    // )
                    // ->when(
                    //     $service->pickup_type == 3,
                    //     function ($query) {
                    //         $query->where('recycle', 1)
                    //             ->where('wast', 1);
                    //     }
                    // )
                    ->where(
                        function ($query) {
                            $query->where('wast', 1)
                                ->orWhere('recycle', 1);
                        }
                    )
                    ->where('type', 2)
                    ->whereIn('barcode_id', $units->pluck('barcode_id'))
                    ->whereBetween(
                        DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                        [
                                $startDate,
                                $endDate,
                            ]
                    )
                ->get();

                if ($checkBarcode->count() != $unitCount) {
                    ++$buildingPending;
                }

            } elseif ($prop->type == 2) {
                $buildings = $units
                    ->mapToGroups(
                        function ($val, $key) {
                            if (!empty($val->id)) {
                                return [$val->building => $val];
                            } else {
                                return false;
                            }
                        }
                    );
                
                foreach ($buildings as $building) {
                    //Check Building Walkthrough for Garden Style: Start
                    $checkWalkthrough = \App\Activitylogs::select('id')
                        ->where('type', 8)
                        ->where('property_id', $prop->id)
                        ->where('building_id', $building[0]->building_id)
                        ->whereBetween(
                            DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                            [
                                $startDate,
                                $endDate,
                            ]
                        )
                        ->get();
                    //Check Building Walkthrough for Garden Style: End
                    if ($checkWalkthrough->isNotEmpty()) {
                        continue;
                    }

                    $i = 0;
                    $unitCount = $building->count(); //Get active unit count
                   // dd($building->pluck('barcode_id'));
                    $checkBarcode = \App\Activitylogs::select('id')
                        // ->when(
                        //     $service->pickup_type == 1,
                        //     function ($query) {
                        //         $query->where('wast', 1);
                        //     }
                        // )
                        // ->when(
                        //     $service->pickup_type == 2,
                        //     function ($query) {
                        //         $query->where('recycle', 1);
                        //     }
                        // )
                        // ->when(
                        //     $service->pickup_type == 3,
                        //     function ($query) {
                        //         $query->where('recycle', 1)
                        //             ->where('wast', 1);
                        //     }
                        // )
                        ->where(
                            function ($query) {
                                $query->where('wast', 1)
                                    ->orWhere('recycle', 1);
                            }
                        )
                        ->where('type', 2)
                        ->whereIn('barcode_id', $building->pluck('barcode_id'))
                        ->whereBetween(
                            DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                            [
                                    $startDate,
                                    $endDate,
                                ]
                        )
                        ->get();

                    if ($checkBarcode->count() != $unitCount) {
                        ++$buildingPending;
                    }
                }
            }
        }

        $this->data['notPickup'] = $buildingPending;
        //For Building Pending: End

        //For Total Employee: Start
        // $empolyeId = [];
        // $empolyee = \App\User::select('id')
        //     ->when(
        //         $this->user->role_id == 1,
        //         function ($query) {
        //             $query->where('subscriber_id', Auth::user()->subscriber_id);
        //         },
        //         function ($query) {
        //             $query->where('user_id', Auth::user()->id);
        //         }
        //     )
        //     ->whereNotIn('is_admin', [1])
        //     ->whereNotIn('role_id', [10])
        //     ->with(
        //         [
        //             'assignedproperties' => function ($query) use ($start) {
        //                 $query->select('id', 'property_id', 'user_id', 'type')
        //                     ->with(
        //                         [
        //                            'getUnitDetail' => function ($query) {
        //                                 $query->select('barcode_id', 'property_id')
        //                                     ->where('is_active', 1);
        //                            },
        //                            'getPropertyDetail' => function ($query) use ($start) {
        //                                 $query->select('id')
        //                                     ->with(
        //                                     [
        //                                         'service' => function ($query) {
        //                                             $query->select('pickup_type', 'property_id');
        //                                         },
        //                                         'todayHasProperty' => function ($query) use ($start) {
        //                                             $query->select('day', 'property_id')
        //                                                 ->where('day', $this->getCurrentDayCount($start));
        //                                         }
        //                                     ]
        //                                 );
        //                             }
        //                         ]
        //                     );
        //             },
        //         ]
        //     )
        // ->get();

        // foreach ($empolyee as $empolye) {
        //     if (in_array($empolye->id, $empolyeId)) {
        //         continue;
        //     }

        //     $assignedProperty = $empolye->assignedproperties;

        //     foreach ($assignedProperty as $assignedId) {
        //         // $hasProperty = \App\PropertyFrequencies::select('day')
        //         //     ->where('property_id', $assignedId->property_id)
        //         //     ->where('day', $this->getCurrentDayCount($start))
        //         //     ->get(); dd($assignedId->getPropertyDetail->todayHasProperty);
        //         $hasProperty = $assignedId->getPropertyDetail->todayHasProperty;
                
        //         if (isset($assignedId->property_id) && $hasProperty->isNotEmpty()) {
        //             $propertyType = $assignedId->getPropertyDetail->service->pickup_type;
        //             $unit = $assignedId->getUnitDetail;
        //             $pickCount = \App\Activitylogs::select('id')
        //                 //->whereRaw("barcode_id in (" . $unit->implode('barcode_id', ',') . ")")
        //                 ->whereIn('barcode_id', $unit->pluck('barcode_id'))
        //                 ->whereBetween(
        //                     'created_at',
        //                     [
        //                         $start,
        //                         $end,
        //                     ]
        //                 )
        //                 ->when(
        //                     $propertyType == 1,
        //                     function ($query) {
        //                         $query->where('wast', 1);
        //                     }
        //                 )
        //                 ->when(
        //                     $propertyType == 2,
        //                     function ($query) {
        //                         $query->where('recycle', 1);
        //                     }
        //                 )
        //                 ->when(
        //                     $propertyType == 3,
        //                     function ($query) {
        //                         $query->where('wast', 1)
        //                             ->where('recycle', 1);
        //                     }
        //                 )
        //             ->get();

        //             if ($unit->count() != $pickCount->count()) {
        //                 $empolyeId[] = $empolye->id;
        //             }
        //         }
        //     }
        // }

        // $this->data['total_employee'] = count(array_unique($empolyeId));
        //For Total Employee: End

        return view('layouts/dashboardmetric', $this->data);
    }

    public function daliyReports(Request $request)
    {
        $i = $request->start + 1;
        $daliyReport = [];
        $search = $request->search['value'];
        $properties = $this->propertyList()->get();

        $start = \Carbon\Carbon::parse($request->startTime, getUserTimezone())->copy()->addHours(6)->toDateTimeString();
        
        $end = \Carbon\Carbon::parse($request->endTime, getUserTimezone())->copy()->addDays(1)->addHours(5)->addMinutes(59)->addSeconds(59)->toDateTimeString();
        
        $recordCount = \App\DaliyRecords::query()
            // ->when(
            //     $properties->isNotEmpty(),
            //     function ($query) use ($properties) {
            //         $query->whereIn('property_id', $properties->pluck('id'));
            //     }
            // )
            ->when(
                !empty($search),
                function ($query) use ($search) {
                    $query->where(
                        function ($query) use ($search) {
                            $query->whereRaw("property_id in (select `id` from `properties` where `name` LIKE '%$search%')");
                        }
                    );
                }
            )
            ->whereBetween(
                'record_date',
                [
                    $start,
                    $end,
                ]
            )
            ->whereHas(
                'property',
                function ($query) {
                    $query->where('subscriber_id', $this->user->subscriber_id);
                }
            )
        ->get();
        
        $records = \App\DaliyRecords::latest()
            // ->when(
            //     $properties->isNotEmpty(),
            //     function ($query) use ($properties) {
            //         $query->whereIn('property_id', $properties->pluck('id'));
            //     }
            // )
            ->whereHas(
                'property',
                function ($query) {
                    $query->where('subscriber_id', $this->user->subscriber_id);
                }
            )
            ->when(
                !empty($search),
                function ($query) use ($search) {
                    $query->where(
                        function ($query) use ($search) {
                            $query->whereRaw("property_id in (select `id` from `properties` where `name` LIKE '%$search%')");
                        }
                    );
                }
            )
            ->whereBetween(
                'record_date',
                [
                    $start,
                    $end,
                ]
            )
            ->with(
                [
                    'property' => function ($query) {
                        $query->withTrashed();
                    }
                ]
            )
            ->limit($request->length)->offset($request->start)
            ->get();

        foreach ($records as $record) {
            $property = "<a href='javascript:void(0);' id='propertyId' data-id='" . $record->property->id . "'>" . ucwords($record->property->name) . "<br/>" .    \Carbon\Carbon::parse($record->record_date)->format('m-d-Y') . "</a>";

            $pickupCompleted = "<a href='" . url('delivery-report?activity=2&property=' . $record->property->id . '') . "'>" . $record->pickup_completed . "</a> ";

            $activeUnits = "<a href='" . url('barcode?status=1&property=' . $record->property->id . '') . "'>" . $record->active_units . "</a> ";

            $routeCheckpointScanned = "<a href='" . url('delivery-report?activity=11&property=' . $record->property->id . '') . "'>" . $record->route_checkpoints_scanned . "</a> ";

            $checkpointsByProperty = "<a href='" . url('routecheck-point?property=' . $record->property->id . '') . "'>" . $record->checkpoints_by_property . "</a> ";
            
            $buildingWalkThroughs = "<a href='" . url('delivery-report?activity=8&property=' . $record->property->id . '') . "'>" . $record->building_walk_throughs . "</a> ";

            $activeBuilding = "<a href='" . url('/building-list?property=' . $record->property->id . '') . "'>" . $record->active_building . "</a> ";

            $checkinoutDuration = "<a href='" . url('check-in-property-pending') . "'>" . $record->checkinout_duration . "</a> ";

            $totalTasksCompleted = "<a href='" . url('report/manage-task?property=' . $record->property->id . '') . "'>" . $record->total_tasks_completed . "</a> ";
            
            $totalTasks = "<a href='" . url('report/manage-task?property=' . $record->property->id . '') . "'>" . $record->total_task . "</a> ";
            
            $missedPropertyCheckouts = "<a href='javascript:void(0);'>" . $record->missed_property_checkouts . "</a> ";


            $daliyReport[] = [
                'user_id' => $i++,
                'property' => $property,
                'pickup_completed' => $pickupCompleted,
                'active_units' => $activeUnits,
                'route_checkpoints_scanned' => $routeCheckpointScanned,
                'checkpoints_by_property' => $checkpointsByProperty,
                'building_walk_throughs' => $buildingWalkThroughs,
                'active_building' => $activeBuilding,
                'checkinout_duration' => $checkinoutDuration,
                'total_tasks_completed' => $totalTasksCompleted,
                'total_tasks' => $totalTasks,
                'missed_property_checkouts' => $missedPropertyCheckouts,
            ];
        }

        return json_encode(
            [
                'draw' => intval($request->draw),
                'recordsTotal' => !empty($recordCount) ? $recordCount->count() : 0,
                'recordsFiltered' => !empty($recordCount) ? $recordCount->count() : 0,
                'data' => $daliyReport,
            ]
        );
    }

    private function getCurrentDayCount($date)
    {
        $currentDay = \Carbon\Carbon::parse($date)->subHours(6)->format('l');

        switch ($currentDay) {
            case 'Monday':
                return 0;
                break;
            case 'Tuesday':
                return 1;
                break;
            case 'Wednesday':
                return 2;
                break;
            case 'Thursday':
                return 3;
                break;
            case 'Friday':
                return 4;
                break;
            case 'Saturday':
                return 5;
                break;
            case 'Sunday':
                return 6;
                break;
        }
    }
}
