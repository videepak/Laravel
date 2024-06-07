<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Mail;
use App\Mail\updateResidentSendEmail;

class GuestController extends BaseController
{
    public function clockInOutDetail(Request $request)
    {
        $users = $reporting = collect();

        $reporting = \App\User::select('timezone', 'subscriber_id', 'is_admin', \DB::raw("CONCAT_WS(' ', `firstname`, `lastname`) as name"))
            ->where('api_token', $request->id)
            ->first();

        if (!is_null($reporting) && $reporting->is_admin == 1) {
            $users = \App\User::select('id', 'reporting_manager_id', \DB::raw("CONCAT_WS(' ', `firstname`, `lastname`) as name"));

            $users->getModel()->userReporting = $reporting;
 
            $users->where(
                [
                    'subscriber_id' => $reporting->subscriber_id,
                    'role_id' => \config('constants.adminRoleId'),
                ]
            )
            ->with(
                [
                    'getManagerUsers' => function ($query) use ($reporting) {
                        $query->select('id', 'reporting_manager_id', \DB::raw("CONCAT_WS(' ', `firstname`, `lastname`) as name"))
                            ->orderBy('firstname');
                    },
                ]
            )
            ->latest();

            $users = $users->get();
        } elseif (!is_null($reporting)) {
            $today = \Carbon\Carbon::now()->setTimezone($reporting->timezone)->subHours(6);
            $todayStart = $today->copy()->format('Y-m-d') . ' 06:00:00';
            $todayEnd = $today->copy()->addDay(1)->format('Y-m-d') . ' 05:59:59';

            $users = \App\User::select('id', 'reporting_manager_id', \DB::raw("CONCAT_WS(' ', `firstname`, `lastname`) as name"))
                ->where('role_id', '!=', 10) //Remove property manager from the list.
                ->whereIn(
                    'reporting_manager_id',
                    function ($query) use ($request) {
                        $query->from('users')
                            ->select('id')
                            ->where('api_token', $request->id);
                    }
                )
                ->with(
                    [
                        'clockDetail' => function ($query) use ($reporting, $todayStart, $todayEnd) {
                            $query->select('id', 'user_id', 'clock_in', 'clock_out', 'reason')
                                ->whereBetween(
                                    \DB::raw("convert_tz(clock_in, 'UTC', '" . $reporting->timezone . "')"),
                                    [
                                        $todayStart,
                                        $todayEnd
                                    ]
                                )
                                //->limit(1)
                                ->orderBy('id', 'DESC');
                        },
                    ]
                )
                ->latest()->get();
        }

        $this->data['users'] = $users;
        $this->data['reporting'] = $reporting;

        return view('guest.clockdetail', $this->data);
    }

