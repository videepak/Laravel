<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminReportsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        $subscriber = \App\User::query()
            ->select('id', \DB::raw("CONCAT_WS(' ', `title`, `firstname`, `lastname`) as name"))
            ->where('is_admin', 1)
            ->whereHas('getSubscriber')
            ->get();
        
        return view('admin.reports.report', ['subscriber' => $subscriber]);
    }

    public function serviceReport(Request $request)
    {
        $i = $request->start + 1;
        $o = 0;
        $logsArray = [];

        $users = \App\Subscriber::query()
            ->select('id', 'user_id')
            ->where('user_id', $request->subscriber)
            ->with(
                [
                    'user' => function ($query) {
                        $query->select('id', 'email', 'subscriber_id', 'is_admin', 'timezone');
                    },
                    'employees' => function ($query) {
                        $query->select('id', 'email', 'subscriber_id', 'is_admin', 'role_id', 'timezone', 'deleted_at')
                            ->withTrashed();
                    },
                    'getProperties' => function ($query) {
                        $query->select('id', 'subscriber_id', 'name')
                            ->withTrashed();
                    }
                ]
            )
        ->first();

        $timezone = $users->user->timezone;
        
        $startTime = \Carbon\Carbon::parse($request->startTime, $timezone)
            ->addHours(6)->copy();
        
        $endTime = \Carbon\Carbon::parse($request->startTime, $timezone)
            ->addDays(1)->addHours(5)->addMinutes(59)->addSeconds(59)->copy();

        $getProperties = $users->getProperties;

        $getEmployees = $users->employees;
        
        $barcode = \App\Units::select('barcode_id')
            ->where(
                function ($query) {
                    $query->where(
                        function ($query) {
                            $query->where('is_route', 0)
                                ->where('is_active', 1);
                        }
                    )
                    ->orWhere(
                        function ($query) {
                            $query->where('is_route', 1);
                        }
                    );
                }
            )
            ->whereIn('property_id', $getProperties->pluck('id'))
            ->withTrashed()
            ->get()
            ->map(
                function ($val, $key) {
                    return $val->barcode_id;
                }
            );

            $total = \App\Activitylogs::query()
                ->where(
                    function ($query) use ($getEmployees) {
                        $query->whereIn('user_id', $getEmployees->pluck('id'))
                            ->orWhereIn('updated_by', $getEmployees->pluck('id'));
                    }
                )
                ->where(
                    function ($query) use ($barcode, $getProperties) {
                        $query->whereRaw("barcode_id in (select `barcode_id` from `units` where `property_id` in (" . collect($getProperties->pluck('id'))->implode(', ') . ") and `is_active` = 1)")
                            ->orWhereIn('property_id', $getProperties->pluck('id'));
                    }
                )
                ->whereIn('type', [2, 3, 6, 8, 5, 11])
                ->whereBetween(
                    \DB::raw("convert_tz(updated_at, 'UTC','" . $timezone . "')"),
                    [
                        $startTime,
                        $endTime,
                    ]
                )
                ->whereNotNull('barcode_id')
                ->withTrashed()
                ->latest()
                ->get();
              
            $totalLog = \App\Activitylogs::query()
                ->where(
                    function ($query) use ($getEmployees) {
                        $query->whereIn('user_id', $getEmployees->pluck('id'))
                            ->orWhereIn('updated_by', $getEmployees->pluck('id'));
                    }
                )
                ->where(
                    function ($query) use ($barcode, $getProperties) {
                        $query->whereRaw("barcode_id in (select `barcode_id` from `units` where `property_id` in (" . collect($getProperties->pluck('id'))->implode(', ') . ") and `is_active` = 1)")
                            ->orWhereIn('property_id', $getProperties->pluck('id'));
                    }
                )
                ->whereIn('type', [2, 3, 6, 8, 5, 11])
                ->whereBetween(
                    \DB::raw("convert_tz(updated_at, 'UTC','" . $timezone . "')"),
                    [
                        $startTime,
                        $endTime,
                    ]
                )
                ->with(
                    [
                        'getUserDetail' => function ($query) {
                            $query->select('id', 'title', 'firstname', 'lastname', 'mobile', 'role_id', 'subscriber_id', 'user_id')
                                ->withTrashed();
                        },
                        'unit' => function ($query) {
                            $query->select('id', 'unit_number', 'property_id', 'building_id', 'barcode_id', 'created_at', 'updated_at')
                                // ->where('is_active', '1')
                                ->with(
                                    [
                                        'getBuildingDetail' => function ($query) {
                                            $query->select('id', 'building_name', 'property_id')
                                                ->withTrashed();
                                        },
                                    ]
                                )
                                ->withTrashed();
                        },
                        'getProperty' => function ($query) {
                            $query->select('id', 'units', 'name', 'type')
                                ->with(
                                    [
                                        'service' => function ($query) {
                                            $query->select('id', 'recycle_weight', 'waste_weight', 'waste_reduction_target', 'recycling', 'property_id')
                                                ->withTrashed();
                                        },
                                    ]
                                )
                                ->withTrashed();
                        }
                    ]
                )
                ->whereNotNull('barcode_id')
                ->limit($request->length)->offset($request->start)
                ->withTrashed()
                ->latest()
                ->get();
                        
        foreach ($totalLog as $log) {
            $propertyName = $propertyId = $buildingName = $type = '';
            $userInfoByUserId = $log->getUserDetail;
            $property = $log->getProperty;
            $building = !empty($log->unit->getBuildingDetail) ? $log->unit->getBuildingDetail : '';

            if ($log->type == 11) {
                $units = \App\Units::where('barcode_id', $log->barcode_id)
                    ->withTrashed()->first();

                if (isset($property->name)) {
                    $propertyName = $property->name;
                }
                
                if (!is_null($units)) {
                    $buildingName = !empty($units->getBuilding->building_name) ? $units->getBuilding->building_name : '-';
                }
            } elseif ($log->type == 3) {
                $vio = \App\Violation::where('barcode_id', $log->barcode_id)->withTrashed()
                    ->first();
                
                if (is_null($vio)) {
                    continue;
                }
                        
                $units = $log->unit;
            } else {
                $units = $log->unit;
            }
                
            if (isset($units->property_id)) {
                if (!isset($property->service)) {
                    continue;
                }

                $services = $property->service;
                
                if ($log->type == 2 && $log->wast == 1 && $log->recycle == null) {
                    $type = 'Waste Total: ' . $services->waste_weight;
                }
                        
                if ($log->type == 2 && $log->recycle == 1 && $log->wast == null) {
                    $type = 'Recycle Total: ' . $services->recycle_weight;
                }
                        
                if ($log->type == 2 && $log->recycle == 1 && $log->wast == 1) {
                    $type = 'Waste Total:' . $services->recycle_weight . '<br/> Recycle Total:' . $services->waste_weight;
                }
                
                if (isset($property->name)) {
                    $propertyName = $property->name;
                }
                
                if (isset($units->getBuildingDetail->building_name)) {
                    $buildingName = $units->getBuildingDetail->building_name;
                }
                
                $logsArray[] = [
                    'sNo' => ++$o,
                    'property_name' => $propertyName,
                    'building' => empty($buildingName) ? $propertyName : $buildingName,
                    'unit' => !empty($units->unit_number) ? $units->unit_number : $units->name,
                    'updated_at' => $log->updated_at->timezone($timezone)->format('m-d-Y h:i A'),
                    'type' => !empty($type) ? $type : '-',
                    'status' => $log->text,
                    'employee_name' => ucwords($userInfoByUserId->title . ' ' . $userInfoByUserId->firstname . ' ' . $userInfoByUserId->lastname),
                ];
            } elseif ($log->type == 8 || $log->type == 5 || $log->type == 12) {
                if (isset($property->name)) {
                    $propertyName = $property->name;
                    $propertyId = $property->id;
                }
                
                if (isset($building->building_name)) {
                    $buildingName = $building->building_name;
                }
                    
                $logsArray[] = [
                    'sNo' => ++$o,
                    'property_name' => $propertyName,
                    'building' => empty($buildingName) ? $propertyName : $buildingName,
                    'unit' => !empty($units->name) ? $units->name : '-',
                    'updated_at' => $log->updated_at->timezone($timezone)->format('m-d-Y h:i A'),
                    'type' => '-',
                    'status' => $log->text,
                    'employee_name' => ucwords($userInfoByUserId->title . ' ' . $userInfoByUserId->firstname . ' ' . $userInfoByUserId->lastname),
                ];
            }
        }

        return json_encode(
            [
                'draw' => intval($request->draw),
                'recordsTotal' => !empty($total) ? $total->count() : 0,
                'recordsFiltered' => !empty($total) ? $total->count() : 0,
                'data' => $logsArray,
            ]
        );
    }

    public function unitReport(Request $request)
    {
        $i = $request->start + 1;
        $image = 0;
        $serviceArray = [];

        $users = \App\Subscriber::query()
            ->select('id', 'user_id')
            ->where('user_id', $request->subscriber)
            ->with(
                [
                    'user' => function ($query) {
                        $query->select('id', 'email', 'subscriber_id', 'is_admin', 'timezone');
                    },
                    'getProperties' => function ($query) {
                        $query->select('id', 'subscriber_id', 'name')
                            ->with(
                                [
                                    'getUnit' => function ($query) {
                                        $query->select('id', 'address1', 'address2', 'unit_number', 'activation_date', 'property_id', 'building_id', 'latitude', 'longitude', 'building', 'barcode_id', 'created_at', 'updated_at', 'floor', 'is_active', 'is_route')
                                            ->where(
                                                function ($query) {
                                                    $query->where(
                                                        function ($query) {
                                                            $query->where('is_route', 0)
                                                                ->where('is_active', 1);
                                                        }
                                                    )
                                                    ->orWhere('is_route', 1);
                                                }
                                            );
                                    }
                                ]
                            )
                            ->whereHas(
                                'getUnit',
                                function ($query) {
                                    $query->where(
                                        function ($query) {
                                            $query->where(
                                                function ($query) {
                                                    $query->where('is_route', 0)
                                                        ->where('is_active', 1);
                                                }
                                            )
                                            ->orWhere('is_route', 1);
                                        }
                                    );
                                }
                            );
                    }
                ]
            )
        ->first();
                    
        $timezone = $users->user->timezone;

        $startTime = \Carbon\Carbon::parse($request->startTime, $timezone)
            ->addHours(6)->copy();
        
        $endTime = \Carbon\Carbon::parse($request->startTime, $timezone)
            ->addDays(1)->addHours(5)->addMinutes(59)->addSeconds(59)->copy();
        
        $getProperties = $users->getProperties;
        
        foreach ($getProperties as $property) {
            foreach ($property->getUnit as $getUnit) {
                $lastScanDate = \App\Activitylogs::query()
                    ->select('created_at')
                    ->where('barcode_id', $getUnit->barcode_id)
                    ->latest()
                    ->first();

                $serviceArray[] = [
                    //'Sno' => $i++,
                    'Address1' => $getUnit->address1,
                    'Address2' => $getUnit->address2,
                    'unitNumber' => $getUnit->unit_number,
                    'activationDate'  => !empty($getUnit->activation_date) ? \Carbon\Carbon::parse($getUnit->activation_date)->timezone($timezone)->format('m-d-Y h:i A') : '',
                    'Property'  => $property->name,
                    'Building'  => $getUnit->building,
                    'Floor' => $getUnit->floor,
                    'Latitude'  => $getUnit->latitude,
                    'Longitude' => $getUnit->longitude,
                    'Barcode'  => $getUnit->barcode_id,
                    'lastScanDate'  => !empty($lastScanDate->created_at) ? $lastScanDate->created_at->timezone($timezone)->format('m-d-Y h:i A') : '',
                    'Units'  => empty($getUnit->is_route) ? 'Unit' : 'Route Checkpoint',
                    'CreatedAt'  => $getUnit->created_at->timezone($timezone)->format('m-d-Y h:i A'),
                    'UpdatedAt' => $getUnit->updated_at->timezone($timezone)->format('m-d-Y h:i A'),
                    'Status' => !empty($getUnit->is_active) ? 'Active' : 'In-active',
                ];
            }
        }

        if (!empty($serviceArray)) {
            foreach ($serviceArray as $key => $row)
            {
                $createdAt[$key] = $row['CreatedAt'];
            }

            array_multisort($createdAt, SORT_DESC, $serviceArray);
        }

        return json_encode(
            [
                'draw' => intval($request->draw),
                'recordsTotal' => count($serviceArray) ? count($serviceArray) : 0,
                'recordsFiltered' => count($serviceArray) ? count($serviceArray) : 0,
                'data' => count($serviceArray) ? array_slice($serviceArray, $request->start, $request->length) : 0,
            ]
        );
    }

    public function clockReport(Request $request)
    {
        $i = $request->start + 1;
        $image = 0;
        $clockArray = [];

        $users = \App\Subscriber::query()
            ->select('id', 'user_id')
            ->where('user_id', $request->subscriber)
            ->with(
                [
                    'user' => function ($query) {
                        $query->select('id', 'email', 'subscriber_id', 'is_admin', 'timezone');
                    }
                ]
            )
            ->withTrashed()
        ->first();
        
        $timezone = $users->user->timezone;
        
        $startTime = \Carbon\Carbon::parse($request->startTime, $timezone)
            ->addHours(6)->copy();
        
        $endTime = \Carbon\Carbon::parse($request->startTime, $timezone)
            ->addDays(1)->addHours(5)->addMinutes(59)->addSeconds(59)->copy();

        $cloc = \App\ClockInOut::where(
            function ($query) use ($request) {
                $query->whereIn(
                    'user_id',
                    function ($query) use ($request) {
                        $query->select('id')
                            ->from('users')
                            ->whereNotIn('role_id', [10])
                            ->whereNull('deleted_at')
                            ->where('subscriber_id', $request->subscriber);
                    }
                );
            }
        )
        ->whereBetween(
            \DB::raw("convert_tz(created_at,'UTC','" . $timezone . "')"),
            [
                $startTime,
                $endTime,
            ]
        )
        ->get();
            
        $clock = \App\ClockInOut::where(
            function ($query) use ($request) {
                $query->whereIn(
                    'user_id',
                    function ($query) use ($request) {
                        $query->select('id')
                            ->from('users')
                            ->whereNotIn('role_id', [10])
                            ->whereNull('deleted_at')
                            ->where('subscriber_id', $request->subscriber);
                    }
                );
            }
        )
        ->whereBetween(
            \DB::raw("convert_tz(created_at,'UTC','" . $timezone . "')"),
            [
                $startTime,
                $endTime,
            ]
        )
        ->limit($request->length)->offset($request->start)
        ->with(
            [
                'getUser',
            ]
        )
        ->get();

        foreach ($clock as $clocks) {
            $name = $clockin = $clockout = $reason = '';

            $reporting = \App\User::select(\DB::raw("CONCAT_WS(' ', `firstname`, `lastname`) as name"))->where('id', $clocks->getUser->reporting_manager_id)->first();

            $name = !empty($clocks->getUser->firstname) ? ucwords($clocks->getUser->firstname) . ' ' . ucwords($clocks->getUser->lastname) : '-';

            $clockin = !empty($clocks->clock_in) ? \Carbon\Carbon::parse($clocks->clock_in)->timezone($timezone)->format('m-d-Y h:i A') : '-';

            $clockout = !empty($clocks->clock_out) ? \Carbon\Carbon::parse($clocks->clock_out)->timezone($timezone)->format('m-d-Y h:i A') : '';

            $reason = !empty($clocks->reason) ? ucwords($clocks->reason) : '';
            
            $reporting = !is_null($reporting) ? ucwords($reporting->name) : '-';

            $clockArray[] = [
                'user_id' => $i++,
                'name' => $name,
                'reportingname' => $reporting,
                'clockin' => $clockin,
                'clockout' => $clockout,
                'reason' => $reason,
            ];
        }

        return json_encode(
            [
                'draw' => intval($request->draw),
                'recordsTotal' => !empty($cloc) ? $cloc->count() : 0,
                'recordsFiltered' => !empty($cloc) ? $cloc->count() : 0,
                'data' => $clockArray,
            ]
        );
    }

    public function violationReport(Request $request)
    {
        $i = $request->start + 1;
        $image = 0;
        $violationArray = [];

        $users = \App\Subscriber::query()
            ->select('id', 'user_id')
            ->where('user_id', $request->subscriber)
            ->with(
                [
                    'user' => function ($query) {
                        $query->select('id', 'email', 'subscriber_id', 'is_admin', 'timezone');
                    },
                    'getProperties' => function ($query) {
                        $query->select('id', 'subscriber_id')
                            ->withTrashed();
                    },
                ]
            )
        ->first();
        
        $timezone = $users->user->timezone;
        
        $startTime = \Carbon\Carbon::parse($request->startTime, $timezone)
            ->addHours(6)->copy();
        
        $endTime = \Carbon\Carbon::parse($request->startTime, $timezone)
            ->addDays(1)->addHours(5)->addMinutes(59)->addSeconds(59)->copy();

        $violation = \App\Violation::query()
            ->whereBetween(
                \DB::raw("convert_tz(created_at, 'UTC','" . $timezone . "')"),
                    [
                        $startTime,
                        $endTime,
                    ]
            )
            ->whereIn('property_id', $users->getProperties->pluck('id'))
            ->withCount(
                [
                    'images',
                ]
            )
            ->latest()
            ->withTrashed()
            ->get();
        
        $vio = \App\Violation::query()
            ->whereBetween(
                \DB::raw("convert_tz(created_at, 'UTC','" . $timezone . "')"),
                    [
                        $startTime,
                        $endTime,
                    ]
            )
            ->whereIn('property_id', $users->getProperties->pluck('id'))
            ->withCount(
                [
                    'images',
                ]
            )
            ->with(
                [
                    'getReason' => function ($query) {
                        $query->select('id', 'reason')
                            ->withTrashed();
                    },
                    'getAction' => function ($query) {
                        $query->select('id', 'action')
                            ->withTrashed();
                    },
                    'getUser' => function ($query) {
                        $query->select('id', \DB::raw("CONCAT_WS(' ', `title`, `firstname`, `lastname`) as name"))
                            ->withTrashed();
                    },
                    'getUnitNumber' => function ($query) {
                        $query->select('id', 'unit_number', 'barcode_id', 'property_id')
                            ->withTrashed();
                    },
                    'getBuilding' => function ($query) {
                        $query->select('id', 'building_name');
                    },
                    'getProperty' => function ($query) {
                        $query->select('id', 'name')
                            ->withTrashed();
                    },
                ]
            )
            ->latest()
            ->withTrashed()
            ->limit($request->length)->offset($request->start)
            ->get();

        foreach ($vio as $vios) {
            $name = $property = $detail = $clockout = $reason = '';
        
            if (isset($vios->getUser->name)) {
                $name = ucwords($vios->getUser->name);
            }

            $property = isset($vios->getProperty->name) ? $vios->getProperty->name : '';

            if (isset($vios->getReason->reason)) {
                $rule = ucwords($vios->getReason->reason);
            }
    
            if (isset($vios->getAction->action)) {
                $action = ucwords($vios->getAction->action);
            }

            $vioStatus = $vios->status;

            if ($vioStatus == 6) {
                $type = 'Archived';
            } elseif ($vioStatus == 5) {
                $type = 'Closed';
            } elseif ($vioStatus == 2) {
                $type = 'Submitted';
            } elseif ($vioStatus == 0) {
                $type = 'New';
            } elseif ($vioStatus == 7) {
                $type = 'Read';
            } elseif ($vioStatus == 8) {
                $type = 'In Process';
            } elseif ($vioStatus == 9) {
                $type = 'On Hold';
            } elseif ($vioStatus == 10) {
                $type = 'Sent Notice';
            } else {
                $type = 'Discarded';
            }

            if (isset($vios->created_at)) {
                $createdAt = \Carbon\Carbon::parse($vios->created_at)
                        ->timezone($timezone)->format('m-d-Y h:i A');
            }
     
            if (isset($vios->getBuilding->building_name)) {
                $building = ucwords($vios->getBuilding->building_name);
            } else {
                $building = "";
            }

            if (empty($vios->type) && isset($vios->getUnitNumber->unit_number)) {
                $detail = 'Unit Number:' . $vios->getUnitNumber->unit_number;
            }
        
            if (!empty($vios->type) && isset($vios->getUnitNumber->unit_number)) {
                $detail = 'Route Checkpoint: ' . $vios->getUnitNumber->unit_number;
            }

            if (isset($vios->special_note)) {
                $specialNote = ucwords($vios->special_note);
            }

            $violationArray[] = [
                'user_id' => $i++,
                'username' => $name,
                'property' => $property,
                'rule' => $rule,
                'action' => $action,
                'status' => $type,
                'detail' => $detail,
                'special' => $specialNote,
                'building' => $building,
                'detail' => $detail,
                'imagecount' => $vios->images_count ? $vios->images_count : 0,
                'created_at' => $createdAt
            ];
        }
        
        return json_encode(
            [
                'draw' => intval($request->draw),
                'recordsTotal' => !empty($violation) ? $violation->count() : 0,
                'recordsFiltered' => !empty($violation) ? $violation->count() : 0,
                'data' => $violationArray,
            ]
        );
    }

    public function showManageReports(Request $request)
    {
        $reportLogs = \App\ReportLog::select('receiver','body')->paginate(10);
    //     $receiver = '';
    //     $body = '';
    //     foreach($reportLogs as $reports)
    //     {
    //         $receiver = json_decode($reports->receiver);
    //         $body = json_decode($reports->body);
    //     }
    //    dd($body);
       return view('admin.reports.reportlog', ['reportLogs' => $reportLogs]);
    }
}
