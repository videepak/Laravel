<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ManageReportController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('RoleAndPermission:report');

        /* If the user is not admin then apply permission validation. */
        //        if (!$this->user->hasRole(['admin', 'property_manager'])) {
        //            if (!$this->user->can('report')) {
        //                return redirect('/home');
        //            }
        //        }
    }

    public function index(Request $request)
    {
        $days_in_month = date('t');
        $data = [];
        $condition = request()->segment(2);
        $id = request()->segment(3);
        $logs_array = $pro_id = [];

        $weekStartDate = \Carbon\Carbon::now()->timezone(getUserTimezone())
            ->startOfWeek()->subWeek()->subDays(1)->format('Y-m-d') . ' 06:00:00';
        
        $weekEndDate = \Carbon\Carbon::now()->timezone(getUserTimezone())
            ->endOfWeek()->subWeek()->format('Y-m-d') . ' 05:59:59';

        //Property list for select box: Start
        $properties = $this->getProperty(); //dd($properties[0]->getUnit);
        $this->data['properties'] = $properties;
        //Property list for select box: End

        if ($condition == 'property' && !empty($id)) {
            $pro_id[] = $id;
            $this->data['condition_id'] = $id;
        } elseif ($properties->isNotEmpty()) {
            $pro_id = $properties->map(function ($val, $key) {
                return $val->id;
            });
        }

        $overall_waste = $overall_recyle = $waste = $recycle = 0;

        $user_ids = \App\User::select('id')
            ->when(
                $this->user->role_id != 1,
                function ($query) {
                    $query->where('user_id', $this->user->id);
                }
            )
            ->when(
                $this->user->role_id == 1,
                function ($query) {
                    $query->where('subscriber_id', $this->user->subscriber_id);
                }
            )
        ->get();
                
        $total_logs = \App\Activitylogs::select('id', 'user_id', 'text', 'barcode_id', 'wast', 'recycle', 'updated_by', 'property_id', 'building_id', 'type', 'created_at', 'updated_at')
        ->when(
            !$this->user->hasRole('property_manager'),
            function ($query) use ($user_ids) {
                $query->where(
                    function ($query) use ($user_ids) {
                        $query->whereRaw("user_id in (" . $user_ids->implode('id', ', ') . ")")
                            ->orWhereRaw("updated_by in (" . $user_ids->implode('id', ', ') . ")");
                    }
                );
            }
        )
        ->whereBetween(
            \DB::raw("convert_tz(updated_at, 'UTC','" . getUserTimezone() . "')"),
            [
                $weekStartDate,
                $weekEndDate,
            ]
        )
        ->where('type', 2)
        //->whereIn('barcode_id', $barcode)
        ->when(
            !empty($pro_id),
            function ($query) use ($pro_id) {
                $query->whereRaw("barcode_id in (select `barcode_id` from `units` where `property_id` in (" . collect($pro_id)->implode(', ') . ") and `is_active` = 1 and `units`.`deleted_at` is null)");
            }
        )
        ->with(
            [
                'getUserDetail' => function ($query) {
                    $query->select('id', 'title', 'firstname', 'lastname', 'mobile', 'role_id', 'subscriber_id', 'user_id')
                        ->withTrashed();
                },
                'getUserDetailUpdatedBy' => function ($query) {
                    $query->select('id', 'title', 'firstname', 'lastname', 'mobile', 'role_id', 'subscriber_id', 'user_id')
                        ->withTrashed();
                },
                'unit' => function ($query) {
                    $query->select('id', 'property_id', 'building_id', 'barcode_id', 'created_at', 'updated_at')
                        ->where('is_active', '1')
                        ->where('is_route', 0)
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
        ->latest()
        ->get();
            
        foreach ($total_logs as $log) { //dd($log->unit->getBuildingDetail);
            $propertyName = $buildingName = '';
            $userInfoByUserId = $log->getUserDetail;
            $userInfoByUpdatedBy = $log->getUserDetailUpdatedBy;
            $units = $log->unit;

            if (isset($units->property_id)) {
                if (empty($log->getProperty->service)) {
                    continue;
                }

                $services = $log->getProperty->service;
                $propertyunit = $log->getProperty;
                
                if (empty($propertyunit->units)) {
                    continue;
                }
                
                $totalwaste = $propertyunit->units * $services->waste_weight;
                $getwaste = ($totalwaste * $services->waste_reduction_target) / 100;
                $new_west_target = round($totalwaste - $getwaste);

                if ($log->type == 2 && $log->wast == 1 && $log->recycle == null) {
                    //$status = 'Waste Collection';
                    $overall_waste = $services->waste_weight;
                }

                if ($log->type == 2 && $log->recycle == 1 && $log->wast == null) {
                    // $status = 'Recycle Collection';
                    $overall_recyle = $services->recycle_weight;
                }

                if ($log->type == 2 && $log->recycle == 1 && $log->wast == 1) {
                    //$status = 'Waste And Recycle Collection';
                    $overall_recyle = $services->recycle_weight;
                    $overall_waste = $services->waste_weight;
                }

                if (isset($propertyunit->name)) {
                    $propertyName = $propertyunit->name;
                    $type = $propertyunit->type;
                }

                if (isset($log->unit->getBuildingDetail->building_name)) {
                    $buildingName = $log->unit->getBuildingDetail->building_name;
                }

                $employee = $userInfoByUserId->title
                        . ' ' . $userInfoByUserId->firstname
                        . ' ' . $userInfoByUserId->lastname;

                $logs_array[] = [
                    'service_agreement' => $services->id,
                    'latitude' => $log->latitude,
                    'longitude' => $log->longitude,
                    'updated_at' => $log->updated_at,
                    'status' => $log->text,
                    'waste_total' => $overall_waste,
                    'recycle_total' => $overall_recyle,
                    'unit' => $units->unit_number,
                    'employee_name' => ucwords($employee),
                    'property_name' => $propertyName,
                    'property_id' => $units->property_id,
                    'type' => $type,
                    'waste_target' => $new_west_target,
                    'building_name' => $buildingName,
                ];
            }
        }

        $this->data['logs_array'] = $logs_array;

        ////////////////////Pagiante End////////////////////////////

        $this->data['condition_id'] = $condition . '/ ' . $id;
        
        if (isset($pro_id)) {
            /////////START CHART/////////////
            $user_detail = \App\User::find($this->user->id);
            $user_ids = \App\User::where('subscriber_id', $user_detail->subscriber_id)->pluck('id');
            $chart_array = [];
            $this->data['qrcodeDetail'] = \App\Units::where('is_route', 0)->get();
            $this->data['qrcodeActive'] = \App\Units::where('is_active', 1)->where('is_route', 0)->get();

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

            /* Calculation */
            $start_week_data = $this->recycleWasteCal($start_week, $user_ids, $pro_id);
            $monday_data = $this->recycleWasteCal($monday, $user_ids, $pro_id);
            $tuesday_data = $this->recycleWasteCal($tuesday, $user_ids, $pro_id);
            $wednesday_data = $this->recycleWasteCal($wednesday, $user_ids, $pro_id);
            $thursday_data = $this->recycleWasteCal($thursday, $user_ids, $pro_id);
            $friday_data = $this->recycleWasteCal($friday, $user_ids, $pro_id);
            $end_week_data = $this->recycleWasteCal($end_week, $user_ids, $pro_id);

            $first_day_this_month = date('Y-m-01 00:00:00'); // hard-coded '01' for first day
            $last_day_this_month = date('Y-m-d 12:59:59');

            $monthly_data = $this->recycleWasteCalMonthly($first_day_this_month, $last_day_this_month, $user_ids, $pro_id);
            if (empty($monthly_data)) {
                $monthly_data['average'] = 0;
            }
            /* Calculation */

            $recyclemonthly = [['Genre', 'Waste Collected', 'Recycle Collected', 'Average',
                ],
                [
                    date(
                        'm-d-Y',
                        strtotime($start_week)
                    ),
                    $start_week_data['waste'],
                    $start_week_data['recycle'],
                    $monthly_data['average'], ],
                [
                    date(
                        'm-d-Y',
                        strtotime($monday)
                    ),
                    $monday_data['waste'],
                    $monday_data['recycle'],
                    $monthly_data['average'], ],
                [
                    date(
                        'm-d-Y',
                        strtotime($tuesday)
                    ),
                    $tuesday_data['waste'],
                    $tuesday_data['recycle'],
                    $monthly_data['average'], ],
                [
                    date(
                        'm-d-Y',
                        strtotime($wednesday)
                    ),
                    $wednesday_data['waste'],
                    $wednesday_data['recycle'],
                    $monthly_data['average'], ],
                [
                    date(
                        'm-d-Y',
                        strtotime($thursday)
                    ),
                    $thursday_data['waste'],
                    $thursday_data['recycle'],
                    $monthly_data['average'], ],
                [
                    date(
                        'm-d-Y',
                        strtotime($friday)
                    ),
                    $friday_data['waste'],
                    $friday_data['recycle'],
                    $monthly_data['average'], ],
                [
                    date(
                        'm-d-Y',
                        strtotime($end_week)
                    ),
                    $end_week_data['waste'],
                    $end_week_data['recycle'],
                    $monthly_data['average'], ],
            ];
        } else {
            $recyclemonthly = [
                ['Genre', 'Waste Collected', 'Recycle Collected', 'Average'],
                [date('m-d-Y'), 0, 0, 0],
                [date('m-d-Y'), 0, 0, 0],
                [date('m-d-Y'), 0, 0, 0],
                [date('m-d-Y'), 0, 0, 0],
                [date('m-d-Y'), 0, 0, 0],
                [date('m-d-Y'), 0, 0, 0],
                [date('m-d-Y'), 0, 0, 0],
            ];
        }

        $this->data['chartarr'] = json_encode($recyclemonthly);

        return view('report.recycle', $this->data);
    }

    public function deliveryData(Request $request)
    {
        $logsArray = $pro_id = [];
        $id = $request->id;
        $properties = $this->getProperty();
        $i = $request->start;
        $scanBy = $request->scanBy;
        $pickupType = $request->pickupType;
        $search = $request->search['value'];
        $startTime = \Carbon\Carbon::parse($request->startTime, getUserTimezone())->addHours(6)->copy();
        $endTime = \Carbon\Carbon::parse($request->endTime, getUserTimezone())
        ->addDays(1)->addHours(5)->addMinutes(59)->addSeconds(59)->copy();

        if (!empty($id)) {
            $pro_id[] = $id;
        } elseif ($properties->isNotEmpty()) {
            $pro_id = $properties->map(
                function ($val, $key) {
                    return $val->id;
                }
            );
        }

        //Get active unit according to property id: Start
        $barcode = \App\Units::select('barcode_id')
            ->where('is_active', 1)
            //->where('is_route', 0)
            ->whereIn('property_id', $pro_id)
            ->when(
                !empty($search),
                function ($query) use ($search) {
                    $query->where('unit_number', 'like', "%$search%")
                    ->orWhere('building', 'like', "%$search%");
                }
            )
            ->withTrashed()
            ->get()->map(
                function ($val, $key) {
                    return $val->barcode_id;
                }
            );

        //Get active unit according to property id: End

        $overall_waste = $overall_recyle = $waste = $recycle = 0;

        //Get user id for employee or admin role: Start
        if ($scanBy) {
            $user_ids = collect($scanBy);
        } else {
            $user_ids = \App\User::select('id')
                ->when(
                    !$this->user->hasRole('admin'),
                    function ($query) {
                        $query->where('user_id', $this->user->id);
                    }
                )
                ->when(
                    $this->user->hasRole('admin'),
                    function ($query) {
                        $query->where('subscriber_id', $this->user->subscriber_id);
                    }
                )
            ->withTrashed()
            ->get();
        }
        //Get user id for employee or admin role: End
        //Get total record: Start
        $total_re = \App\Activitylogs::when(
            !$this->user->hasRole('property_manager') && !empty($user_ids) && $user_ids->isNotEmpty(),
            function ($query) use ($user_ids) {
                $query->where(
                    function ($query) use ($user_ids) {
                        $query->whereRaw("user_id in (" . $user_ids->implode('id', ', ') . ")")
                            ->orWhereRaw("updated_by in (" . $user_ids->implode('id', ', ') . ")");
                    }
                );
            }
        )
        ->where(
            function ($query) use ($barcode, $search, $pro_id) {
                $query->whereIn('barcode_id', $barcode)
                ->when(
                    empty($search),
                    function ($query) use ($pro_id) {
                        $query->orWhereIn('property_id', $pro_id);
                    }
                );
            }
        )
        ->whereBetween(
            \DB::raw("convert_tz(updated_at, 'UTC','" . getUserTimezone() . "')"),
            [
                $startTime,
                $endTime,
            ]
        )
        ->when(
            !empty($id),
            function ($query) use ($id) {
                $query->where('property_id', $id);
            }
        )
        ->when(
            !empty($pickupType),
            function ($query) use ($pickupType) {
                $query->where('type', $pickupType);
            },
            function ($query) {
                $query->whereIn('type', [2, 3, 6, 8, 5, 11, 12]);
            }
        )
        ->withTrashed()
        ->latest()
        ->get();
        //Get total record: End
        //Get record by limit: Start
        $total_logs = \App\Activitylogs::when(
            !$this->user->hasRole('property_manager') && !empty($user_ids) && $user_ids->isNotEmpty(),
            function ($query) use ($user_ids) {
                $query->where(
                    function ($query) use ($user_ids) {
                        $query->whereRaw("user_id in (" . $user_ids->implode('id', ', ') . ")")
                        ->orWhereRaw("updated_by in (" . $user_ids->implode('id', ', ') . ")");
                    }
                );
            }
        )
        ->where(
            function ($query) use ($barcode, $search, $pro_id) {
                $query->whereIn('barcode_id', $barcode)
                ->when(
                    empty($search),
                    function ($query) use ($pro_id) {
                        $query->orWhereIn('property_id', $pro_id);
                    }
                );
            }
        )
        ->whereBetween(
            \DB::raw("convert_tz(updated_at, 'UTC','" . getUserTimezone() . "')"),
            [
                $startTime,
                $endTime,
            ]
        )
        ->when(
            !empty($id),
            function ($query) use ($id) {
                $query->where('property_id', $id);
            }
        )
        ->when(
            !empty($pickupType),
            function ($query) use ($pickupType) {
                $query->where('type', $pickupType);
            },
            function ($query) {
                $query->whereIn('type', [2, 3, 6, 8, 5, 11, 12]);
            }
        )
        ->with(
            [
                'getUserDetail' => function ($query) {
                    $query->select('id', 'title', 'firstname', 'lastname', 'mobile', 'role_id', 'subscriber_id', 'user_id')
                        ->withTrashed();
                },
                'unit' => function ($query) {
                    $query->select('id', 'unit_number', 'property_id', 'building_id', 'barcode_id', 'created_at', 'updated_at')
                        //->where('is_active', '1')
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
        ->withTrashed()
        ->latest()
        ->limit($request->length)->offset($request->start)
        //->limit(1)->offset(3)
        ->get();
        //Get record by limit: Start
              
        foreach ($total_logs as $log) {
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
                $vio = \App\Violation::where('barcode_id', $log->barcode_id)->withTrashed()->first();

                if (is_null($vio)) {
                    continue;
                }

                $units = $log->unit;
            } else {
                $units = $log->unit;
            }

            if (isset($units->property_id)) {
                if (isset($property->service)) {
                    $services = $property->service;
                }

                if (isset($services) && $log->type == 2 && $log->wast == 1 && $log->recycle == null) {
                    $type = 'Waste Total: ' . $services->waste_weight;
                }

                if (isset($services) && $log->type == 2 && $log->recycle == 1 && $log->wast == null) {
                    $type = 'Recycle Total: ' . $services->recycle_weight;
                }
                
                if (isset($services) && $log->type == 2 && $log->recycle == 1 && $log->wast == 1) {
                    $type = 'Waste Total:'
                            . $services->recycle_weight
                            . '<br/> Recycle Total:'
                            . $services->waste_weight;
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
                    'updated_at' => $log->updated_at->timezone(getUserTimezone())->format('m-d-Y h:i A'),
                    'type' => !empty($type) ? $type : '-',
                    'status' => $log->text,
                    'employee_name' => ucwords($userInfoByUserId->title . ' '
                    . $userInfoByUserId->firstname . ' ' . $userInfoByUserId->lastname),
                ];
            } elseif ($log->type == 8 || $log->type == 5 || $log->type == 12) {
                if (isset($property->name)) {
                    $propertyName = $property->name;
                    $propertyId = $property->id;
                }

                $buildingName = \App\Building::query()
                        ->select('building_name')
                        ->where('id', $log->building_id)
                        ->first();

                $logsArray[] = [
                    'sNo' => ++$i,
                    'property_name' => $propertyName,
                    'building' => is_null($buildingName) ? $propertyName : $buildingName->building_name,
                    //'building' => empty($buildingName) ? $propertyName : $buildingName,
                    'unit' => !empty($units->unit_number) ? $units->unit_number : '-',
                    'updated_at' => $log->updated_at->timezone(getUserTimezone())->format('m-d-Y h:i A'),
                    'type' => '-',
                    'status' => $log->text,
                    'employee_name' => ucwords($userInfoByUserId->title
                        . ' ' . $userInfoByUserId->firstname
                        . ' ' . $userInfoByUserId->lastname),
                ];
            }
        }
        
        return json_encode(
            [
                'draw' => intval($request->draw),
                'recordsTotal' => !empty($total_re) ? $total_re->count() : 0,
                'recordsFiltered' => !empty($total_re) ? $total_re->count() : 0,
                'data' => $logsArray,
            ]
        );
    }

    public function deliverychart(Request $request)
    {
        $days_in_month = date('t');
        $data = [];
        $condition = request()->segment(2);
        $id = request()->segment(3);
        $logs_array = $barcode = $pro_id = [];

        //Property list for select box: Start
        $properties = $this->getProperty();
        $this->data['properties'] = $this->propertyList()->orderBy('name')->get();
        //Property list for select box: End

        if ($condition == 'property' && !empty($id)) {
            $pro_id[] = $id;
            $this->data['condition_id'] = $id;
        } elseif ($properties->isNotEmpty()) {
            $pro_id = $properties
                ->map(
                    function ($val, $key) {
                        return $val->id;
                    }
                );
        }

        $barcode = \App\Units::select('barcode_id')
            ->whereIn('property_id', $pro_id)
            ->where('is_active', 1)
            ->get()
            ->map(
                function ($val, $key) {
                    return $val->barcode_id;
                }
            );

        $barcode[] = 'NULL';

        if ($condition == 'property' && !empty($id)) {
            $prop = [$id];
        } elseif ($properties->isNotEmpty()) {
            //$prop = $this->propertyList()->pluck('id');
            $prop = $properties->pluck('id');
        }

        $overall_waste = $overall_recyle = $waste = $recycle = 0;

        //Get user id for employee or admin role: Start
        $user_ids = \App\User::select('id', \DB::raw("CONCAT_WS(' ', `title`, `firstname`, `lastname`) as name"))
        ->when(
            $this->user->role_id == getAdminId(),
            function ($query) {
                $query->where('subscriber_id', $this->user->subscriber_id);
            },
            function ($query) {
                $query->where('id', $this->user->id);
            }
        )
        ->orderBy('title')
        ->withTrashed()
        ->get();

        //Get user id for employee or admin role: End
        // $total_logs = \App\Activitylogs::when(
        //     !$this->user->hasRole('property_manager')
        //     && !empty($user_ids),
        //     function ($query) use ($user_ids) {
        //         $query->where(function ($query) use ($user_ids) {
        //             $query->whereRaw("user_id in (" . $user_ids->implode('id', ', ') . ")")
        //             ->orWhereRaw("updated_by in (" . $user_ids->implode('id', ', ') . ")");
        //         });
        //     }
        // )
        // ->where(
        //     function ($query) use ($barcode) {
        //         $query->whereIn('barcode_id', $barcode)
        //             ->orWhereNull('barcode_id');
        //     }
        // )
        // ->when(
        //     !empty($id),
        //     function ($query) use ($id) {
        //         $query->where('property_id', $id);
        //     }
        // )
        // ->whereIn('type', [2, 3, 6, 8, 5])
        // ->latest()
        // ->get();

        $chart_array = [];
        $usersId = $user_ids->pluck('id')->toArray();
        $this->data['scanBy'] = $user_ids;
        // $this->data['qrcodeDetail'] = \App\Units::all();
        // $this->data['qrcodeActive'] = \App\Units::where('is_active', 1)->get();
        
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

        /* Calculation */
        $pickupstart_week_data = $this->pickupWasteCal($start_week, $usersId, $barcode);
        $pickupmonday_data = $this->pickupWasteCal($monday, $usersId, $barcode);
        $pickuptuesday_data = $this->pickupWasteCal($tuesday, $usersId, $barcode);
        $pickupwednesday_data = $this->pickupWasteCal($wednesday, $usersId, $barcode);
        $pickupthursday_data = $this->pickupWasteCal($thursday, $usersId, $barcode);
        $pickupfriday_data = $this->pickupWasteCal($friday, $usersId, $barcode);
        $pickupend_week_data = $this->pickupWasteCal($end_week, $usersId, $barcode);

        $recyclestart_week_data = $this->recycleDeliveryWastCal($start_week, $usersId, $barcode);
        $recyclemonday_data = $this->recycleDeliveryWastCal($monday, $usersId, $barcode);
        $recycletuesday_data = $this->recycleDeliveryWastCal($tuesday, $usersId, $barcode);
        $recyclewednesday_data = $this->recycleDeliveryWastCal($wednesday, $usersId, $barcode);
        $recyclethursday_data = $this->recycleDeliveryWastCal($thursday, $usersId, $barcode);
        $recyclefriday_data = $this->recycleDeliveryWastCal($friday, $usersId, $barcode);
        $recycleend_week_data = $this->recycleDeliveryWastCal($end_week, $usersId, $barcode);

        $viostart_week_data = $this->violationWasteCal($start_week, $usersId, $barcode);
        $viomonday_data = $this->violationWasteCal($monday, $usersId, $barcode);
        $viotuesday_data = $this->violationWasteCal($tuesday, $usersId, $barcode);
        $viowednesday_data = $this->violationWasteCal($wednesday, $usersId, $barcode);
        $viothursday_data = $this->violationWasteCal($thursday, $usersId, $barcode);
        $viofriday_data = $this->violationWasteCal($friday, $usersId, $barcode);
        $vioend_week_data = $this->violationWasteCal($end_week, $usersId, $barcode);

        $notestart_week_data = $this->notesWasteCal($start_week, $usersId, $barcode);
        $notemonday_data = $this->notesWasteCal($monday, $usersId, $barcode);
        $notetuesday_data = $this->notesWasteCal($tuesday, $usersId, $barcode);
        $notewednesday_data = $this->notesWasteCal($wednesday, $usersId, $barcode);
        $notethursday_data = $this->notesWasteCal($thursday, $usersId, $barcode);
        $notefriday_data = $this->notesWasteCal($friday, $usersId, $barcode);
        $noteend_week_data = $this->notesWasteCal($end_week, $usersId, $barcode);

        $first_day_this_month = date('Y-m-01 00:00:00');
        $last_day_this_month = date('Y-m-d 12:59:59');

        $delivery = [['Genre', 'Waste Collected', 'Recycle Collected', 'Notes', 'Violations',
            ],
            [date(
                'm-d-Y',
                strtotime($start_week)
            ),
                $pickupstart_week_data['pickup'],
                $recyclestart_week_data['recycles'],
                $notestart_week_data['notes'],
                $viostart_week_data['violation'], ],
            [date(
                'm-d-Y',
                strtotime($monday)
            ),
                $pickupmonday_data['pickup'],
                $recyclemonday_data['recycles'],
                $notemonday_data['notes'],
                $viomonday_data['violation'], ],
            [date(
                'm-d-Y',
                strtotime($tuesday)
            ),
                $pickuptuesday_data['pickup'],
                $recycletuesday_data['recycles'],
                $notetuesday_data['notes'],
                $viotuesday_data['violation'], ],
            [date(
                'm-d-Y',
                strtotime($wednesday)
            ),
                $pickupwednesday_data['pickup'],
                $recyclewednesday_data['recycles'],
                $notewednesday_data['notes'],
                $viowednesday_data['violation'], ],
            [date(
                'm-d-Y',
                strtotime($thursday)
            ),
                $pickupthursday_data['pickup'],
                $recyclethursday_data['recycles'],
                $notethursday_data['notes'],
                $viothursday_data['violation'], ],
            [date(
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

        return view('report.delivery', $this->data);
    }

    protected function getProperty()
    {
        $properties = \App\Property::when(
            !$this->user->hasRole(['admin', 'property_manager']),
            function ($query) {
                $query->where(
                    function ($query) {
                        $query->whereIn(
                            'id',
                            function ($query) {
                                $query->select('property_id')
                                    ->from('user_properties')
                                    ->where('user_id', $this->user->id);
                                //->whereNull('deleted_at');
                            }
                        )
                        ->orWhereIn(
                            'id',
                            function ($query) {
                                $query->select('id')
                                    ->from('properties')
                                    ->where('user_id', $this->user->id);
                                //->whereNull('deleted_at');
                            }
                        );
                    }
                );
            }
        )
        ->when(
            $this->user->hasRole('admin'),
            function ($query) {
                $query->whereIn(
                    'id',
                    function ($query) {
                        $query->select('id')
                            ->from('properties')
                            ->where('subscriber_id', $this->user->subscriber_id);
                        //->whereNull('deleted_at');
                    }
                );
            }
        )
        ->when(
            $this->user->hasRole('property_manager'),
            function ($query) {
                $query->where(
                    function ($query) {
                        $query->whereIn(
                            'id',
                            function ($query) {
                                $query->select('property_id')
                                    ->from('user_properties')
                                    ->where('user_id', $this->user->id);
                                //->whereNull('deleted_at');
                            }
                        );
                    }
                );
            }
        )
        ->with(
            [
                'getUnit' => function ($query) {
                    $query->where('is_active', 1)
                        ->where('is_route', 0)
                        ->withTrashed();
                }
            ]
        )
        ->withTrashed()
        ->orderBy('name')
        ->get();

        return $properties;
    }

    protected function propertyDelivery()
    {
        $properties = \App\Property::when(
            !$this->user->hasRole(['admin', 'property_manager']),
            function ($query) {
                $query->where(
                    function ($query) {
                        $query->whereIn(
                            'id',
                            function ($query) {
                                $query->select('property_id')
                                    ->from('user_properties')
                                    ->where('user_id', $this->user->id);
                                //->whereNull('deleted_at');
                            }
                        )
                        ->orWhereIn(
                            'id',
                            function ($query) {
                                $query->select('id')
                                    ->from('properties')
                                    ->where('user_id', $this->user->id);
                                //->whereNull('deleted_at');
                            }
                        );
                    }
                );
            }
        )
        ->when(
            $this->user->hasRole('admin'),
            function ($query) {
                $query->whereIn(
                    'id',
                    function ($query) {
                        $query->select('id')
                            ->from('properties')
                            ->where('subscriber_id', $this->user->subscriber_id);
                        //->whereNull('deleted_at');
                    }
                );
            }
        )
        ->when(
            $this->user->hasRole('property_manager'),
            function ($query) {
                $query->where(
                    function ($query) {
                        $query->whereIn(
                            'id',
                            function ($query) {
                                $query->select('property_id')
                                    ->from('user_properties')
                                    ->where('user_id', $this->user->id);
                                //->whereNull('deleted_at');
                            }
                        );
                    }
                );
            }
        )
        ->with(
            [
                'getUnit' => function ($query) {
                    $query->where('is_active', 1)
                        ->where('is_route', 0)
                        ->withTrashed();
                }
            ]
        )
        ->whereHas(
            'getUnit',
            function ($query) {
                $query->where('is_active', 1)
                ->where('is_route', 0)
                    ->withTrashed();
            }
        )
        //->withTrashed()
        ->orderBy('name')
        ->get();

        return $properties;
    }

    public function recycleWasteCal($matchdate, $userIds, $proId)
    {
        $startDate = \Carbon\Carbon::parse($matchdate)->addHour(6);
        $endDate = \Carbon\Carbon::parse($matchdate)->addDay(1)->addHour(5)->addMinute(59)->addSecond(59);
        //dd($matchdate, $startDate, $endDate);
        $overallWaste = $overallRecyle = $wasteTarget = 0;

        $pkptueday = \App\Activitylogs::select('id', 'barcode_id', 'wast', 'recycle', 'type')
            ->where('type', '2')
            ->when(
                !empty($proId),
                function ($query) use ($proId) {
                    $query->whereRaw("barcode_id in (select `barcode_id` from `units` where `property_id` in (" . collect($proId)->implode(', ') . ") and `is_active` = 1 and `deleted_at` is null)");
                }
            )
                // ->whereIn(
                //     'barcode_id2',
                //     function ($query) use ($proId) {
                //         $query->select('barcode_id')
                //         ->from('units')
                //         ->whereIn('property_id', $proId)
                //         ->where('is_active', 1)
                //         ->whereNull('deleted_at');
                //     }
                // )
            ->with(
                [
                    'unit' => function ($query) {
                        $query->select('id', 'property_id', 'building_id', 'barcode_id', 'created_at', 'updated_at')
                            ->where('is_active', '1')
                            ->with(
                                [
                                    'service' => function ($query) {
                                        $query->select('id', 'waste_weight', 'recycle_weight', 'property_id', 'waste_reduction_target')
                                            ->withTrashed();
                                    }
                                ]
                            )
                            ->withTrashed();
                    }
                ]
            )
            ->whereBetween('updated_at', [$startDate, $endDate])
        ->get();

        if (!empty($pkptueday)) {
            foreach ($pkptueday as $val) { //dd($val->unit->service);
                $units = $val->unit;
                //dd($val->unit->property_id);
                // $units = \App\Units::select('property_id')->where('barcode_id', $val->barcode_id)->first();
                // if (empty($proId)) {
                //     $units = \App\Units::select('property_id')->where('barcode_id', $val->barcode_id)->first();
                // } else {
                //     $units = \App\Units::select('property_id')
                //             ->where('barcode_id', $val->barcode_id)
                //             ->where('property_id', $proId)
                //             ->first();
                // }

                if (!is_null($units)) {
                    $services = $val->unit->service;

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

    public function pickupWasteCal($matchdate, $userIds, $barcode)
    {
        $startDate = \Carbon\Carbon::parse($matchdate)->addHour(6);
        $endDate = \Carbon\Carbon::parse($matchdate)
                ->addDay(1)->addHour(5)->addMinute(59)->addSecond(59);

        $overallPickup = 0;
        $pickup = \App\Activitylogs::select('id')
            ->where('type', '2')
            ->when(
                !$this->user->hasRole('property_manager'),
                function ($query) use ($userIds) {
                    //$query->whereIn('user_id', $userIds);
                    $query->whereRaw("user_id in (" . implode(",", $userIds) . ")");
                }
            )
            ->whereNotNull('barcode_id')
            ->where('wast', '1')
            ->whereBetween(
                \DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                [$startDate, $endDate]
            )
            ->when(
                !empty($barcode),
                function ($query) use ($barcode) {
                    $query->whereIn('barcode_id', $barcode);
                }
            )
        ->get();

        $overallPickup = $pickup->count();
        $array = ['pickup' => $overallPickup];

        return $array;
    }

    public function recycleDeliveryWastCal($matchdate, $userIds, $barcode)
    {
        $startDate = \Carbon\Carbon::parse($matchdate)->addHour(6);
        $endDate = \Carbon\Carbon::parse($matchdate)->addDay(1)
                ->addHour(5)->addMinute(59)->addSecond(59);

        $overallRecycle = 0;
        $recycle = \App\Activitylogs::select('id')
            ->where('type', '2')
            ->when(
                !$this->user->hasRole('property_manager'),
                function ($query) use ($userIds) {
                    //$query->whereIn('user_id', $userIds);
                    $query->whereRaw("user_id in (" . implode(",", $userIds) . ")");
                }
            )
            ->where('barcode_id', '!=', null)
            ->where('wast', '1')
            ->where('recycle', '1')
            ->whereBetween(
                \DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                [$startDate, $endDate]
            )
            ->when(
                !empty($barcode),
                function ($query) use ($barcode) {
                    $query->whereIn('barcode_id', $barcode);
                }
            )->get();

        $overallRecycle = $recycle->count();
        $array = ['recycles' => $overallRecycle];

        return $array;
    }

    public function violationWasteCal($matchdate, $userIds, $barcode)
    {
        $startDate = \Carbon\Carbon::parse($matchdate)->addHour(6);
        $endDate = \Carbon\Carbon::parse($matchdate)
                ->addDay(1)->addHour(5)->addMinute(59)->addSecond(59);

        $overallViolation = 0;

        $violation = \App\Activitylogs::select('id')
            ->where('type', '3')
                ->when(
                    !$this->user->hasRole('property_manager'),
                    function ($query) use ($userIds) {
                        //$query->whereIn('user_id', $userIds);
                        $query->whereRaw("user_id in (" . implode(",", $userIds) . ")");
                    }
                )
                ->where('barcode_id', '!=', null)
                ->whereBetween(
                    \DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                    [$startDate, $endDate]
                )
                ->when(
                    !empty($barcode),
                    function ($query) use ($barcode) {
                        $query->whereIn('barcode_id', $barcode);
                    }
                )
                ->get();

        $overallViolation = $violation->count();
        $array = ['violation' => $overallViolation];

        return $array;
    }

    public function notesWasteCal($matchdate, $userIds, $barcode)
    {
        $startDate = \Carbon\Carbon::parse($matchdate)->addHour(6);
        $endDate = \Carbon\Carbon::parse($matchdate)
                ->addDay(1)->addHour(5)->addMinute(59)->addSecond(59);
        $overallNotes = 0;
        $notes = \App\Activitylogs::select('id')
                ->where('type', '6')
                ->when(
                    !$this->user->hasRole('property_manager'),
                    function ($query) use ($userIds) {
                        $query->whereIn('user_id', $userIds);
                    }
                )
                ->where('barcode_id', '!=', null)
                ->whereBetween(
                    \DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                    [$startDate, $endDate]
                )
                ->when(
                    !empty($barcode),
                    function ($query) use ($barcode) {
                        $query->whereIn('barcode_id', $barcode);
                    }
                )
                ->get();

        $overallNotes = $notes->count();
        $array = ['notes' => $overallNotes];

        return $array;
    }

    public function recycleWasteCalMonthly($firstDayThisMonth, $lastDayThisMonth, $userIds, $id)
    {
        $matchdate;
        $overallWaste = $overallRecyle = $overallAvrage = $totalwaste = $getwaste = 0;

        $pkptueday = \App\Activitylogs::select('id', 'type', 'wast', 'recycle')
                ->where('type', '2')
                ->whereIn('user_id', $userIds)
                ->where('barcode_id', '!=', null)
                ->whereBetween('updated_at', [$firstDayThisMonth, $lastDayThisMonth])
                ->with(
                    [
                        'unit' => function ($query) {
                            $query->select('id', 'property_id', 'building_id', 'barcode_id', 'created_at', 'updated_at')
                                ->where('is_active', '1')
                                ->with(
                                    [
                                        'service' => function ($query) {
                                            $query->select('id', 'waste_weight', 'recycle_weight', 'property_id', 'waste_reduction_target')
                                                ->withTrashed();
                                        }
                                    ]
                                )
                                ->withTrashed();
                        }
                    ]
                )
                ->get();

        $propertyunit = \App\Property::select('id', 'units')
                ->whereIn('id', $id)->first();

        if (!empty($propertyunit)) {
            $newservices = \App\Service::where('property_id', $id)->first();

            if (!empty($newservices)) {
                $totalwaste = $propertyunit->units * $newservices->waste_weight;
                $getwaste = ($totalwaste * $newservices->waste_reduction_target) / 100;
                $overallAvrage = round($totalwaste - $getwaste);

                if (!empty($pkptueday)) {
                    foreach ($pkptueday as $val) {
                        $units = $val->unit;
                                
                        if (!empty($units)) {
                            $services = $val->unit->service;

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

    /*
    *
    * Calcutate the total pickup and total active unit (Task: #1061).
    *
    */
    public function calPickup(Request $request)
    {
        $arr = [];
        $startTime = \Carbon\Carbon::parse($request->startTime, getUserTimezone())
        ->addHours(6);
        $endTime = \Carbon\Carbon::parse($request->endTime, getUserTimezone())
        ->addDays(1)->addHours(5)->addMinutes(59)->addSeconds(59);
        $pro = $this->getProperty();

        $properties = \App\Property::select('id', 'type', 'name')
            ->when(
                !empty($request->id),
                function ($query) use ($request) {
                    $query->where('id', $request->id);
                },
                function ($query) use ($pro) {
                    $query->whereIn('id', $pro->pluck('id'));
                }
            )
            ->withCount(
                [
                    'getUnit' => function ($query) {
                        $query->where(
                            function ($query) {
                                $query->where('is_route', 0)
                                    ->where('is_active', 1);
                            }
                        )
                        ->orWhere('is_route', 1);
                    },
                ]
            )
            ->with(
                [
                    'service' => function ($query) {
                        $query->select('property_id', 'pickup_type')
                            ->withTrashed();
                    },
                ]
            )
            ->whereHas(
                'getUnit',
                function ($query) {
                    $query->where(
                        function ($query) {
                            $query->where('is_route', 0)
                                ->where('is_active', 1);
                        }
                    )
                    ->orWhere('is_route', 1)
                    ->withTrashed();
                }
            )
        ->withTrashed()
        ->get();

        foreach ($properties as $property) {
            $count = $property->getActivity()
                ->select('id')
                ->whereBetween(
                    \DB::raw("convert_tz(updated_at, 'UTC','" . getUserTimezone() . "')"),
                    [
                        $startTime,
                        $endTime,
                    ]
                )
                ->where('type', 2)
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
                ->groupBy('barcode_id')
                ->withTrashed()
            ->get();

            $arr[] = $count->count();
            $calUnit[] = $property->get_unit_count;
        }

        return response()
            ->json(
                [
                    'totalBin' => array_sum($calUnit),
                    'totalPickup' => array_sum($arr),
                ],
                200
            );
    }

    public function manageTask()
    {
        $properties = $this->propertyDelivery();
        $this->data['properties'] = $properties;

        $users = \App\User::select('id', \DB::raw("CONCAT_WS(' ', `title`, `firstname`, `lastname`) as name"))
            ->when(
                !$this->user->hasRole('admin'),
                function ($query) {
                    $query->where('user_id', $this->user->id);
                }
            )
            ->when(
                $this->user->hasRole('admin'),
                function ($query) {
                    $query->where('subscriber_id', $this->user->subscriber_id);
                }
            )
            ->withTrashed()
        ->get();

        $this->data['scanBy'] = $users;

        return view('report.taskreport', $this->data);
    }

    public function taskData(Request $request)
    {
        $logsArray = [];
        $id = $request->id;
        $i = $request->start;
        $scanBy = $request->scanBy;
        $fre = $request->fre;
        $media = $request->media;
        $search = $request->search['value'];
        $startTime = \Carbon\Carbon::parse($request->startTime, getUserTimezone())->addHours(6)->copy();
        $endTime = \Carbon\Carbon::parse($request->endTime, getUserTimezone())
        ->addDays(1)->addHours(5)->addMinutes(59)->addSeconds(59)->copy();
        
        //Get total result:Start
        $taskCount = \App\Activitylogs::where('type', 13)
            ->whereIn(
                'user_id',
                function ($query) {
                    $query->select('id')
                        ->from('users')
                        ->where('subscriber_id', $this->user->subscriber_id)
                        ->whereNull('deleted_at');
                }
            )
            ->when(
                !empty($id),
                function ($query) use ($id) {
                    $query->where('property_id', $id);
                }
            )
            ->when(
                !empty($scanBy),
                function ($query) use ($scanBy) {
                    $query->where('user_id', $scanBy);
                }
            )
            ->when(
                !empty($fre),
                function ($query) use ($fre) {
                    $query->where('task_frequency', $fre);
                }
            )
            ->when(
                !empty($media),
                function ($query) use ($media) {
                    $query->whereRaw("id in (select `activity_id` from task_images where `media_type` = '$media')");
                }
            )
            ->when(
                !empty($fre),
                function ($query) use ($fre) {
                    $query->where(
                        function ($query) use ($fre) {
                            $query->whereRaw("barcode_id in (select `barcode_id` from `tasks` where `frequency` = $fre)");
                        }
                    );
                }
            )
            ->when(
                !empty($search),
                function ($query) use ($search) {
                    $query->where(
                        function ($query) use ($search) {
                            $query->whereRaw("barcode_id in (select `barcode_id` from `tasks` where `name` LIKE '%$search%')");
                        }
                    );
                }
            )
            ->whereBetween(
                \DB::raw("convert_tz(updated_at, 'UTC','" . getUserTimezone() . "')"),
                [
                    $startTime,
                    $endTime,
                ]
            )
        ->get();
        //Get total result:End

        $tasks = \App\Activitylogs::where('type', 13)
            ->whereIn(
                'user_id',
                function ($query) {
                    $query->select('id')
                        ->from('users')
                        ->where('subscriber_id', $this->user->subscriber_id)
                        ->whereNull('deleted_at');
                }
            )
            ->when(
                !empty($id),
                function ($query) use ($id) {
                    $query->where('property_id', $id);
                }
            )
            ->when(
                !empty($scanBy),
                function ($query) use ($scanBy) {
                    $query->where('user_id', $scanBy);
                }
            )
            ->when(
                !empty($fre),
                function ($query) use ($fre) {
                    $query->where('task_frequency', $fre);
                }
            )
            ->when(
                !empty($media),
                function ($query) use ($media) {
                    $query->whereRaw("id in (select `activity_id` from task_images where `media_type` = '$media')");
                }
            )
            ->when(
                !empty($fre),
                function ($query) use ($fre) {
                    $query->where(
                        function ($query) use ($fre) {
                            $query->whereRaw("barcode_id in (select `barcode_id` from `tasks` where `frequency` = $fre)");
                        }
                    );
                }
            )
            ->when(
                !empty($search),
                function ($query) use ($search) {
                    $query->where(
                        function ($query) use ($search) {
                            $query->whereRaw("barcode_id in (select `barcode_id` from `tasks` where `name` LIKE '%$search%')");
                        }
                    );
                }
            )
            ->whereBetween(
                \DB::raw("convert_tz(updated_at, 'UTC','" . getUserTimezone() . "')"),
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
                    'getProperty' => function ($query) {
                        $query->select('id', 'units', 'name', 'type')
                            ->withTrashed();
                    },
                    'taskMedia' => function ($query) {
                        $query->select('id', 'files_name', 'activity_id', 'media_type');
                    }
                ]
            )
        ->get();
        
        foreach ($tasks as $task) {
            $userInfoByUserId = $task->getUserDetail;
            $property = $task->getProperty;
            
            if (isset($property)) {
                $propertyName = ucwords($property->name);
            }
            
            $frequency = \App\Tasks::where('barcode_id', $task->barcode_id)
                ->withTrashed()->first();
            
            if ($task->task_frequency == 1) {
                $fre = 'Daliy';
            }
            
            if ($task->task_frequency == 2) {
                $fre = 'Weekly';
            }
            
            if ($task->task_frequency == 3) {
                $fre = 'Monthly';
            }

            if ($task->taskMedia) {
                $media = $task->taskMedia->files_name;
                $title = ucwords($task->taskMedia->media_type);
                //$url = url("uploads/task/$media");
                //$url = Storage::disk('s3')->url("uploads/task/$media", 'public');
                $url = url("/uploads/task/$media");

                if ($title == 'Video') {
                    $icon = 'fa fa-video-camera';
                } elseif ($title == 'Image') {
                    $icon = 'fa fa-picture-o';
                } elseif ($title == 'Audio') {
                    $icon = 'fa fa-volume-up';
                } else {
                    $icon = '';
                }

                $action = "<a href='$url' target='_blank'><li class='$icon' title='$title'></li></a>";
            } else {
                $action = "<a href='javascript:void(0);' style='opacity: 0.5;pointer-events: none;'><li class='fa fa-picture-o' title='No Media File Found'></li></a>";
            }

            $logsArray[] = [
                'sNo' => ++$i,
                'task' => ucwords($frequency->name),
                'property_name' => $propertyName,
                'updated_at' => $task->updated_at->timezone(getUserTimezone())->format('m-d-Y h:i A'),
                'status' => $fre,
                'action' => $action,
                'employee_name' => ucwords($userInfoByUserId->title
                    . ' ' . $userInfoByUserId->firstname
                    . ' ' . $userInfoByUserId->lastname),
            ];
        }

        return json_encode(
            [
                'draw' => intval($request->draw),
                'recordsTotal' => !empty($taskCount) ? $taskCount->count() : 0,
                'recordsFiltered' => !empty($taskCount) ? $taskCount->count() : 0,
                'data' => $logsArray,
            ]
        );
    }

    public function historicalCheckInOut()
    {
        $employies = \App\User::query()
            ->select('id', \DB::raw("CONCAT_WS(' ', `title`, `firstname`, `lastname`) as name"))
            ->where('subscriber_id', $this->user->subscriber_id)
            ->where('role_id', '!=', \App\User::PROPERTYMANAGER)
            ->orderBy(\DB::raw("CONCAT_WS(' ', `title`, `firstname`, `lastname`)"), 'asc')
            //->withTrashed()
            ->get();
        
        $this->data['employies'] = $employies;

        $properties = $this->propertyList()
            ->orderBy('name', 'asc')
            //->withTrashed()
            ->get();
        
        $this->data['properties'] = $properties;

        return view('report.historicalCheckinOutLog', $this->data);
    }

    /**
    *
    * Task : #1623: Capture Historical Check in/Out Log by property
    * Function Name : getHistoricalReport
    *
    **/

    public function getHistoricalReport(Request $request)
    {
        $o = true;
        $timeArray = $logsArray = [];
        $property = $request->property;
        $name = $request->name;
        $properties = $this->propertyList()
            ->when(
                !empty($property),
                function ($query) use ($property) {
                    $query->where('id', $property);
                }
            )
        ->get();

        $startTime = \Carbon\Carbon::parse($request->startTime, getUserTimezone())
            ->addHours(6)->copy();
        $endTime = \Carbon\Carbon::parse($request->endTime, getUserTimezone())
            ->addDays(1)->addHours(5)->addMinutes(59)->addSeconds(59)->copy();
        
        while ($startTime->lt($endTime)) {
            $eName = $pName = '';
            $violation = $checkpoints = $taskcompleted = 0;
            $serviceduration = $checIn = $checOut = '-- / -- / --';
            $i = $request->start + 1;
                
            $end = $o ? $startTime->copy()->addDays(1)->subSeconds(1) : $startTime->copy()->addDays(1);

            $details = \App\Activitylogs::query()
                ->select('id', 'user_id', 'text', 'barcode_id', 'property_id', 'building_id', 'type', 'task_frequency', 'created_at', 'updated_at')
                ->whereIn('property_id', $properties->pluck('id'))
                ->when(
                    !empty($name),
                    function ($query) use ($name) {
                        $query->where('user_id', $name);
                    }
                )
                ->whereIn(
                    'type',
                    [
                        \App\Activitylogs::VIOLATION,
                        \App\Activitylogs::CHECKPOINT,
                        \App\Activitylogs::TASKCOMPLETE,
                        \App\Activitylogs::CHECKINOUT,
                    ]
                )
                ->whereBetween(
                    \DB::raw("convert_tz(created_at, 'UTC','" . getUserTimezone() . "')"),
                    [
                        $startTime,
                        $end,
                    ]
                )
                ->with(
                    [
                        'getUserDetail' => function ($query) {
                            $query->select('id', \DB::raw("CONCAT_WS(' ', `title`, `firstname`, `lastname`) as name"), 'subscriber_id', 'subscriber_id')
                                ->withTrashed();
                        },
                        'getProperty' => function ($query) {
                            $query->select('id', 'name', 'type')
                                ->withTrashed();
                        }
                    ]
                )
                ->latest()
                ->get();
             
            $activities = $details->groupBy('property_id');
            
            foreach ($activities as $activity) {
                $violation = $checkpoints = $taskcompleted = 0;
                $serviceduration = $checIn = $checOut = '-- / -- / --';
                foreach ($activity as $detail) {
                    if (isset($detail->getUserDetail->name)) {
                        $eName = $detail->getUserDetail->name;
                        $eName .= '<br/><b>Created At: </b>' . $detail->created_at
                            ->timezone(getUserTimezone())->format('m-d-Y h:i A');
                        $eName .= '<br/><b>Updated At: </b>' . $detail->updated_at
                            ->timezone(getUserTimezone())->format('m-d-Y h:i A');
                    }

                    if (isset($detail->getProperty->name)) {
                        $pName = $detail->getProperty->name;
                    }

                    if ($detail->type == \App\Activitylogs::VIOLATION) {
                        ++$violation;
                    }

                    if ($detail->type == \App\Activitylogs::CHECKPOINT) {
                        ++$checkpoints;
                    }

                    if ($detail->type == \App\Activitylogs::TASKCOMPLETE) {
                        ++$taskcompleted;
                    }

                    if ($detail->type == \App\Activitylogs::CHECKINOUT) {
                        $serviceduration = $checIn = $checOut = '-- / -- / --';
                        $checkinout = \App\PropertiesCheckIn::query()
                            ->where('user_id', $detail->getUserDetail->id)
                            ->where('property_id', $detail->getProperty->id)
                            ->whereBetween(
                                \DB::raw("convert_tz(updated_at, 'UTC','" . getUserTimezone() . "')"),
                                [
                                    $startTime,
                                    $end,
                                ]
                            )
                            ->latest()
                        ->first();
                                    
                        if (!is_null($checkinout)) {
                            $checIn = $checkinout->created_at
                                ->timezone(getUserTimezone())->format('m-d-Y h:i A');

                            if ($checkinout->check_in_complete == 1) {
                                $checOut = $checkinout->updated_at
                                    ->timezone(getUserTimezone())->format('m-d-Y h:i A');
                            }
                        
                            //Calculate the service duration : Start
                            if (!is_null($checkinout) && !empty($checkinout->check_in_complete)) {
                                $serviceduration = $checkinout->created_at
                                    ->timezone(getUserTimezone())
                                    ->diffInHours($checkinout->updated_at
                                    ->timezone(getUserTimezone()))
                                    . ':' .
                                    $checkinout->created_at
                                    ->timezone(getUserTimezone())
                                    ->diff($checkinout->updated_at
                                    ->timezone(getUserTimezone()))
                                    ->format('%I:%S');
                            }
                            //Calculate the service duration : End
                        }
                    }
                }
                
                $logsArray[] = [
                    //'sNo' => $i++,
                    'name' => ucwords($eName),
                    'propertyName' => ucwords($pName),
                    'checkin' => $checIn,
                    'checkout' => $checOut,
                    'serviceduration' => $serviceduration,
                    'violation' => $violation,
                    'checkpoints' => $checkpoints,
                    'taskcompleted' => $taskcompleted,
                    'created_at' => $detail->created_at->timezone(getUserTimezone())
                ];
            }
            
            $startTime = $end;
            $o = false;
        }
        
        if (!empty($logsArray)) {
            foreach ($logsArray as $key => $row)
            {
                $createdAt[$key] = $row['created_at'];
            }

            array_multisort($createdAt, SORT_DESC, $logsArray);
        }
        
        return json_encode(
            [
                'draw' => intval($request->draw),
                'recordsTotal' => count($logsArray) ? count($logsArray) : 0,
                'recordsFiltered' => count($logsArray) ? count($logsArray) : 0,
                'data' => count($logsArray) ? array_slice($logsArray, $request->start, $request->length) : 0,
            ]
        );
    }

    public function routeCheckpoints()
    {
        $this->data['properties'] = $this->propertyList()->get();
        return view('report.routecheckpoint', $this->data);
    }

    public function routeCheckPoint(Request $request)
    {
        $routeArray = [];
        $i = $request->start + 1;
        $searchText = $request->search['value'];
        $prop = $request->propertyId;

        // Get total result:Start (Todo: merge the both queries)
        
        $properties = $this->propertyList()
            ->when(
                !empty($prop),
                function ($query) use ($prop) {
                    $query->where(
                        function ($query) use ($prop) {
                            $query->where('id', $prop);
                        }
                    );
                }
            );

        $this->data['properties'] = $properties;

        $routesPoint = \App\Units::query()
            ->whereIn('property_id', $properties->pluck('id'))
            ->where('is_route', 1)
            ->get();
        //Get total result:End

        //Get result with limit:Start
        $routes = \App\Units::query()
            ->whereIn('property_id', $properties->pluck('id'))
            ->where('is_route', 1)
            ->with(
                [
                    'getPropertyDetail' => function ($query) {
                        $query->select('id', 'type', 'name');
                    },
                    'getBuildingDetail' => function ($query) {
                        $query->select('id', 'building_name', 'address')
                            ->withTrashed();
                    },
                    'isRouteComplete' => function ($query) {
                        $query->select('id', 'barcode_id', 'created_at', 'updated_at')
                            ->where('type', 11)
                            ->whereBetween(
                                \DB::raw("convert_tz(created_at,'UTC','" . getUserTimezone() . "')"),
                                [
                                    getStartEndTime()->startTime,
                                    getStartEndTime()->endTime,
                                ]
                            );
                    }
                ]
            )
        ->limit($request->length)->offset($request->start)
        ->latest()
        ->get();
        //Get result with limit: End

        foreach ($routes as $route) {
            $scan = '-- / -- / --';
            $property = "<b>Name :</b>" . ucwords($route->unit_number);
            $property .= '<br/><b>Barcode Id :</b> ' . ucwords($route->barcode_id);
            $property .= '<br/><b>Property :</b> ' . ucwords($route->getPropertyDetail->name);
            
            if ($route->isRouteComplete->isNotEmpty()) {
                $scan = $route->isRouteComplete[0]->created_at->timezone(getUserTimezone())->format('m-d-Y h:i A');
            }

            if ($route->getPropertyDetail->type == 1) {
                $property .= '<br/><b>Streets :</b> ' . ucwords($route->getBuildingDetail->building_name);
                
                $property .= '<br/><b>Streets Address :</b> ' . ucwords($route->getBuildingDetail->address);
            } elseif ($route->getPropertyDetail->type == 2) {
                $property .= '<br/><b>Buildings :</b> ' . ucwords($route->getBuildingDetail->building_name);
                
                $property .= '<br/><b>Buildings Address :</b> ' . ucwords($route->getBuildingDetail->address);
            } elseif ($route->getPropertyDetail->type == 3) {
                $property .= '<br/><b>Floors :</b >' . ucwords($route->getBuildingDetail->building_name);
                
                $property .= '<br/><b>Floors Address :</b> ' . ucwords($route->getBuildingDetail->address);
            } elseif (isset($route->getBuildingDetail->building_name)) {
                $property .= '<br/><b>Streets :</b> ' . ucwords($route->getBuildingDetail->building_name);
                
                $property .= '<br/><b>Streets Address :</b> ' . ucwords($route->getBuildingDetail->address);
            }

            $barcode = \QrCode::size(120)->generate($route->barcode_id);

            $routeArray[] = [
                'id' => $i++,
                'barcode' => $barcode,
                'property' => $property,
                'description' => $scan
            ];
        }

        return json_encode(
            [
                'draw' => intval($request->draw),
                'recordsTotal' => !empty($routesPoint) ? $routesPoint->count() : 0,
                'recordsFiltered' => !empty($routesPoint) ? $routesPoint->count() : 0,
                'data' => $routeArray,
            ]
        );
    }

    public function routeCheckpointExcel(Request $request)
    {
        $date = getStartEndTime()->startTime;
        $property = $request->property;
        $properties = $this->propertyList()
            ->when(
                !empty($property),
                function ($query) use ($property) {
                    $query->where('id', $property);
                }
            )
        ->get();

        $routes = \App\Units::query()
            ->whereIn('property_id', $properties->pluck('id'))
            ->where('is_route', 1)
            ->with(
                [
                    'getPropertyDetail' => function ($query) {
                        $query->select('id', 'type', 'name');
                    },
                    'getBuildingDetail' => function ($query) {
                        $query->select('id', 'building_name', 'address')
                            ->withTrashed();
                    },
                    'isRouteComplete' => function ($query) {
                        $query->select('id', 'barcode_id', 'created_at', 'updated_at')
                            ->where('type', 11)
                            ->whereBetween(
                                \DB::raw("convert_tz(created_at,'UTC','" . getUserTimezone() . "')"),
                                [
                                    getStartEndTime()->startTime,
                                    getStartEndTime()->endTime,
                                ]
                            );
                    }
                ]
            )
        ->latest()
        ->get();

        $i = 1;
        $routeArray[] = ['S.No', 'Route Checkpoint', 'Barcode', 'Property', 'Building', 'Address', 'Checkpoints Scanned'];

        foreach ($routes as $route) {
            $scan = '-- / -- / --';
            
            if ($route->isRouteComplete->isNotEmpty()) {
                $scan = $route->isRouteComplete[0]->created_at->timezone(getUserTimezone())->format('m-d-Y h:i A');
            }

            $routeArray[] = [
                'id' => $i++,
                'route' => ucwords($route->unit_number),
                'barcode' => ucwords($route->barcode_id),
                'property' => ucwords($route->getPropertyDetail->name),
                'build' => isset($route->getBuildingDetail->building_name) ? ucwords($route->getBuildingDetail->building_name) : '',
                'address' => isset($route->getBuildingDetail->address) ? ucwords($route->getBuildingDetail->address) : '',
                'description' => $scan
            ];
        }

        \Excel::create(
            'Trash Scan Routecheck Points',
            function ($excel) use ($routeArray, $date) {
                $excel->setTitle('Trash Scan Routecheck Points - [' . $date . ']');
                $excel->setDescription('Trash Scan Routecheck Points');
                $excel->sheet(
                    'sheet1',
                    function ($sheet) use ($routeArray) {
                        $sheet->fromArray($routeArray, null, 'A1', false, false);
                    }
                );
            }
        )
        ->download('xls');
    }
}