    public function violationDetailByLink($id, $subscriberId)
    {
        //try {
            $decrypted = \Hashids::decode($id);

            $subId = \Hashids::decode($subscriberId);

            $this->data['logo'] = getLogo($subId)['logo'];

            $this->data['companyName'] = empty(getLogo($subId)['logo'])
                    ?: getLogo($subId)['companyName'];

            $enquiry = \App\Violation:: where('id', $decrypted)
                ->with(
                    [
                        'getReason',
                        'getUser',
                        //'getUnitNumber',
                        'images',
                        'getUnitNumber' => function ($query) {
                            $query->where('is_route', 0);
                        }
                    ]
                )
            ->get();

            $this->data['enquiry'] = $enquiry;

        if ($enquiry->isNotEmpty()) {
            $barcodeId = $enquiry[0]->barcode_id;
            $date = $enquiry[0]->created_at;
              
            $propertyDetail = \App\Property::withTrashed()
                ->where('id', $enquiry[0]->property_id)
                ->get();
                
            if ($propertyDetail->isNotEmpty() && !empty($propertyDetail[0]->image)) {
                $this->data['logo'] = url('/uploads/property') . '/' . $propertyDetail[0]->image;
            } else {
                $this->data['logo'] = getLogo()['logo'];
            }
                
            // if ($enquiry[0]->type) {
            //     $route = \App\RouteCheckIn::where('barcode_id', $enquiry[0]->barcode_id)
            //     ->withTrashed()->first();
    
            //     $routeName = $route->name;
            //     $propertyName = $route->getProperty->name;
            //     $propertyAddress = $route->getProperty->address;
            //     $buildingAddress = $route->getBuilding->address;
            //     $building = ucwords($route->getBuildingDetail->building_name);
            // } elseif (!empty($enquiry[0]->barcode_id)) {
            //     $uni = \App\Units::where('barcode_id', $enquiry[0]->barcode_id)
            //         ->withTrashed()->first();
                    
            //     $unit = !empty($uni->unit_number) ? $uni->unit_number : '-' ;
            //     $propertyAddress = isset($uni->getPropertyDetail->address1) ? $uni->getPropertyDetail->address : '';
            //     $propertyName = isset($uni->getPropertyDetail->name) ? $uni->getPropertyDetail->name : '';
            //     $buildingAddress = $route->getBuildingDetail->building_name;
            //     $building = ucwords($route->getBuildingDetail->address);
            // }

            $uni = \App\Units::where('barcode_id', $enquiry[0]->barcode_id)
                    ->withTrashed()->first();
                    
            $unit = !empty($uni->unit_number) ? $uni->unit_number : '-' ;
                
            $propertyAddress = isset($uni->getPropertyDetail->address1) ?
                
            $uni->getPropertyDetail->address : '';
                
            $propertyName = isset($uni->getPropertyDetail->name) ? $uni->getPropertyDetail->name : '';
                
            $buildingAddress = $route->getBuildingDetail->building_name;
                
            $building = ucwords($route->getBuildingDetail->address);

            $filename = public_path('uploads/violation/' . $enquiry[0]->image_name);

            if ($enquiry[0]->image_name == '' || !file_exists($filename)) {
                $url = url('/uploads/violation/no-image-available.png');
            } else {
                $url = url('/uploads/violation/' . $enquiry[0]->image_name);
            }

            $status = '';

            if ($enquiry[0]->status == 0) {
                $status = 'New';
            } elseif ($enquiry[0]->status == 2) {
                $status = 'Submitted';
            } elseif ($enquiry[0]->status == 3) {
                $status = 'Discarded';
            } elseif ($enquiry[0]->status == 4) {
                $status = 'Pending (Payment)';
            } elseif ($enquiry[0]->status == 5) {
                $status = 'Closed';
            } elseif ($enquiry[0]->status == 6) {
                $status = 'Archived';
            }

            $name = '';

            if (!empty($enquiry[0]->getUser->firstname)) {
                $name = ucwords($enquiry[0]->getUser->firstname . ' ' . $enquiry[0]->getUser->lastname);
            }

            if (isset($enquiry[0]->getReason->reason) && !empty($enquiry[0]->getReason->reason)) {
                $reason = ucwords($enquiry[0]->getReason->reason);
            }

            if (isset($enquiry[0]->getAction->action) && !empty($enquiry[0]->getAction->action)) {
                $action = ucwords($enquiry[0]->getAction->action);
            }

            $voilationDetails = [
                'id' => $enquiry[0]->id,
                'status' => $status,
                'user_name' => $name,
                'property_name' => $propertyName,
                'address' => $propertyAddress,
                'reason' => $reason,
                'action' => $action,
                'type' => $propertyDetail[0]->type,
                'unit' => !empty($unit) ? $unit : false,
                'isRoute' => $enquiry[0]->type,
                'images' => $enquiry[0]->images,
                'created_at' => $date,
                'special_note' => $enquiry[0]->special_note,
                'is_mail' => 1,
                'building_address' => $buildingAddress,
                'building' => $building,
                'comment' => !empty($enquiry[0]->comment) ? $enquiry[0]->comment : '',
                'reminder' => !empty($propertyDetail[0]->reminder) ? $propertyDetail[0]->reminder : ''
            ];

            $this->data['violation'] = $voilationDetails;
        }

            return view('guest.violation', $this->data);
        // } catch (\Exception $e) {
        //     echo 'Message: ' .$e->getMessage();
        // }
    }

