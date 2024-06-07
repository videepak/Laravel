<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Storage;

class PropertyManagerController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        //$this->middleware('RoleAndPermission:violation');
    }

    public function index(Request $request, $propertyId = '')
    {
        //Permission: only admin or property manager can access this panel:Start
        if (!$this->user->hasRole(['admin', 'property_manager'])) {
            return redirect('unauthorized');
        }
        //Permission: only admin or property manager can access this panel:End

        // $property = \App\Property::when(
        //     !empty($propertyId),
        //     function ($query) use ($propertyId) {
        //             $query->where('id', $propertyId);
        //     }
        // )
        // ->when(
        //     empty($propertyId),
        //     function ($query) {
        //         $query->where(
        //             function ($query) {
        //                 $query->whereIn(
        //                     'id',
        //                     function ($query) {
        //                     $query->select('property_id')
        //                             ->from('user_properties')
        //                             ->where('user_id', $this->user->id)
        //                             ->whereNull('deleted_at');
        //                     }
        //                 );
        //             }
        //         )
        //         ->orWhere('user_id', $this->user->id)
        //         ->when(
        //             $this->user->role_id == 1,
        //             function ($query) {
        //                 $query->orWhere('subscriber_id', $this->user->subscriber_id);
        //             }
        //         );
        //     }
        // )
        // ->withCount(
        //     [
        //         'getViolationByProperties' => function ($query) {
        //                 $query->when(
        //                     $this->user->role_id == getPropertyManageId(),
        //                     function ($query) {
        //                         $query->whereIn('status', [2, 3, 4, 5, 6]);
        //                     }
        //                 );
        //         }
        //     ]
        // )
        // ->latest();

        // Mantain two diffrent status first one for admin and
        //second one for property manager (Task: 640 comment: #17)): Start
        $col = $this->user->hasRole('property_manager')
            ? 'manager_status' : 'status';
        $statusVal = $this->user->hasRole('property_manager') ? [6, 11] : [6];
        // Mantain two diffrent status first one for admin and
        //second one for property manager (Task: 640 comment: #17)): End

        $properties = $this->propertyList()
            ->when(
                !empty($propertyId),
                function ($query) use ($propertyId) {
                    $query->where('id', $propertyId);
                }
            )
            ->with(
                [
                    'getUnit'  => function ($query) {
                        $query->where('is_route', 0);
                    },
                ]
            )
        ->get();

        $arr = [];

        foreach ($properties as $property) {
            $pro = $property->getUnit;

            $vio = \App\Violation::whereIn('barcode_id', $pro->pluck('barcode_id'))
                ->whereNotIn($col, $statusVal)
                ->get()->count();

            $arr[] = [
                'id' => $property->id,
                'name' => $property->name,
                'get_violation_by_properties_count' => $vio,
            ];
        }

        //$a = (object) $arr;

        $property = collect($arr)
            ->sortBy('get_violation_by_properties_count', SORT_REGULAR, true);

        // $property = $property->get()
        //     ->sortBy('get_violation_by_properties_count', SORT_REGULAR, true);

        $this->data['propertyId'] = $propertyId;
        $this->data['properties'] = $property;

        return view('manager.propertylist', $this->data);
    }

    public function getViolation(Request $request)
    {
        // Mantain two diffrent status first one for admin and
        //second one for property manager (Task: 640 comment: #17)): Start
        $col = $this->user->hasRole('property_manager')
            ? 'manager_status' : 'status';
        $statusVal = $this->user->hasRole('property_manager') ? [6, 11] : [6];
        // Mantain two diffrent status first one for admin and
        //second one for property manager (Task: 640 comment: #17)): End
        $content = '';
        $i = 1;
        $top = $request->top;

        $getUnit = \App\Units::where(
            [
                'property_id' => $request->id,
                'is_active' => 1,
                'is_route' => 0
            ]
        )
        ->withCount(
            [
                'getViolationByBarcode' => function ($query) use ($col, $statusVal) {
                    $query->whereNotIn($col, $statusVal);
                },
            ]
        )->get();

        $units = $getUnit->sortBy('get_violation_by_barcode_count', SORT_REGULAR, true)
            ->take($top);

        if ($units->isNotEmpty()) {
            $content = '<table class="table table-striped"><thead>'
                    . '<tr><th>S.no</th><th>Bin Tag ID</th>';
            $content .= '<th>Total Violation</th></tr></thead><tbody>';

            foreach ($units as $unit) {
                $content .= '<tr><th scope="row">' . $i . '</th>';
                $content .= '<td>' . $unit->unit_number . '</td>';
                $content .= '<td>' . $unit->get_violation_by_barcode_count . '</td></tr>';
                ++$i;
            }
            $content .= '</tbody></table>';
        } else {
            $content = '<div class="col-md-12 col-sm-12 col-xs-12" '
                    . 'style="padding: 40px 15px;text-align: center;'
                    . 'font-weight: bolder;">No Record Found.</div>';
        }

        echo $content;
    }

    public function unitHistory($id)
    {
        //Permission: only Property Manager can access:Start
        if (!$this->user->hasRole(['property_manager'])) {
            return redirect('unauthorized');
        }
        //Permission: only Property Manager can access:End

        $getResidentid = \App\Resident::select('id')->where('unit_id', $id)->first();
                        
        $unit = \App\Units::where('id', $id)
            ->with(
                [
                    'getViolationByBarcode' => function ($query) {
                        $query->whereNotIn('status', [0])
                           ->latest();
                    },
                    'getResident' => function ($query) {
                        $query->select('id', 'unit_id')
                            ->with(
                                [
                                    'getViolation' => function ($query) {
                                        $query->select('id', 'residents_id', 'unit_id', 'violation_id');
                                    },
                                ]
                            );
                    },
                    'getPropertyDetail',
                    'getBuildingDetail',
                    'getActivityByBarcode',
                ]
            )
        ->get();
        
        #1693: Property Manager Dashboard Not in Sync with Porter Activity : Start
        $getResident = $unit[0]->getResident;
        $currentTab = $previousTab = [];
        
        if (!is_null($getResident) && $getResident->getViolation->isNotEmpty()) {
            $currentTab = \App\Violation::query()
                ->select('id', 'violation_reason', 'violation_action', 'image_name', 'user_id')
                ->whereIn('id', $getResident->getViolation->pluck('violation_id'))
                ->whereNotIn('status', [0])
                ->latest()
                ->get();

        }

        $previousTab = \App\Violation::query()
            ->select('id', 'violation_reason', 'violation_action', 'image_name', 'user_id')
            ->whereNotIn('status', [0])
            ->when(
                !is_null($getResident) && $getResident->getViolation->isNotEmpty(),
                function ($query) use ($getResident) {
                    $query->whereNotIn('id', $getResident->getViolation->pluck('violation_id'));
                }
            )
            ->where('barcode_id', $unit[0]->barcode_id)
            ->latest()
            ->get();
        #1693: Property Manager Dashboard Not in Sync with Porter Activity : End

        $recycleTotal = $recycleWeight = 0;
        $log = null;

        if (!empty($unit)) {
            $log = \App\Activitylogs::query()
                ->where('barcode_id', $unit[0]->barcode_id)
                ->where('type', 2)
                ->where('recycle', 1)
                ->get();
        }
        if (!empty($log)) {
            $recycleWeight = \App\Service::where(
                'property_id',
                $unit[0]->property_id
            )
            ->where(
                function ($query) {
                    $query->where('pickup_type', 2)
                    ->orWhere('pickup_type', 3);
                }
            )->first();
        }

        if (isset($recycleWeight->recycle_weight)
                && !empty($recycleWeight->recycle_weight)) {
            $recycleTotal = $recycleWeight->recycle_weight * $log->count();
        }

        $residentDetails = \App\Resident::query()
            ->select(\DB::raw("CONCAT_WS(' ', `firstname`, `lastname`) as name"), 'mobile', 'email')
            ->where('unit_id', $id)
            ->first();
        
        $this->data['residentDetails'] = $residentDetails;
        $this->data['recycle_weight'] = $recycleTotal;
        $this->data['unit'] = $unit;
        $this->data['getCurrentViolation'] = $currentTab;
        $this->data['getPreviousViolation'] = $previousTab;
        
        return view('manager.unithistory', $this->data);
    }

    public function searchUnit(Request $request)
    {
        $val = $request['query'];

        $response = \App\Units::select('unit_number as value', 'id as data', 'property_id', 'building_id')
                ->where('unit_number', 'LIKE', "%$val%")
                ->whereIn(
                    'property_id',
                    function ($query) {
                        $query->select('property_id')
                        ->from('user_properties')
                        ->where('user_id', $this->user->id)
                        ->whereNull('deleted_at');
                    }
                )
                ->orWhereIn(
                    'property_id',
                    function ($query) {
                        $query->select('id')
                        ->from('properties')
                        ->where('user_id', $this->user->id)
                        ->whereNull('deleted_at');
                    }
                )
                ->where('is_active', 1)
                ->with('getPropertyDetail', 'getBuildingDetail')
                ->get();

        $a = $response->map(
            function ($item, $key) {
                $concat = $item->value . ' (Property Name: ' . $item->getPropertyDetail->name . ')';

                return $a[] = [
                    'value' => $concat,
                    'data' => $item->data,
                ];
            }
        );

        $obj = new \stdClass();
        $obj->suggestions = $a;

        return response()->json($obj, 200);
    }

    public function getUnitsServiced(Request $request)
    {
        //1047: Property Manger Portal - Drill down on units serviced Comment #3.
        $i = $request->start + 1;
        $reportArray = [];

        $start = \Carbon\Carbon::parse($this->usertime->startTime)->subDays(1)->addHours(10)->copy()->toDateTimeString();

        $end = \Carbon\Carbon::parse($this->usertime->endTime)->subDays(1)->addHours(10)->copy()->toDateTimeString();

        $properties = $this->propertyList()
            ->whereHas(
                'service',
                function ($query) use ($start, $end) {
                    $query->where('pickup_start', '<=', $this->usertime->startTime)
                        ->where('pickup_finish', '>=', $this->usertime->endTime);
                }
            )
        ->get();

        $propertyUnits = \App\Units::select('id', 'property_id', 'unit_number', 'building_id', 'building', 'barcode_id', 'is_active')
            ->whereIn('property_id', $properties->pluck('id'))
            ->where('is_active', 1)
            ->where('is_route', 0)
            ->latest()
            ->with(
                [
                    'getPropertyDetail',
                ]
            )
        ->paginate(50);

        $propertyUnitsMapped = $propertyUnits
            ->mapToGroups(
                function ($property_unit, $key) use ($propertyUnits, $start, $end) {
                    $property = $property_unit->getPropertyDetail;
                    $service = $property->service;

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
                        ->where('barcode_id', $property_unit->barcode_id)
                        ->whereBetween(
                            \DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                            [
                                $start,
                                $end,
                            ]
                        )
                        ->with(
                            [
                                'getUserDetail' => function ($query) {
                                    $query->select('id', \DB::raw("CONCAT_WS(' ', `title`, `firstname`, `lastname`) as name"));
                                },
                            ]
                        )
                    ->first();

                    if (!empty($property->id) && !is_null($checkBarcode)) {
                        $property_unit->username = $checkBarcode->getUserDetail->name;
                        $property_unit->created = $checkBarcode->created_at;

                        return [$property->name => $property_unit];
                    } else {
                        return [];
                    }
                }
            );

        foreach ($propertyUnitsMapped as $qrCollection) {
            foreach ($qrCollection as $qrcodeDetails) {
                if (!empty($qrcodeDetails->barcode_id)) {
                    $qrcode = \QrCode::size(120)->generate($qrcodeDetails->barcode_id);
                } else {
                    $qrcode = '-';
                }

                if (!empty($qrcodeDetails->unit_number)) {
                    $unitof = $qrcodeDetails->unit_number;
                } elseif (!empty($qrcodeDetails->barcode_id)) {
                    $unitof = $qrcodeDetails->barcode_id;
                } else {
                    $unitof = '-';
                }

                if (!empty($qrcodeDetails->getPropertyDetail->name)) {
                    $property = '<b>Property Name: </b>' . ucwords($qrcodeDetails->getPropertyDetail->name);
                } else {
                    $property = '';
                }

                if (!empty($qrcodeDetails->getPropertyDetail->name)) {
                    $property .= '<br/><b>Property Address: </b>' . ucwords($qrcodeDetails->getPropertyDetail->address . ', ' . $qrcodeDetails->getPropertyDetail->city . ', ' . $qrcodeDetails->getPropertyDetail->getState->name . ', ' . $qrcodeDetails->getPropertyDetail->zip);
                }

                if (!empty($qrcodeDetails->getPropertyDetail->type) && ($qrcodeDetails->getPropertyDetail->type == 1 ||
                $qrcodeDetails->getPropertyDetail->type == 4)) {
                    $property .= '<br/><b>Unit Address:</b>' .
                    ucwords($qrcodeDetails->address1);
                } elseif (!empty($qrcodeDetails->getPropertyDetail->type) && ($qrcodeDetails->getPropertyDetail->type == 3 || $qrcodeDetails->getPropertyDetail->type == 2)) {
                    if (isset($qrcodeDetails->getBuildingDetail->building_name)) {
                        $property .= '<br/><b>Building Name:</b> ' . ucwords($qrcodeDetails->getBuildingDetail->building_name);
                    }
                }

                if (isset($qrcodeDetails->getBuildingDetail->address)) {
                    $property .= '<br/>Building Address:</b> ' . ucwords($qrcodeDetails->getBuildingDetail->address);
                }

                $property .= '<br/><b>Unit:</b> ';

                if (empty($qrcodeDetails->unit_number)) {
                    $property .= 'Not Mention.';
                } else {
                    $property .= ucwords($qrcodeDetails->unit_number);
                }

                $property .= '<br/><b>Property Type: </b>';

                if (!empty($qrcodeDetails->getPropertyDetail->type)) {
                    if ($qrcodeDetails->getPropertyDetail->type == 1) {
                        $property .= 'Single Family Home';
                    } elseif ($qrcodeDetails->getPropertyDetail->type == 2) {
                        $property .= 'Garden Style Apartment';
                    } elseif ($qrcodeDetails->getPropertyDetail->type == 3) {
                        $property .= 'High Rise Apartment';
                    } elseif ($qrcodeDetails->getPropertyDetail->type == 4) {
                        $property .= 'Townhome';
                    }
                }

                if (!empty($qrcodeDetails->username)) {
                    $detail = '<b>Username: </b>' . $qrcodeDetails->username;
                    $detail .= '<br/><b>Pickup Date:</b> ' . \Carbon\Carbon::parse($qrcodeDetails->created)->timezone(getUserTimezone())
                    ->format('m-d-Y h:i A');
                }

                if (!empty($qrcodeDetails->username)) {
                    $reportArray[] = [
                        'user_id' => $i++,
                        'qrcode' => $qrcode,
                        'unitof' => $unitof,
                        'property' => $property,
                        'detail' => $detail,
                    ];
                }
            }
        }

        return json_encode(
            [
                'draw' => intval($request->draw),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => $reportArray,
            ]
        );

        $this->data['qrcodeDetail'] = $propertyUnitsMapped;
        $this->data['property_units'] = $propertyUnits;
    }

    public function unitsServiced(Request $request)
    {
        //1047: Property Manger Portal - Drill down on units serviced Comment #3.
        $date = \Carbon\Carbon::parse($request->date)->format('Y-m-d');
        $start = \Carbon\Carbon::parse($date)->copy()->format('Y-m-d') . ' 06:00:00';
        $end = \Carbon\Carbon::parse($date)->addDays(1)->copy()->format('Y-m-d') . ' 05:59:59';

        //dd($date, $start, $end);

        $properties = $this->propertyList()
            ->whereHas(
                'service',
                function ($query) use ($start, $end) {
                    $query->where('pickup_start', '<=', $this->usertime->startTime)
                        ->where('pickup_finish', '>=', $this->usertime->endTime);
                }
            )
        ->get();

        $propertyUnits = \App\Units::select('id', 'property_id', 'unit_number', 'building_id', 'building', 'barcode_id', 'is_active')
            ->whereIn('property_id', $properties->pluck('id'))
            ->where('is_active', 1)
            ->where('is_route', 0)
            ->latest()
            ->with(
                [
                    'getPropertyDetail',
                ]
            )
            ->paginate(50);

        $propertyUnitsMapped = $propertyUnits
            ->mapToGroups(
                function ($property_unit, $key) use ($propertyUnits, $start, $end) {
                    $property = $property_unit->getPropertyDetail;
                    $service = $property->service;

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
                        ->where('barcode_id', $property_unit->barcode_id)
                        ->whereBetween(
                            \DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                            [
                                $start,
                                $end,
                            ]
                        )
                        ->with(
                            [
                                'getUserDetail' => function ($query) {
                                    $query->select('id', \DB::raw("CONCAT_WS(' ', `title`, `firstname`, `lastname`) as name"));
                                },
                            ]
                        )
                    ->first();

                    if (!empty($property->id) && !is_null($checkBarcode)) {
                        $property_unit->username = $checkBarcode->getUserDetail->name;
                        $property_unit->created = $checkBarcode->created_at;

                        return [$property->name => $property_unit];
                    } else {
                        return [];
                    }
                }
            );

        $this->data['qrcodeDetail'] = $propertyUnitsMapped;
        $this->data['property_units'] = $propertyUnits;

        return view('manager/unitserviced', $this->data);
    }

    public function editProperty(Request $request)
    {
         //Permission: only Property Manager can access:Start
        if ($this->checkPropertyPermission($request->property)) {
            return redirect('unauthorized');
        }
        //Permission: only Property Manager can access:End
        
        $property = \App\Property::find($request->property);
        $this->data['property'] = $property;
        
        return view('manager.editproperty', $this->data);
    }

    public function updateProperty(Request $request, $id)
    {
        $this->validate(
            $request,
            [
                'image_type' => 'image|mimes:jpeg,png,jpg',
                'reminder' => 'nullable'
            ]
        );

        $property = \App\Property::find($id);
        $property->reminder = htmlentities($request->reminder);

        if (!empty($request->file('image_type'))) {
            $file = $request->file('image_type');
            $filename = time() . $file->getClientOriginalName();
            $filename = str_replace(' ', '', $filename);
            $filePath = 'uploads/user/' . $filename;
            Storage::disk('s3')->put($filePath, file_get_contents($file));
            
            //$file->move(public_path('uploads/property/'), $filename);
            $property->image = $filename;
        }
        $status = $property->save();

        $class = ($status) ? 'success' : 'error';
        $message = ($status) ? 'Property details updated successfully.'
                : 'Property updatation failed.';
        $data = [
            'title' => 'Property',
            'text' => $message,
            'class' => $class,
        ];

        return redirect('property')
            ->with('status', $data);

        return view('manager.editproperty', $this->data);
    }

    public function taskList()
    {
        $properties = $this->propertyList()
            ->whereHas(
                'service',
                function ($query) {
                    $query->where('pickup_start', '<=', $this->usertime->startTime)
                        ->where('pickup_finish', '>=', $this->usertime->endTime);
                }
            )
            ->with(
                [
                    'getEmployee' => function ($query) {
                        $query->select('users.id', \DB::raw("CONCAT_WS(' ', `title`, `firstname`, `lastname`) as name"));
                    }
                ]
            )
        ->get();
        
        $users = $properties->pluck('getEmployee')->unique();
        $this->data['scanBy'] = $users;
        $this->data['properties'] = $properties;
        return view('manager.tasklist', $this->data);
    }

    public function getTask(Request $request)
    {
        $logsArray = [];
        $id = $request->id;
        $i = $request->start;
        $scanBy = $request->scanBy;
        $fre = $request->fre;
        $media = $request->media;
        $search = $request->search['value'];

        if (!empty($request->date)) {
            $startTime = \Carbon\Carbon::parse($request->date . " 06:00:00");
            $endTime = \Carbon\Carbon::parse($startTime)->addDays(1)->copy();
        } elseif (!empty($request->startTime) && !empty($request->endTime)) {
            $startTime = \Carbon\Carbon::parse($request->startTime, getUserTimezone())->addHours(6)->copy();
            $endTime = \Carbon\Carbon::parse($request->endTime, getUserTimezone())
            ->addDays(1)->addHours(5)->addMinutes(59)->addSeconds(59)->copy();
        } else {
            $startTime = $this->usertime->startTime;
            $endTime = $this->usertime->endTime;
        }
        
        $properties = $this->propertyList()
            ->whereHas(
                'service',
                function ($query) {
                    $query->where('pickup_start', '<=', $this->usertime->startTime)
                        ->where('pickup_finish', '>=', $this->usertime->endTime);
                }
            )
        ->get();
        //dd($properties->pluck('id')->toArray());
        //Get total result:Start
        $taskCount = \App\Activitylogs::where('type', 13)
            ->when(
                empty($id),
                function ($query) use ($properties) {
                    $query->whereIn('property_id', $properties->pluck('id')->toArray());
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
            ->when(
                empty($id),
                function ($query) use ($properties) {
                    $query->whereIn('property_id', $properties->pluck('id')->toArray());
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
                $url = url("uploads/task/$media");

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
}