    protected function automatedService($user, $date)
    {
        $users = \App\Subscriber::query()
            ->select('id', 'user_id')
            ->where('id', $user->subscriber_id)
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

        switch ($timezone) {
            case "America/Los_Angeles":
                $reportTime = 'PST (UTC-8:00 Hrs)';
                break;
            case "America/Chicago":
                $reportTime =  "CST (UTC-6:00 Hrs)";
                break;
            case "America/Denver":
                $reportTime =  "MST (UTC-7:00 Hrs)";
                break;
            default:
                $reportTime = "EST (UTC-5:00 Hrs)";
        }
        
        $startTime = \Carbon\Carbon::parse($date, $timezone)
            ->addHours(6)->copy();
        
        $endTime = \Carbon\Carbon::parse($date, $timezone)
            ->addDays(1)->addHours(5)->addMinutes(59)->addSeconds(59)->copy();

        $getProperties = $users->getProperties;

        $getEmployees = $users->employees;

        $logsArray[] = [
            'S.No', 'Property', 'Building Name', 'Unit',
            'Scan Date', 'Volume','Activity', 'Scan By',
            'Property Timezone'
        ];

        $i = 0;

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
                //->whereNotNull('barcode_id')
                ->withTrashed()
                ->latest()
                ->get();
                //dd($totalLog->toArray());        
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
                    'sNo' => ++$i,
                    'property_name' => $propertyName,
                    'building' => empty($buildingName) ? $propertyName : $buildingName,
                    'unit' => !empty($units->unit_number) ? $units->unit_number : $units->name,
                    'updated_at' => $log->updated_at->timezone($timezone)->format('m-d-Y h:i A'),
                    'type' => !empty($type) ? $type : '-',
                    'status' => $log->text,
                    'employee_name' => ucwords($userInfoByUserId->title . ' ' . $userInfoByUserId->firstname . ' ' . $userInfoByUserId->lastname),
                    'timezone' => $reportTime
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
                    'sNo' => ++$i,
                    'property_name' => $propertyName,
                    'building' => empty($buildingName) ? $propertyName : $buildingName,
                    'unit' => !empty($units->name) ? $units->name : '-',
                    'updated_at' => $log->updated_at->timezone($timezone)->format('m-d-Y h:i A'),
                    'type' => '-',
                    'status' => $log->text,
                    'employee_name' => ucwords($userInfoByUserId->title . ' ' . $userInfoByUserId->firstname . ' ' . $userInfoByUserId->lastname),
                    'timezone' => $reportTime
                ];
            }
        }

        \Excel::create(
            'Trash Scan Daily Service Report [ ' . $date . ' ]',
            function ($excel) use ($logsArray, $date) {
                $excel->setTitle('Trash Scan Daily Service Report - [' . $date . ']');
                $excel->setDescription('Trash Scan Dailyfsdfsdf Service Report - [' . $date . ']');
                $excel->sheet(
                    'sheet1',
                    function ($sheet) use ($logsArray) {
                        $sheet->fromArray($logsArray, null, 'A1', false, false);
                    }
                );
            }
        )
        ->download('xls');
    }

    public function automatedServiceReport(Request $request, $id)
    {
        $decodeid = \Hashids::decode($id);
        
        $users = \App\User::find($decodeid);
        
        if (is_null($users)) {
            return "Service Report Not Found.";
        }

        if ($request->has('date') && !is_null($request->date)) {
            $this->automatedService($users, $request->date);
        }

        if (!is_null($users)) {
            $getNotification = \App\UserNotification::select('user_id', 'day_frequency', 'type')
                ->where('subscriber_id', $users->subscriber_id)
                ->where('user_id', $users->id)
                ->where('day_frequency', '!=', 0)
                ->where('type', 7)
                ->first();


            if (is_null($getNotification)) {
                $reportName = "DailyServiceReport-$id";
            } elseif (!is_null($getNotification) && $getNotification->day_frequency == 1) {
                $reportName = "DailyServiceReport-$id";
            } elseif (!is_null($getNotification) && $getNotification->day_frequency == 2) {
                $reportName = "WeeklyServiceReport-$id";
            } elseif (!is_null($getNotification) && $getNotification->day_frequency == 3) {
                $reportName = "MonthlyServiceReport-$id";
            }
            
            if (!file_exists(public_path("uploads/pdf/$reportName.xls"))) {
                return "Service Report Not Found.";
            } else {
                return redirect(url("uploads/pdf/$reportName.xls"));
            }
        }
    }
    
    protected function automatedViolation($user, $date)
    {
        $users = \App\Subscriber::query()
            ->select('id', 'user_id')
            ->where('id', $user->subscriber_id)
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
        
        $violationArray[] = ['S.No', 'Username', 'Property', 'Rule', 'Action', 'Status', 'Details', 'Special Notes', 'Building', 'No.of Image', 'Created At', 'Property Timezone'];
        
        $i = 1;

        $timezone = $users->user->timezone;

        switch ($timezone) {
            case "America/Los_Angeles":
                $reportTime = 'PST (UTC-8:00 Hrs)';
                break;
            case "America/Chicago":
                $reportTime =  "CST (UTC-6:00 Hrs)";
                break;
            case "America/Denver":
                $reportTime =  "MST (UTC-7:00 Hrs)";
                break;
            default:
                $reportTime = "EST (UTC-5:00 Hrs)";
        }
        
        $startTime = \Carbon\Carbon::parse($date, $timezone)
            ->addHours(6)->copy();
        
        $endTime = \Carbon\Carbon::parse($date, $timezone)
            ->addDays(1)->addHours(5)->addMinutes(59)->addSeconds(59)->copy();
        
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
                'created_at' => $createdAt,
                'timezone' => $reportTime
            ];
        }
        
        \Excel::create(
            'Trash Scan Violation Report - [' . $date . ']',
            function ($excel) use ($violationArray, $date) {
                $excel->setTitle('Trash Scan Violation Report - [' . $date . ']');        
                $excel->setDescription('Trash Scan Violation Report - [' . $date . ']');
        
                $excel->sheet(
                    'sheet1',
                    function ($sheet) use ($violationArray) {
                        $sheet->fromArray($violationArray, null, 'A1', false, false);
                    }
                );
            }
        )
        ->download('xls');
    }

    public function automatedViolationReport(Request $request, $id)
    {
        $decodeid = \Hashids::decode($id);
        
        $users = \App\User::find($decodeid);
        
        if (is_null($users)) {
            return "Service Report Not Found.";
        }

        if ($request->has('date') && !is_null($request->date)) {
            $this->automatedViolation($users, $request->date);
        }
        
        if (!is_null($users)) {
            $getNotification = \App\UserNotification::select('user_id', 'day_frequency', 'type')
                ->where('subscriber_id', $users->subscriber_id)
                ->where('user_id', $users->id)
                ->where('day_frequency', '!=', 0)
                ->where('type', 10)
                ->first();
            
            if (is_null($getNotification)) {
                $reportName = "DailyViolationReport-$id";
            } elseif (!is_null($getNotification) && $getNotification->day_frequency == 1) {
                $reportName = "DailyViolationReport-$id";
            } elseif (!is_null($getNotification) && $getNotification->day_frequency == 2) {
                $reportName = "WeeklyViolationReport-$id";
            } elseif (!is_null($getNotification) && $getNotification->day_frequency == 3) {
                $reportName = "MonthlyViolationReport-$id";
            }
            
            //if (is_null($getNotification) || !file_exists(public_path("uploads/pdf/$reportName.xls")))
            if (!file_exists(public_path("uploads/pdf/$reportName.xls"))) {
                return "Service Report Not Found.";
            } else {
                return redirect(url("uploads/pdf/$reportName.xls"));
            }
        }
    }

    protected function automatedUnit($user, $date)
    {
        $users = \App\Subscriber::query()
            ->select('id', 'user_id')
            ->where('id', $user->subscriber_id)
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
                    
        $serviceArray[] = ['S.No', 'Address1', 'Address2', 'Unit Number', 'Activation Date', 'Property', 'Building', 'Floor', 'Latitude', 'Longitude', 'Barcode', 'Last Scan Date', 'Units', 'Created At', 'Updated At', 'Status', 'Property Timezone'];

        $timezone = $users->user->timezone;

        switch ($timezone) {
            case "America/Los_Angeles":
                $reportTime = 'PST (UTC-8:00 Hrs)';
                break;
            case "America/Chicago":
                $reportTime =  "CST (UTC-6:00 Hrs)";
                break;
            case "America/Denver":
                $reportTime =  "MST (UTC-7:00 Hrs)";
                break;
            default:
                $reportTime = "EST (UTC-5:00 Hrs)";
        }

        $startTime = \Carbon\Carbon::parse($date, $timezone)
            ->addHours(6)->copy();
        
        $endTime = \Carbon\Carbon::parse($date, $timezone)
            ->addDays(1)->addHours(5)->addMinutes(59)->addSeconds(59)->copy();
        
        $i = 1;
        
        $getProperties = $users->getProperties;
        
        foreach ($getProperties as $property) {
            foreach ($property->getUnit as $getUnit) {
                $lastScanDate = \App\Activitylogs::query()
                    ->select('created_at')
                    ->where('barcode_id', $getUnit->barcode_id)
                    ->latest()
                    ->first();

                $serviceArray[] = [
                    'S.No' => $i++,
                    'Address1' => $getUnit->address1,
                    'Address2' => $getUnit->address2,
                    'Unit Number' => $getUnit->unit_number,
                    'Activation Date'  => !empty($getUnit->activation_date) ? \Carbon\Carbon::parse($getUnit->activation_date)->timezone($timezone)->format('m-d-Y h:i A') : '',
                    'Property'  => $property->name,
                    'Building'  => $getUnit->building,
                    'Floor' => $getUnit->floor,
                    'Latitude'  => $getUnit->latitude,
                    'Longitude' => $getUnit->longitude,
                    'Barcode'  => $getUnit->barcode_id,
                    'Last Scan Date'  => !empty($lastScanDate->created_at) ? $lastScanDate->created_at->timezone($timezone)->format('m-d-Y h:i A') : '',
                    'Units'  => empty($getUnit->is_route) ? 'Unit':'Route Checkpoint',
                    'Created At'  => $getUnit->created_at->timezone($timezone)->format('m-d-Y h:i A'),
                    'Updated At' => $getUnit->updated_at->timezone($timezone)->format('m-d-Y h:i A'),
                    'Status' => !empty($getUnit->is_active) ? 'Active' : 'In-active',
                    'timezone' => $reportTime
                ];
            }
        }

        \Excel::create(
            'Trash Scan unit report - [' . $date . ']',
            function ($excel) use ($serviceArray, $date) {
                $excel->setTitle('Trash Scan unit report - [' . $date . ']');
        
                $excel->setDescription('Trash Scan unit report - [' . $date . ']');
        
                $excel->sheet(
                    'sheet1',
                    function ($sheet) use ($serviceArray) {
                        $sheet->fromArray($serviceArray, null, 'A1', false, false);
                    }
                );
            }
        )
        ->download('xls');
    }

    public function automatedUnitReport(Request $request, $id)
    {
        $decodeid = \Hashids::decode($id);
        
        $users = \App\User::find($decodeid);
        
        if (is_null($users)) {
            return "Service Report Not Found.";
        }
        
        if ($request->has('date') && !is_null($request->date)) {
            $this->automatedUnit($users, $request->date);
        }

        if (!is_null($users)) {
            $getNotification = \App\UserNotification::select('user_id', 'day_frequency', 'type')
                ->where('subscriber_id', $users->subscriber_id)
                ->where('user_id', $users->id)
                ->where('day_frequency', '!=', 0)
                ->where('type', 8)
                ->first();
            
            if (!is_null($getNotification) && $getNotification->day_frequency == 1) {
                $reportName = "DailyUnitReport-$id";
            } elseif (!is_null($getNotification) && $getNotification->day_frequency == 2) {
                $reportName = "WeeklyUnitReport-$id";
            } elseif (!is_null($getNotification) && $getNotification->day_frequency == 3) {
                $reportName = "MonthlyUnitReport-$id";
            }
            
            if (is_null($getNotification) || !file_exists(public_path("uploads/pdf/$reportName.xls"))) {
                return "Service Report Not Found.";
            } else {
                return redirect(url("uploads/pdf/$reportName.xls"));
            }
        }
    }

    protected function automatedClockInOut($user, $date)
    {
        $users = \App\Subscriber::query()
            ->select('id', 'user_id')
            ->where('id', $user->subscriber_id)
            ->with(
                [
                    'user' => function ($query) {
                        $query->select('id', 'email', 'subscriber_id', 'is_admin', 'timezone');
                    }
                ]
            )
            ->withTrashed()
        ->first();
        
        $clockArray[] = ['S.No', 'Name', 'Reporting Manager', 'Clockin', 'Clockout', 'Reason', 'Property Timezone'];

        $timezone = $users->user->timezone;

        switch ($timezone) {
            case "America/Los_Angeles":
                $reportTime = 'PST (UTC-8:00 Hrs)';
                break;
            case "America/Chicago":
                $reportTime =  "CST (UTC-6:00 Hrs)";
                break;
            case "America/Denver":
                $reportTime =  "MST (UTC-7:00 Hrs)";
                break;
            default:
                $reportTime = "EST (UTC-5:00 Hrs)";
        }
        
        $startTime = \Carbon\Carbon::parse($date, $timezone)
            ->addHours(6)->copy();
        
        $endTime = \Carbon\Carbon::parse($date, $timezone)
            ->addDays(1)->addHours(5)->addMinutes(59)->addSeconds(59)->copy();
        
        $i = 1;
        
        $clock = \App\ClockInOut::where(
            function ($query) use ($user) {
                $query->whereIn(
                    'user_id',
                    function ($query) use ($user) {
                        $query->select('id')
                            ->from('users')
                            ->whereNotIn('role_id', [10])
                            ->whereNull('deleted_at')
                            ->where('subscriber_id', $user->subscriber_id);
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
                'timezone' => $reportTime
            ];
        }

        \Excel::create(
            'Trash Scan clock in/out Report - [' . $date . ']',
            function ($excel) use ($clockArray, $date) {
                $excel->setTitle('Trash Scan Daliy clock in/out Report - [' . $date . ']');
                $excel->setDescription('Trash Scan Daliy clock in/out Report Report - [' . $date . ']');
        
                $excel->sheet(
                    'sheet1',
                    function ($sheet) use ($clockArray) {
                        $sheet->fromArray($clockArray, null, 'A1', false, false);
                    }
                );
            }
        )
        ->download('xls');
    }

    public function automatedClockinoutReport(Request $request, $id)
    {   
        $decodeid = \Hashids::decode($id);
        
        $users = \App\User::find($decodeid);
        
        if (is_null($users)) {
            return "Service Report Not Found.";
        }

        if ($request->has('date') && !is_null($request->date)) {
            $this->automatedClockInOut($users, $request->date);
        }

        if (!is_null($users)) {
            $getNotification = \App\UserNotification::select('user_id', 'day_frequency', 'type')
                ->where('subscriber_id', $users->subscriber_id)
                ->where('user_id', $users->id)
                ->where('day_frequency', '!=', 0)
                ->where('type', 9)
                ->first();
            
            if (!is_null($getNotification) && $getNotification->day_frequency == 1) {
                $reportName = "DailyClockInOutReport-$id";
            } elseif (!is_null($getNotification) && $getNotification->day_frequency == 2) {
                $reportName = "WeeklyClockInOutReport-$id";
            } elseif (!is_null($getNotification) && $getNotification->day_frequency == 3) {
                $reportName = "MonthlyClockInOutReport-$id";
            }
            
            if (is_null($getNotification) || !file_exists(public_path("uploads/pdf/$reportName.xls"))) {
                return "Service Report Not Found.";
            } else {
                return redirect(url("uploads/pdf/$reportName.xls"));
            }
        }
    }

    public function symbolicLink($folder, $filename)
    {
        $url = Storage::disk('s3')
            ->temporaryUrl('uploads/' .  $folder . '/' . $filename, \Carbon\Carbon::now()->addMinutes(30));

        $mimeType = Storage::disk('s3')
            ->getMimeType('uploads/' .  $folder . '/' . $filename);

        $path = file_get_contents($url);

        return \Response::make(
            $path,
            200,
            [
                'Content-Type' =>  $mimeType,

            ]
        );
    }

    public function residents($id)
    {
        if ($this->checkResidentAlert($id)) {
            return redirect('unauthorized');
        }

        $property = \App\Property::where('id', $id)
            ->with(
                [
                    'getBuildingIsActiveUnit'
                ]
            )
        ->first();

        return view('guest.residents', compact('property'));
    }

    public function getResidents(Request $request)
    {
        $property = \App\Property::query()
            ->select('reminder', 'id')
            ->whereIn(
                'id',
                function ($query) {
                    $query->select('property_id')
                        ->from('units');
                }
            )
            ->with(
                [
                    'checkInProperty' => function ($query) {
                        $query->select('property_id', \DB::raw("DATE_FORMAT(created_at,'%Y-%m-%d %H:%i:%s') as create_at"))
                            ->latest();
                    }
                ]
            )
        ->first();

        return response()
            ->json(
                [
                    'property' => $property
                ]
            );
    }

    public function updateResidents(Request $request)
    {
        $validated = $request->validate(
            [
                'unitId' => 'bail|required|integer',
                'firstname' => 'bail|required|string',
                'lastname' => 'bail|required|string',
                'email' => 'bail|required|email|unique:service_alert_residents',
                'mobile' => 'bail|required|min:11|numeric',
                'iagree' => 'accepted'
            ],
            [
                'iagree.accepted' => 'The checkbox must be accepted.'
            ]
        );

        $pde = \App\Units::select('id', 'property_id', 'building_id')
            ->with(
                [
                    'getPropertyDetail' => function ($query) {
                        $query->select('id', 'name');
                    }
                ]
            )
        ->find($request->unitId);
        
        $create = \App\ServiceAlertResidents::create(
            [
                'fname' => $request->firstname,
                'lname' => $request->lastname,
                'mobile' => $request->mobile,
                'email' => $request->email,
                'property_id' => $pde->property_id,
                'building_id' => $pde->building_id,
                'unit_id' => $request->unitId
            ]
        );

        $check = \App\Resident::where('unit_id', $request->unitId)->first();

        $data = [
            'message' => 'Trash Scan App: Welcome! Reply YES to opt-in and receive future Valet Trash Service Alerts for your community ' . ucwords($pde->getPropertyDetail->name) . '. Msg&data rates may apply.',
            'logo' => getLogo()['logo'],
            'companyName' => getLogo()['companyName'],
        ];

        Mail::to($request->email)->send(new updateResidentSendEmail($data));

        $mobile = '+1' . $request->mobile . '';
        $content = 'Trash Scan App: ';
        $content .= 'Welcome! Reply YES to opt-in and receive future Valet Trash Service Alerts for your community ' . ucwords($pde->getPropertyDetail->name) . '. Msg&data rates may apply.';
        sms($mobile, $content);

        return redirect()
            ->back()
            ->withSuccess('Hello ' . ucwords($request->firstname . ' ' . $request->lastname) . ' you have successfully enrolled the service alert.');
    }

    public function updateResidentsBackUp(Request $request)
    {
        $validated = $request->validate(
            [
                'unitId' => 'bail|required|integer',
                'firstname' => 'bail|required|string',
                'lastname' => 'bail|required|string',
                'email' => 'bail|required|email',
                'mobile' => 'bail|required|min:11|numeric',
                'iagree' => 'accepted'
            ],
            [
                'iagree.accepted' => 'The checkbox must be accepted.'
            ]
        );

        $check = \App\Resident::where('unit_id', $request->unitId)->first();
        
        if (!is_null($check)) {
            \App\Resident::query()
                ->where('id', $check->id)
                ->update(
                    [
                        'is_alert' => 1
                    ]
                );
        } elseif (!empty($request->unitId)) {
            $p = \App\Property::select('name')->where('id', $request->propertyId)->first();
            $add = \App\Resident::create(
                [
                    'firstname' => $request->firstname,
                    'lastname' => $request->lastname,
                    'mobile' => $request->mobile,
                    'email' => $request->email,
                    'subscriber_id' => 1,
                    'property_id' => $request->propertyId,
                    'unit_id' => $request->unitId
                ]
            );

            \App\ResidentsUnit::create(
                [
                    'residents_id' => $add->id,
                    'unit_id' => $add->unit_id,
                    'move_in_date' => \Carbon\Carbon::now()
                ]
            );
        }

        $data = [
            'message' => 'Trash Scan App: Welcome! Reply YES to opt-in and receive future Valet Trash Service Alerts for your community ' . ucwords($p->name) . '. Msg&data rates may apply.',
            'logo' => getLogo()['logo'],
            'companyName' => getLogo()['companyName'],
        ];

        Mail::to($request->email)->send(new updateResidentSendEmail($data));

        $mobile = '+1' . $request->mobile . '';
        $content = 'Trash Scan App: ';
        $content .= 'Welcome! Reply YES to opt-in and receive future Valet Trash Service Alerts for your community ' . ucwords($p->name) . '. Msg&data rates may apply.';
        sms($mobile, $content);

        return redirect()
            ->back()
            ->withSuccess('Hello ' . ucwords($request->firstname . ' ' . $request->lastname) . ' you have successfully enrolled the service alert.');
    }

    protected function checkResidentAlert($pid)
    {
        $p = \App\Property::query()
            ->select('id', 'subscriber_id')
            ->where(
                [
                    'id' => $pid,
                    'resident_alert' => 1,
                ]
            )
        ->first();

        if (!is_null($p)) {
            $s = \App\Subscriber::query()
                ->select('id')
                ->where(
                    [
                        'id' => $p->subscriber_id,
                        'resident_alert' => 1,
                    ]
                )
            ->first();
        }

        return is_null($p) || is_null($s) ? true : false;
    }
}
