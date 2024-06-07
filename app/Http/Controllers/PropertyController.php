<?php

namespace App\Http\Controllers;

use Auth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Pagination\LengthAwarePaginator;

class PropertyController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('RoleAndPermission:properties')
            ->only(
                [
                'index', 'create', 'create',
                'store', 'edit', 'update', 'destroy',
                ]
            );
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->data['properties'] = $this->propertyList()
                ->select('id', 'name', 'address', 'city', 'zip', 'state')
                ->with(
                    [
                        'getState' => function ($query) {
                            $query->select('id', 'name');
                        },
                    ]
                )
                ->withCount(
                    [
                        'getUnit'  => function ($query) {
                            $query->where('is_route', 0);
                        },
                    ]
                )
                ->get();

        $this->data['employee'] = \App\User::select('id', 'firstname', 'lastname')
            ->where(
                [
                    ['subscriber_id', '=', $this->user->subscriber_id],
                ]
            )
            ->whereNotIn('role_id', [10])
        ->get();

        return view('property.list', $this->data);
    }

    public function getPropertyList(Request $request)
    {
        $i = $request->start + 1;
        $search = $request->search['value'];
        $propertyArray = [];

        //Get total result:Start (Todo: merge the both queries)
        $properties = $this->propertyList()
            ->when(
                !empty($search),
                function ($query) use ($search) {
                    $query->where(
                        function ($query) use ($search) {
                            $query->where('name', 'like', "%$search%")
                                ->orWhere('address', 'like', "%$search%")
                                ->orWhere('city', 'like', "%$search%");
                        }
                    );
                }
            )
            ->get();
        //Get total result:End
        //Get result with limit:Start
        $propert = $this->propertyList()
            ->when(
                !empty($search),
                function ($query) use ($search) {
                    $query->where(
                        function ($query) use ($search) {
                            $query->where('name', 'like', "%$search%")
                              //->orWhere('units', 'like', "%$search%")
                                ->orWhere('address', 'like', "%$search%")
                                ->orWhere('city', 'like', "%$search%");
                            //->orWhere('state', 'like', "%$search%")
                              //->orWhere('zip', 'like', "%$search%");
                        }
                    );
                }
            )
            ->with(
                [
                    'getState' => function ($query) {
                        $query->select('id', 'name');
                    },
                ]
            )
            ->withCount(
                [
                    'getUnit' => function ($query) {
                        $query->where('is_route', 1);
                    },
                    //'getRouteCheckPoint',
                ]
            )
            ->limit($request->length)->offset($request->start)
            ->latest()
            ->get();
        //Get result with limit:End

        foreach ($propert as $property) {
            //Identify the property type:Start
            if ($property->type == 1) {
                $type = "Curbside Community";
            } elseif ($property->type == 2) {
                $type = "Garden Style Apartment";
            } elseif ($property->type == 3) {
                $type = "High Rise Apartment";
            } elseif ($property->type == 4) {
                $type = "Townhome";
            }
            //Identify the property type:End
            //Prepare the action link according to user role:Start
            $state = !empty($property->getState->name) ? $property->getState->name : '-';

            if ($this->user->hasRole('property_manager')) {
                $action = "<a href='" . url('property-manager/edit-property?property=' . $property->id . '') . "' title='Edit'><li class='fa fa-edit'></li></a> ";

                $action .= "| <a href='" . url('violation') . "' title='Violation' ><li class='fa fa-chain-broken'></li></a> ";

                $action .= "| <a href='" . url('activity/logs?property=' . $property->id) . "' title='Activity' ><li class='fa fa-history'></li></a>";
            }

            if (!$this->user->hasRole('property_manager')) {
                $action = "<a href='" . url('property/' . $property->id) . "' onclick='return deleteProperty(this, event);' title='Delete'><li class='fa fa-trash-o'></li></a> | ";

                $action .= "<a href='" . url('property/' . $property->id . '/edit/') . "' title='Edit'><li class='fa fa-edit'></li></a> | ";

                $action .= "<a href='" . url('property/qrcode-generate/' . $property->id) . "' title='Qr-Code'><li class='fa fa-qrcode'></li></a> | ";

                $action .= "<a href='javascript:void(0);' class='assign-user' data-propertyid=" . $property->id . " title='Assgin User' ><li class='fa fa-user'></li></a> | ";

                $cou = $property->get_unit_count ? 'green' : 'red';
                $action .= "<a href='" . url('routecheck-point?property=' . $property->id) . "' title='Route Check Point' ><span class='badge bg-$cou'>" . $property->get_unit_count . '</span></a>';

                if ($property->resident_alert && $this->subscriber->resident_alert) {
                    $url3 = url('property/residents-alert/' . $property->id . '/' . 0);
    
                    $action .= ' | <a href="' . $url3 . '" title="Disable Residents Service Alert "><li class="fa fa-bell-o" style="color:green"></li></a>';
                } else if ($this->subscriber->resident_alert) {
                    $url3 = url('property/residents-alert/' . $property->id . '/' . 1);
                    
                    $action .= ' | <a href="' . $url3 . '" title="Disable Residents Service Alert "><li class="fa fa-bell-o" style="color:red"></li></a>';
                }
            }
            //Prepare the action link according to user role:End
            $urlProp = url('property/getQrCodeProperty/' . $property->id);

            $propertyName = '<a href="javascript:void(0);" data-toggle="modal" style="color: #5c5afd;
            text-decoration: underline;" data-target="#printqrcodeProperty" class="qrCodeProperty" data-remote="' . $urlProp . '">' . ucwords($property->name) . '</a>';

            $propertyArray[] = [
                'user_id' => $i++,
                'name' => $propertyName,
                'type' => $type,
                //'unitCount' => $property->get_unit_count,
                'address' => ucwords($property->address . ', ' . $property->city . ', ' . $state . ', ' . $property->zip),
                //'city' => $property->city,
                //'state' => !empty($property->getState->name) ? $property->getState->name : '-',
                //'zip' => $property->zip,
                'action' => $action,
            ];
        }

        return json_encode(
            [
                'draw' => intval($request->draw),
                'recordsTotal' => !empty($properties) ? $properties->count() : 0,
                'recordsFiltered' => !empty($properties) ? $properties->count() : 0,
                'data' => $propertyArray,
            ]
        );
    }

    public function residentAlert($id, $statu)
    {
        $status = \App\Property::query()
            ->where('id', $id)
            ->update(
                [
                    'resident_alert' => $statu
                ]
            );

        $class = ($status) ? 'success' : 'error';
        $message = ($statu) ? 'Service Alert Enable Successfully.' : 'Service Alert Disable Successfully.';
        
        $data = array(
            'title' => 'Property Service Alert',
            'text' => $message,
            'class' => $class
        );

        return redirect()->back()->with('status', $data);
    }

    public function propertyManageViolationList($id = '')
    {
        $propertyList = $this->propertyList()->get();
        //Permission: only property manager can access this panel:Start
        if (!$this->user->hasRole(['property_manager'])) {
            return redirect('unauthorized');
        }
        //Permission: only property manager can access this panel:End
        //Remove rollback status violation.
        $violation = \App\Violation::whereNotIn('status', [1])
                        ->whereIn(
                            'barcode_id',
                            function ($query) use ($propertyList) {
                                $query->select('barcode_id')
                                ->from('units')
                                ->whereIn('property_id', $propertyList->pluck('id')->toArray())
                                ->whereNull('deleted_at');
                            }
                        )
                        ->whereIn('status', [2, 3, 4, 5])
                        ->with(
                            [
                                'getReason', 
                                'getUser',
                                'getUnitNumber'  => function ($query) {
                                    $query->where('is_route', 0);
                                },
                                'images',
                            ]
                        )->latest()->paginate(50);

        //Get violation reason according to subscriber id:Start
        $reasonFirst = \App\Reason::select('reason as text', 'id as value')
                ->where('user_id', $this->user->subscriber_id)
                ->whereNotNull('user_id')
                ->get();
        //Get violation reason according to subscriber id:End
        //Get violation action according to subscriber id:Start
        $action = \App\Action::select('action as text', 'id as value')
                ->where(
                    function ($query) {
                        $query->where('company_id', $this->user->subscriber_id)
                        ->orWhere('type', 0);
                    }
                )->get();
        //Get violation action according to subscriber id:End

        $this->data['violation'] = $violation;
        $this->data['reasons'] = $reasonFirst->toJson();
        $this->data['actions'] = $action->toJson();
        $this->data['propertyList'] = $propertyList;

        //Get template:Start
        $this->data['violationEmailBody'] = \Config::get('constants.violationEmailBody');
        $this->data['violationEmailSubject'] = \Config::get('constants.violationEmailSubject');
        //Get template:End

        return view('violation.index', $this->data);
    }

    public function checkInPending(Request $request)
    {
        //Permission: Only property manager or those user can access this
        //check in section which permite by admin:Start
        if (!$this->user->ability('property_manager', 'report')) {
            return redirect('unauthorized');
        }
        //Permission: Only property manager or those user can access this
        //check in section which permite by admin:Start
        
        if ($request->has('date')) {
            $propertyId = $request->input('property');
            $date = $request->input('date');
            
            $startTime = \Carbon\Carbon::parse($date, getUserTimezone())->addHours(6);
            $endTime = \Carbon\Carbon::parse($date, getUserTimezone())->addDays(1)->addHours(5)->addMinutes(59)->addSeconds(59)->copy();

            $property = \App\Property::query()
                ->when(
                    !is_null($propertyId),
                    function ($query) use ($propertyId) {
                        $query->where('id', $propertyId);
                    }
                );
        } else {
            $property = $this->propertyList();
            $startTime = $this->usertime->startTime;
            $endTime = $this->usertime->endTime;
        }

        $propertyList = $property
            ->whereHas(
                'getUnit',
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
            ->withCount(
                [
                    'checkInProperty' => function ($query) use ($startTime, $endTime) {
                        $query->whereBetween(
                            \DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                            [
                                $startTime,
                                $endTime,
                            ]
                        );
                    },
                ]
            )
            ->with(
                [
                    'service' => function ($query) {
                        $query->select('id', 'property_id', 'pickup_finish');
                    },
                    'checkInProperty' => function ($query) use ($startTime, $endTime) {
                        $query->whereBetween(
                            \DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                            [
                                $startTime,
                                $endTime,
                            ]
                        );
                    },
                    'allcheckIn' => function ($query) use ($startTime, $endTime) {
                        $query->whereBetween(
                            \DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                            [
                                $startTime,
                                $endTime,
                            ]
                        )
                        ->orderBy('id', 'DESC');
                    },
                    'getCheckInUser' => function ($query) use ($startTime, $endTime) {
                        $query->whereBetween(
                            \DB::raw("convert_tz(properties_check_in.updated_at,'UTC','" . getUserTimezone() . "')"),
                            [
                                $startTime,
                                $endTime,
                            ]
                        );
                    },
                    'checkInSmsLog' => function ($query) use ($startTime, $endTime) {
                        $query->whereBetween(
                            \DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                            [
                                $startTime,
                                $endTime,
                            ]
                        )
                        ->latest();
                    },
                ]
            )
            ->paginate(15);
        //dd($propertyList->toArray());
        $this->data['properties'] = $propertyList;
        $this->data['offset'] = paginateOffset($propertyList->currentPage(), 15);

        return view('property.checkpropertylist', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = \App\User::select('id', 'firstname', 'lastname', 'role_id')
            ->where('subscriber_id', $this->user->subscriber_id)
            ->latest()->get();
        
        $this->data['propertyManager'] = $user->filter(
            function ($value, $key) {
                return $value->role_id == 10;
            }
        );
        
        $this->data['remaing_unit'] = $this->get_remaining_unit();
        
        $this->data['states'] = \App\State::all();
        
        $this->data['customers'] = \App\Customer::query()
            ->whereIn(
                'id',
                function ($query) {
                    $query->select('customer_id')
                        ->from('customer_subscribers')
                        //->where('user_id', $this->user->user_id)
                        ->where('subscriber_id', $this->user->subscriber_id);
                }
            )
        ->get();

        $this->data['days'] = days();
        $this->data['users'] = $user;
        
        return view('property/create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function getLatLong($address)
    {
        $params = [
            'address' => $address,
            'sensor' => 'false',
            'key' => 'AIzaSyCubWJgDiR9oE6vy6yimjXSzUcs2tt20D0',
        ];

        $formattedAddr = http_build_query($params);

        $url = 'https://maps.googleapis.com/maps/api/geocode/json?' . $formattedAddr;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        curl_close($ch);

        $output = json_decode($result);

        if ($output->status == 'OK') {
            $data['latitude'] = $output->results[0]->geometry->location->lat;
            $data['longitude'] = $output->results[0]->geometry->location->lng;
            $data['status'] = true;

            return $data;
        } else {
            $data['status'] = false;
            if (isset($output->error_message)) {
                $data['msg'] = $output->error_message;
            } else {
                $data['msg'] = 'Invalid Address.';
            }

            return $data;
        }
    }

    public function store(Request $request)
    {
        $date = $request->datefilter;
        $split = explode('-', $date);
        $start_date = $split[0];
        $end_date = $split[1];
        
        $this->validate(
            $request,
            [
                'customer' => 'required',
                'property_name' => 'required',
                'property_type' => 'required',
                'propertyRadius' => 'required|numeric',
                'address' => 'required',
                'city' => 'required',
                'state' => 'required',
                'zip' => 'required|numeric',
                'datefilter' => 'required',
            ]
        );

        $main_address = $same_address = $lat = $long = 0;

        $address = $request->address . ',' . $request->city . ',' . $request->zip;
        $getLatLong = $this->getLatLong($address);

        if (isset($request->addBuilding) && !empty($request->addBuilding)) {
            $numberOfUnit = array_column($request->addBuilding, 'unit');
            $unitSum = array_sum($numberOfUnit);
            $remaningUnit = $this->checkUnitCountForExcel();

            if ($remaningUnit < $unitSum) {
                return redirect('property/create')
                    ->with('status', 'You have exceed the limit of create barcode.');
            }
        }

        if ($getLatLong['status'] == false) {
            return redirect()->back()->with('status', $getLatLong['msg']);
        }

        $lat = $getLatLong['latitude'];
        $long = $getLatLong['longitude'];

        if (count($request->main_address) == 2) {
            $main_address = 1;
            $same_address = 1;
        } elseif ($request->main_address[0] == 'same_address') {
            $same_address = 1;
        } elseif ($request->main_address[0] != 'same_address') {
            $main_address = 1;
        }

        $property = new \App\Property();

        $property->name = $request->property_name;
        $property->type = $request->property_type;
        $property->address = $request->address;
        $property->city = $request->city;
        $property->state = $request->state;
        $property->zip = $request->zip;
        $property->radius = $request->propertyRadius;
        $property->latitude = $lat;
        $property->longitude = $long;
        $property->customer_id = $request->customer;
        $property->user_id = $this->user->id;
        $property->subscriber_id = $this->user->subscriber_id;
        $property->main_address = $main_address;
        $property->same_address = $same_address;
        
        $status = $property->save();
        $propertyId = $property->id;

        \App\Activitylogs::create(
            [
                'text' => 'Property Added',
                'user_id' => $this->user->id,
                'updated_by' => $this->user->id,
                'barcode_id' => null,
                'type' => null,
                'ip_address' => $request->ip(),
            ]
        );

        $pick_freq = $request->pick_frequency;
        $freq_count = count($pick_freq);

        for ($i = 0; $i < $freq_count; ++$i) {
            $propertyfrequencies = new \App\PropertyFrequencies();

            $propertyfrequencies->property_id = $propertyId;
            $propertyfrequencies->day = $pick_freq[$i];
            $propertyfrequencies->save();
        }

        $service = new \App\Service();

        $service->pickup_start = $start_date;
        $service->pickup_finish = $end_date;
        $service->pickup_frequency = 0;
        $service->pickup_type = $request->pick_type;
        $service->waste_weight = $request->waste_weight;
        $service->recycle_weight = $request->recycle_weight;
        $service->waste_reduction_target = $request->waste_reduction;

        if (isset($request->qr_code_tracking)
                && !empty($request->qr_code_tracking)) {
            $service->qrcode_tracking = $request->qr_code_tracking;
        } else {
            $service->qrcode_tracking = 0;
        }

        if (isset($request->valet_trash)
                && !empty($request->valet_trash)) {
            $service->valet_trash = $request->valet_trash;
        } else {
            $service->valet_trash = 0;
        }

        if (isset($request->recycling) && !empty($request->recycling)) {
            $service->recycling = $request->recycling;
        } else {
            $service->recycling = 0;
        }

        $service->property_id = $property->id;
        $service->save();

        $totalUnit = 0;

        foreach ($request->addBuilding as $addBuilding) {
            $buildingAddress = '';
            $buildingUnit = $addBuilding['unit'] < 1 ? 1 : $addBuilding['unit'];
            $buildingAddress = $request->property_type == 2
                    ? $addBuilding['address']
                    : '';

            //if ($request->property_type == 3) {
                if (!empty($addBuilding['address'])) {
                    $buildingAddress = $addBuilding['address'];
                } else {
                    $buildingAddress = $request->address;
                }
           // }

            //if ($request->property_type != 4) {
            $building = \App\Building::create(
                [
                    'building_name' => $addBuilding['name'],
                    'unit_number' => $buildingUnit,
                    'property_id' => $property->id,
                    'address' => $buildingAddress,
                ]
            );
            //}

            $totalUnit = $totalUnit + $addBuilding['unit'];

            for ($i = 1; $i <= $buildingUnit; ++$i) {
                $unit = new \App\Units();
                $unit->property_id = $property->id;
                $unit->type = $request->qr_code_tracking;
                $unit->save();

                $unit->barcode_id = \Hashids::encode($unit->id);

                if ($request->property_type == 4) {
                    $unit->address1 = $addBuilding['address'];
                    $unit->address2 = $addBuilding['address'];
                    $unit->building = $addBuilding['name'];
                    $unit->building_id = $building->id;
                } else {
                    $unit->building = $addBuilding['name'];
                    $unit->building_id = $building->id;
                }
                $unit->save();
            }
        }

        \App\Property::where('id', $property->id)
            ->update(
                [
                    'units' => $totalUnit
                ]
            );

        if (isset($request->propertyManager)
                && !empty($request->propertyManager)) {
            foreach ($request->propertyManager as $propertyManager) {
                \App\UserProperties::create(
                    [
                        'property_id' => $propertyId,
                        'user_id' => $propertyManager,
                        'type' => 2,
                    ]
                );
            }
        }

        #1297: Redundant Route Service: Start
        if (isset($request->redundant)
                && !empty($request->redundant)) {
            foreach ($request->redundant as $redundant) {
                \App\RedundantRouteService::create(
                    [
                        'property_id' => $propertyId,
                        'user_id' => $redundant,
                    ]
                );
            }
        }
        #1297: Redundant Route Service: End

        $class = ($status) ? 'success' : 'error';
        $message = ($status) ? 'Property created successfully.'
                : 'Property creation failed.';
        $data = [
            'title' => 'Property',
            'text' => $message,
            'class' => $class,
        ];

        return redirect('property')
                ->with('status', $data);
    }

    public function addMoreUnit(Request $request)
    {
        $property = \App\Property::select('id', 'name')
            ->with(
                [
                    'getUnit' => function ($query) {
                        $query->select('id', 'building_id', 'property_id', 'building')
                            ->groupBy('building_id')
                            ->where('is_route', 0);
                    },
                ]
            )
        ->find($request->property_id);

        $property->units = $property->units + $request->unit;
        $property->save();

        for ($i = 1; $i <= $request->unit; ++$i) {
            $unit = new \App\Units();
            $unit->property_id = $request->property_id;
            $unit->building_id = $property->getUnit[0]->building_id;
            $unit->building = $property->getUnit[0]->building;
            $unit->save();

            $code = \Hashids::encode($unit->id);
            $unit->barcode_id = $code;
            $unit->save();
        }

        \App\Building::where('id', $property->getUnit[0]->building_id)
            ->update(
                [
                    'unit_number' => DB::raw('unit_number + ' . $request->unit . ''),
                ]
            );

        return response()->json(
            [
                'status' => true,
                'text' => 'Added Unit Successfully.',
            ]
        );
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //Check valid user:Start
        $property = \App\Property::where(
            [
                'id' => $id,
                //'user_id' => $this->user->id,
                'subscriber_id' => $this->user->subscriber_id,
            ]
        )
        ->get();

        if ($property->isEmpty()) {
            return redirect('property');
        }
        //Check valid user:End

        $this->data['remaing_unit'] = $this->get_remaining_unit();

        $property = \App\Property::with(
            [
                'getBuilding' => function ($query) {
                    $query->select('id', 'property_id', 'building_name');
                },
            ]
        )
        ->find($id);

        $this->data['property'] = $property;

        $this->data['service'] = $property->service;
        
        $this->data['customers'] = \App\Customer::query()
            ->whereIn(
                'id',
                function ($query) {
                    $query->select('customer_id')
                        ->from('customer_subscribers')
                        //->where('user_id', $this->user->user_id)
                        ->where('subscriber_id', $this->user->subscriber_id);
                }
            )
        ->get();
        
        $this->data['states'] = \App\State::all();
        
        $this->data['days'] = days();
        
        $this->data['frequencies'] = \App\PropertyFrequencies::select('day')
                ->where('property_id', $property->id)->get();

        // if ($property->type == 4) {
        //     $this->data['units'] = \App\Units::select('id', 'address1', 'building_id')
        //         ->where('property_id', $property->id)
        //         ->get();
        // } else {
            $this->data['units'] = \App\Building::select('id', 'building_name', 'unit_number', 'property_id', 'address')
                ->where('property_id', $property->id)
                ->withCount(
                    [
                        'getUnit'  => function ($query) {
                            $query->where('is_route', 0);
                        },
                    ]
                )
            ->get();
        //}

        $user = \App\User::select('id', 'firstname', 'lastname', 'role_id')
            ->where('subscriber_id', $this->user->subscriber_id)
            ->latest()->get();
        
        $this->data['propertyManager'] = $user->filter(
            function ($value, $key) {
                return $value->role_id == 10;
            }
        );

        $this->data['users'] = $user;

        $frequencies = \App\PropertyFrequencies::select('day')
            ->where('property_id', $property->id)
            ->get();

        $frequenci = [];

        foreach ($frequencies as $frequencie) {
            $frequenci[] = $frequencie->day;
        }

        $propertyManageId = [];
        $array = \App\UserProperties::select('user_id')
            ->where('property_id', $id)->where('type', 2)->get();

        foreach ($array as $arrays) {
            $propertyManageId[] = $arrays->user_id;
        }

        $this->data['propertyCheck'] = $propertyManageId;

        #1297: Redundant Route Service: Start
        $redundantId = [];
        $arrayR = \App\RedundantRouteService::select('user_id')
            ->where('property_id', $id)->get();

        foreach ($arrayR as $arrays) {
            $redundantId[] = $arrays->user_id;
        }

        $this->data['redundant'] = $redundantId;
        #1297: Redundant Route Service: End

        $this->data['frequencies'] = $frequenci;

        return view('property.create', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {   
        $date = $request->datefilter;
        $split = explode('-', $date);
        $start_date = $split[0];
        $end_date = $split[1];

        $this->validate(
            $request,
            [
                'customer' => 'required',
                'property_name' => 'required',
                'propertyRadius' => 'required|numeric',
                'address' => 'required',
                'city' => 'required',
                'state' => 'required',
                'zip' => 'required|numeric',
            ]
        );

        // if ($request->property_type == 1) {
        //     \App\Building::where('property_id', $id)
        //         ->update(
        //             [
        //                 'building_name' => $request->property_name,
        //             ]
        //         );
        // 
        //     \App\Units::where('property_id', $id)
        //         ->update(
        //             [
        //                 'building' => $request->property_name,
        //             ]
        //         );
        // }

        if (isset($request->addBuilding) && !empty($request->addBuilding)) {
            $numberOfUnit = array_column($request->addBuilding, 'unit');
            $unitSum = array_sum($numberOfUnit);
            $remaningUnit = $this->checkUnitCountForExcel();

            if ($remaningUnit < $unitSum) {
                return back()->with('status', 'You have exceed the limit of create barcode.');
            }
        }

        $main_address = $same_address = $lat = $long = 0;
        $address = $request->address . ',' . $request->city . ',' . $request->zip;
        $getLatLong = $this->getLatLong($address);

        if ($getLatLong['status'] == false) {
            return redirect()->back()->with('status', $getLatLong['msg']);
        }

        $lat = $getLatLong['latitude'];
        $long = $getLatLong['longitude'];

        if (count($request->main_address) == 2) {
            $main_address = 1;
            $same_address = 1;
        } elseif ($request->main_address[0] == 'same_address') {
            $same_address = 1;
        } elseif ($request->main_address[0] != 'same_address') {
            $main_address = 1;
        }

        $property = \App\Property::find($id);
        $property->name = $request->property_name;
        $property->address = $request->address;
        $property->city = $request->city;
        $property->state = $request->state;
        $property->zip = $request->zip;
        $property->latitude = $lat;
        $property->longitude = $long;
        $property->radius = $request->propertyRadius;
        $property->customer_id = $request->customer;
        $property->same_address = $same_address;
        $property->main_address = $main_address;
        $status = $property->save();
        $propertyId = $property->id;

        \App\Activitylogs::create(
            [
                'text' => 'Property Updated',
                'user_id' => $this->user->id,
                'updated_by' => $this->user->id,
                'barcode_id' => null,
                'type' => null,
                'ip_address' => $request->ip(),
            ]
        );

        $PropertyFrequencies = \App\PropertyFrequencies::where('property_id', $id)->delete();

        $pick_freq = $request->pick_frequency;
        $freq_count = count($pick_freq);

        for ($i = 0; $i < $freq_count; ++$i) {
            $propertyfrequencies = new \App\PropertyFrequencies();
            $propertyfrequencies->property_id = $propertyId;
            $propertyfrequencies->day = $pick_freq[$i];
            $propertyfrequencies->save();
        }

        $service = new \App\Service();
        $service = $property->service;
        $service->pickup_start = $start_date;
        $service->pickup_finish = $end_date;
        $service->pickup_frequency = 0;
        $service->pickup_type = $request->pick_type;
        $service->waste_weight = $request->waste_weight;
        $service->recycle_weight = $request->recycle_weight;
        $service->waste_reduction_target = $request->waste_reduction;

        if (isset($request->qr_code_tracking) && !empty($request->qr_code_tracking)) {
            $service->qrcode_tracking = $request->qr_code_tracking;
        } else {
            $service->qrcode_tracking = 0;
        }
        $service->save();
        $totalUnit = 0;

        if (isset($request->addBuilding) && count($request->addBuilding) > 0) {
            foreach ($request->addBuilding as $addBuilding) {
                $buildingUnit = $addBuilding['unit'] < 1 ? 1 : $addBuilding['unit'];
                
                $isBuildingCheck = \App\Building::where(
                    [
                        'building_name' => $addBuilding['name'],
                        'property_id' => $property->id,
                    ]
                )->get();
                
                //if ($request->property_type != 4) {
                    if ($isBuildingCheck->isEmpty()) {
                        $building = \App\Building::create(
                            [
                                'building_name' => $addBuilding['name'],
                                'unit_number' => $buildingUnit,
                                'property_id' => $property->id,
                                'address' => !empty($addBuilding['address']) ? $addBuilding['address'] : '',
                            ]
                        );

                        $buildingId = $building->id;
                        $buildingName = $addBuilding['name'];
                    } else {
                        \App\Building::where(
                            [
                                'id' => $isBuildingCheck[0]->id,
                            ]
                        )
                        ->update(
                            [
                                'unit_number' => DB::raw('unit_number + ' . $buildingUnit . ''),
                            ]
                        );

                        $buildingId = $isBuildingCheck[0]->id;
                        $buildingName = $isBuildingCheck[0]->building_name;
                    }
                //}

                $totalUnit = $totalUnit + $addBuilding['unit'];

                for ($i = 1; $i <= $buildingUnit; ++$i) {
                    $unit = new \App\Units();
                    $unit->property_id = $property->id;
                    $unit->type = $request->qr_code_tracking;
                    $unit->save();

                    $unit->barcode_id = \Hashids::encode($unit->id);
                    $unit->building = $buildingName;
                    $unit->building_id = $buildingId;
                    $unit->save();
                }
            }
            
            \App\Property::where('id', $property->id)
                ->update(
                    [
                        'units' => DB::raw('units + ' . $totalUnit . ''),
                    ]
                );
        }

        \App\UserProperties::where(
            [
                'property_id' => $propertyId,
                'type' => 2,
            ]
        )->delete();
        //#981: Allow End Users to Edit Building Name and Details: Start

        #1297: Redundant Route Service: Start
        \App\RedundantRouteService::where(
            [
                'property_id' => $propertyId,
            ]
        )->delete();
        #1297: Redundant Route Service: End        
        
        if (!empty($request->editBuilding) && count($request->editBuilding) > 0) {
            foreach ($request->editBuilding as $editB) { 
                $editB = (object) $editB;

                \App\Building::where('id', $editB->bid)
                    ->update(
                        [
                            'building_name' => $editB->name,
                            'address' => $editB->address,
                        ]
                    );

                \App\Units::where('building_id', $editB->bid)
                ->update(
                    [
                        'building' => $editB->name,
                    ]
                );

                if (!empty($editB->more)) {
                    for ($i = 1; $i <= $editB->more; ++$i) {
                        $unit = new \App\Units();
                        $unit->property_id = $property->id;
                        $unit->type = $request->qr_code_tracking;
                        $unit->save();

                        $unit->barcode_id = \Hashids::encode($unit->id);
                        $unit->building = $editB->name;
                        $unit->building_id = $editB->bid;
                        $unit->save();
                    }

                    \App\Property::where('id', $property->id)
                    ->update(
                        [
                            'units' => DB::raw('units + ' . $editB->more . ''),
                        ]
                    );
                }
            }
        }

        if (!empty($request->editunit) && count($request->editunit) > 0) {
            foreach ($request->editunit as $editB) {
                $editB = (object) $editB;

                \App\Units::where('id', $editB->uid)
                    ->update(
                        [
                            'address1' => $editB->address,
                            'address2' => $editB->address,
                        ]
                    );
            }
        }
        //#981: Allow End Users to Edit Building Name and Details: End

        if (isset($request->propertyManager) && !empty($request->propertyManager)) {
            foreach ($request->propertyManager as $propertyManager) {
                \App\UserProperties::create(
                    [
                        'property_id' => $propertyId,
                        'user_id' => $propertyManager,
                        'type' => 2,
                    ]
                );
            }
        }

        #1297: Redundant Route Service: Start
        if (isset($request->redundant) && !empty($request->redundant)) {
            foreach ($request->redundant as $redundant) {
                \App\RedundantRouteService::create(
                    [
                        'property_id' => $propertyId,
                        'user_id' => $redundant,
                    ]
                );
            }
        }
        #1297: Redundant Route Service: End

        $class = ($status) ? 'success' : 'error';
        $message = ($status) ? 'Property updated successfully.' : 'Property updation failed.';
        $data = [
            'title' => 'Property',
            'text' => $message,
            'class' => $class,
        ];

        return redirect('property')->with('status', $data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $barcodeId = \App\Units::select('barcode_id')
                ->where('property_id', $id)
                ->get();

            \App\Activitylogs::whereIn('barcode_id', $barcodeId->toArray())
                    ->delete();

            \App\Units::where('property_id', $id)->delete();

            \App\Building::where('property_id', $id)->delete();

            \App\UserProperties::where('property_id', $id)->delete();

            \App\Service::where('property_id', $id)->delete();

            \App\PropertyFrequencies::where('property_id', $id)->delete();

            \App\walkThroughRecord::where('property_id', $id)->delete();

            \App\Violation::whereIn('barcode_id', $barcodeId->toArray())
                   ->delete();

            \App\BarcodeNotes::whereIn('barcode_id', $barcodeId->toArray())
                   ->delete();

            \App\TaskAssign::where('property_id', $id)->delete();
            
            \App\Property::where('id', $id)->delete();

            $data = [
                'title' => 'Property',
                'text' => 'Property deleted successfully.',
                'class' => 'success',
            ];
        } catch (\Exception $e) {
            $data = [
                'title' => 'Property',
                'text' => $e,
                'class' => 'error',
            ];
        }

        return redirect('property')->with('status', $data);
    }

    public function generateQrCode($id, Request $request)
    {  
         //Check user can access:Start.
        if ($this->checkBarcodeListPermission($id)) {
            return redirect('unauthorized');
        }
        //Check user can access:End.

        $units = $pUnit = collect([]);
        
        $property = \App\Property::select('id', 'name', 'type')
            ->where('id', $id)
            ->with(
                [
                    'getBuilding' => function ($query) use ($request) {
                        $query->select('id', 'building_name', 'property_id')
                            ->when(
                                !empty($request->buildingSearch),
                                function ($query) use ($request) {
                                    $query->where('id', $request->buildingSearch);
                                }
                            )
                            ->orderBy('building_name')
                            ->with(
                                [
                                    'getUnit' => function ($query) use ($request) {
                                        $query->select('id', 'unit_number', 'barcode_id', 'building_id', 'property_id', 'is_active')
                                        ->where('is_route', 0)
                                        ->when(
                                            !empty($request->unitsSearch),
                                            function ($query) use ($request) {
                                                $query->where('unit_number', 'like', "%$request->unitsSearch%");
                                            }
                                        )
                                        ->orderBy('unit_number');
                                    }
                                ]
                            );
                    }
                ]
            )
        ->first();
        
        foreach ($property->getBuilding as $code) {
            if (!empty($code->getUnit)) {
                foreach ($code->getUnit as $codes) {
                    $units[] = $codes;
                }
            }
        }

        $page = !empty($_GET['page']) ? $_GET['page'] : 1;

        $paginate = new LengthAwarePaginator(
            $units->forPage($page, 50),
            $units->count(),
            50,
            $page,
            ['path' => url("property/qrcode-generate/$id")]
        );
 
        $this->data['propertyType'] = $property->type;
        $this->data['propertyName'] = $property->name;
        $this->data['property'] = $paginate;
        $this->data['property_id'] = $id;
        $this->data['pBuilding'] = \App\Building::where('property_id', $id)->get();
        $this->data['unitsSearch'] = !empty($request->unitsSearch) ?  $request->unitsSearch : '';
        $this->data['buildingSearch'] = !empty($request->buildingSearch) ?  $request->buildingSearch : '';

        return view('property.qrcode', $this->data);
    }

    public function assignempproperty(Request $request)
    {
        $propertyid = $request->property_id;
        $empid = $request->employees;
        $property = true;

        if (empty($empid)) {
            return response()
                ->json(
                    [
                        'title' => 'Property',
                        'text' => 'No employee found.',
                        'class' => 'error',
                    ]
                );
        } else {
            $empCount = count($empid);
            for ($i = 0; $i < $empCount; ++$i) {
                $empCheck = \App\UserProperties::where('user_id', $empid[$i])
                    ->where('property_id', $propertyid)->first();
                if (!$empCheck) {
                    $property = new \App\UserProperties();
                    $property->property_id = $propertyid;
                    $property->user_id = $empid[$i];
                    $property->save();
                    $property = true;
                }
            }
        }

        return response()
            ->json(
                [
                    'title' => 'Property',
                    'text' => ($property) ? 'Property assigned successfully.' : 'Property already assigned.',
                    'class' => $property ? 'success' : 'error',
                ]
            );
    }

    public function selectedids(Request $request)
    {
        $propertyid = $request->property_id;
        $empid = $request->empid;
        $empCount = count($empid);

        for ($i = 0; $i < $empCount; ++$i) {
            $empCheck = \App\UserProperties::where('user_id', $empid[$i])
                    ->where('property_id', $propertyid)->first();

            if (count($empCheck) > 0) {
                $response = [
                    'status' => 0,
                    'message' => 'User exists',
                ];
            }
        }

        return response()->json($response);
    }

    public function getassignedemployees(Request $request)
    {
        $propertyid = $request->property_id;
        $employessDet = \App\UserProperties::where('property_id', $propertyid)
                ->pluck('user_id');

        return $employessDet;
    }

    public function deleteemployeeproperty(Request $request)
    {
        $propertyid = $request->property_id;
        $empid = $request->empid;

        $delete = \App\UserProperties::where('user_id', $empid)
                ->where('property_id', $propertyid)->delete();

        if (!empty($delete)) {
            $response = [
                'status' => 0,
                'message' => 'Employee Unassigned.',
            ];
        }

        return response()->json($response);
    }

    public function detailsautopuplate(Request $request)
    {
        $customerId = $request->customer_id;

        $details = Customer::find($customerId);

        $response = [
            'status' => 1,
            'address' => $details->address,
            'city' => $details->city,
            'state' => $details->state,
            'zip' => $details->zip,
                ];

        return response()->json($response);
    }

    protected function get_package_qr_code()
    {
        $checkHaveAddUnit = DB::table('subscriptions')
                        ->select('subscriptions.package_qr_code')
                        ->join('subscribers', 'subscriptions.id', '=', 'subscribers.subscription_id')
                        ->where('subscribers.id', $this->user->subscriber_id)
                        ->where('subscriptions.package_qr_code', '>', function ($query) {
                            $query->selectRaw('count(*)')
                            ->from('units')
                            ->whereIn(
                                'property_id',
                                function ($query) {
                                    $query->select('id')
                                        ->from('properties')
                                        ->where('user_id', Auth::user()->id)
                                        ->where('deleted_at', null);
                                }
                            )
                            ->where('deleted_at', null);
                        })
                        ->get()->count();

        if ($checkHaveAddUnit > 0) {
            return true;
        } else {
            return false;
        }
    }

    protected function get_remaining_unit()
    {
        $totalUnit = \App\Units::whereIn(
            'property_id',
            function ($query) {
                $query->select('id')
                    ->from('properties')
                        ->whereIn(
                            'user_id',
                            function ($query) {
                                $query->select('id')
                                    ->from('users')
                                    ->where('subscriber_id', $this->user->subscriber_id)
                                    ->whereNull('deleted_at');
                            }
                        )
                        ->whereNull('deleted_at');
            }
        )->get();

        $sub = \App\Subscription::select('package_qr_code')
            ->where(
                'id',
                function ($query) {
                    $query->select('subscription_id')
                        ->from('subscribers')
                        ->where(
                            'id',
                            function ($query) {
                                $query->select('subscriber_id')
                                    ->from('users')
                                    ->where('id', $this->user->id)
                                    ->whereNull('deleted_at');
                            }
                        )
                            ->whereNull('deleted_at');
                }
            )->get();

        $remaningUnit = $sub[0]->package_qr_code - $totalUnit->count();

        return $remaningUnit;
    }

    public function importProperties(Request $request)
    {
        $import = '';
        if (Input::file('uploadexcel')) {
            $path = Input::file('uploadexcel');
            $excel = Excel::load($path)->get()->toArray();
            $arry = array_filter(
                $excel,
                function ($v) {
                    return array_filter($v) !== [];
                }
            );
            
            foreach ($excel as $row) {
                //Check user have property access: Start
//                $checkProperty = \App\Property::where('id', (int) $row['property_id'])
//                        ->when($this->user->hasRole('admin'), function($query) {
//                            $query->where('subscriber_id', $this->user->subscriber_id);
//                        })
//                        ->when(!$this->user->hasRole('admin'), function($query) {
//                            $query->where(function($query) {
//                                $query->whereIn('id', function($query) {
//                                    $query->select('property_id')
//                                    ->from('user_properties')
//                                    ->where('user_id', $this->user->id)
//                                    ->whereNull('deleted_at');
//                                })
//                                ->orWhere('user_id', $this->user->id);
//                            });
//                        })
//                        ->get();
                //Check user have property access: End
                
                //Unique Validation for Unit Number (with respect to property id and building id): Start
                $validate = \App\Units::where('property_id', $request->property_id)
                    ->where('unit_number', (string) $row['unit_number'])
                    ->where('is_route', 0)
                    ->when(
                        !empty($row['building_id']),
                        function ($query) use ($row) {
                            $query->where('building_id', (int) $row['building_id']);
                        }
                    )
                ->get();
                //Unique Validation for Unit Number (with respect to property id and building id): End
                //Update Unit Number: Start
                if (isset($row['unit_number']) && !empty($row['unit_number']) && $validate->isEmpty()) {
                    \App\Units::where('id', $row['unit_id'])
                        ->where('is_route', 0)
                        ->update(
                            [
                                'unit_number' => $row['unit_number'],
                            ]
                        );

                    $import = true;
                }
                //Update Unit Number: End
            }
        }

        if ($import) {
            $status = true;
            $message = 'Unit Number updated successfully.';
        } else {
            $status = false;
            $message = 'Unit number must be unique.';
        }

        $class = ($status) ? 'success' : 'error';
        $data = [
            'title' => 'Excel',
            'text' => $message,
            'class' => $class,
        ];

        return back()->with('status', $data);
    }

    protected function checkUnitCountForExcel()
    {
        $totalUnit = \App\Units::whereIn(
            'property_id',
            function ($query) {
                $query->select('id')
                    ->from('properties')
                    ->whereIn(
                        'user_id',
                        function ($query) {
                            $query->select('id')
                                ->from('users')
                                ->where('subscriber_id', $this->user->subscriber_id)
                                ->whereNull('deleted_at');
                        }
                    )
                    ->whereNull('deleted_at');
            }
        )
        ->where('is_route', 0)
        ->get();

        $sub = \App\Subscription::select('package_qr_code')
            ->where(
                'id',
                function ($query) {
                    $query->select('subscription_id')
                            ->from('subscribers')
                            ->where(
                                'id',
                                function ($query) {
                                    $query->select('subscriber_id')
                                    ->from('users')
                                    ->where('id', $this->user->id)
                                    ->whereNull('deleted_at');
                                }
                            )
                            ->whereNull('deleted_at');
                }
            )->get();

        $remaningUnit = $sub[0]->package_qr_code - $totalUnit->count();

        return $remaningUnit;
    }

    protected function checkUnitNumberUnique($propertyId, $unitNumber, $building)
    {
        $check = \App\Units::where(
            [
                'property_id' => $propertyId,
                'building' => $building,
                'unit_number' => $unitNumber,
            ]
        )->get();

        if ($check->isEmpty()) {
            return true;
        } else {
            return false;
        }
    }

    protected function getbuilding_name($chkname, $propertyId)
    {
        if ($chkname) {
            $building = \App\Building::where('property_id', $propertyId)->get();
            if (!$building->isEmpty()) {
                foreach ($building as $build) {
                    if (strcasecmp($build->building_name, $chkname) === 0) {
                        return $build->id;
                    }
                }
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    public function getSample($id)
    {
        $units = \App\Units::where('property_id', $id)
            ->where('is_route', 0)
            ->with(
                [
                    'getPropertyDetail'
                ]
            )
        ->get();

        $multiplied = $units->map(
            function ($unit) {
                $test = $unit;
                $temp = collect($unit->toArray());

                if (!empty($unit->barcode_id)) {
                    $address = $unit->getPropertyDetail->address
                        . ', ' . $unit->getPropertyDetail->city
                        . ', ' . $unit->getPropertyDetail->zip;

                    $unitArray['Property Id'] = $unit->property_id;
                    $unitArray['Unit Id'] = $unit->id;
                    $unitArray['Building Id'] = $unit->building_id;
                    $unitArray['Property Name'] = $unit->getPropertyDetail->name;
                    $unitArray['Barcode Id'] = $unit->barcode_id;
                    $unitArray['Property Address'] = $address;

                    if (isset($unit->getBuildingDetail->building_name) && $unit->getPropertyDetail->type != 1) {
                        $unitArray['Building Name'] = $unit->getBuildingDetail->building_name;
                    } else {
                        $unitArray['Building Name'] = '';
                    }

                    $unitArray['Unit Number'] = $unit->unit_number;

                    return $unitArray;
                }
            }
        );

        $schoolArray = [];
        $schoolArray[] = [
            'Property Id',
            'Unit Id',
            'Building Id',
            'Property Name',
            'Barcode Id',
            'Property Address',
            'Building Name',
            'Unit Number',
            ];

        foreach ($multiplied as $schl) {
            $schoolArray[] = $schl;
        }

        $sheetName = 'Excel';
        $createExcel = $this->createExcel($schoolArray, $sheetName);
    }

    public function addBuilgingDetailManually()
    {
        $properties = \App\Property::all();

        foreach ($properties as $property) {
            $untis = $property->getUnit->where('is_route', 0);
            //Property type Town Home
            if (isset($property) && $property->type == 4) {
                \App\Building::where('property_id', $property->id)->delete();

                \App\Units::where(
                    [
                        'type' => 4,
                        'property_id' => $property->id,
                    ]
                )
                ->update(
                    [
                        'address1' => $property->address,
                        'building_id' => '',
                        'building' => '',
                    ]
                );

            //Property type High Rise
            } elseif (isset($property) && $property->type == 3) {
                $building = $property->getBuilding;

                if ($building->isEmpty()) {
                    $lastInsertId = \App\Building::create(
                        [
                            'property_id' => $property->id,
                            'building_name' => $property->name,
                            'address' => $property->address,
                            'unit_number' => $untis->count(),
                        ]
                    );

                    \App\Units::where(
                        [
                            'type' => 3,
                            'property_id' => $property->id,
                        ]
                    )
                    ->update(
                        [
                            'building_id' => $lastInsertId->id,
                            'building' => $property->name,
                        ]
                    );
                } elseif ($building->count() == 1) {
                    if (empty($building->address)) {
                        \App\Building::where('id', $building[0]->id)
                            ->update(
                                [
                                    'address' => $property->address,
                                    'unit_number' => $untis->count(),
                                ]
                            );
                    }

                    \App\Units::where(
                        [
                            'type' => 3,
                            'property_id' => $property->id,
                        ]
                    )
                    ->update(
                        [
                            'building_id' => $building[0]->id,
                            'building' => $building[0]->building_name,
                        ]
                    );
                } elseif ($building->count() > 1) {
                    continue;
                }
                //Property type Garden style
            } elseif (isset($property) && $property->type == 2) {
                $building = $property->getBuilding;

                if ($building->isEmpty()) {
                    $lastInsertId = \App\Building::create(
                        [
                            'property_id' => $property->id,
                            'building_name' => $property->name,
                            'address' => $property->address,
                            'unit_number' => $untis->count(),
                        ]
                    );

                    \App\Units::where(
                        [
                            'type' => 2,
                            'property_id' => $property->id,
                        ]
                    )
                    ->update(
                        [
                            'building_id' => $lastInsertId->id,
                            'building' => $property->name,
                        ]
                    );
                }
            } elseif (isset($property) && $property->type == 1) {
                \App\Building::where('property_id', $property->id)->delete();

                \App\Units::where(
                    [
                        'type' => 1,
                        'property_id' => $property->id
                    ]
                )
                ->update(
                    [
                        'building_id' => '',
                        'building' => ''
                    ]
                );
            }
        }
    }

    public function getImages($violationId)
    {
        $violation = \App\Violation::find($violationId);

        $this->data['violation'] = $violation;

        return view('violation.imagesmodal', $this->data);
    }

    public function getviolationdetails($violationId)
    {
        $violation = \App\Violation::withTrashed()->find($violationId);
        $voilationDetails = $status = '';

        if (!empty($violation)) {
            $vioStatus = $this->user->hasRole('property_manager') ? $violation->manager_status : $violation->status;
            $status = '';

            if ($vioStatus == 6) {
                $status = 'Archived';
            } elseif ($vioStatus == 5) {
                $status = 'Closed';
            } elseif ($vioStatus == 2) {
                $status = 'Submitted';
            } elseif ($vioStatus == 0) {
                $status = 'New';
            } elseif ($vioStatus == 7) {
                $status = 'Read';
            } elseif ($vioStatus == 8) {
                $status = 'In Process';
            } elseif ($vioStatus == 9) {
                $status = 'On Hold';
            } elseif ($vioStatus == 10) {
                $status = 'Sent Notice';
            } else {
                $status = 'Discarded';
            }

            $user = \App\User::withTrashed()
                    ->find($violation->user_id);
            
            // if (empty($violation->type)) {
            //     $unit = \App\Units::where('barcode_id', $violation->barcode_id)
            //         ->withTrashed()->first();
            // } else {
            //     $unit = \App\RouteCheckIn::where('barcode_id', $violation->barcode_id)
            //         ->withTrashed()->first();
            // }

            $unit = \App\Units::where('barcode_id', $violation->barcode_id)
                    ->withTrashed()->first();
            
            $property = \App\Property::withTrashed()
                    ->find($unit->property_id);

            $building = \App\Building::where('id', $unit->building_id)
                    ->withTrashed()->first();

            $reason = \App\Reason::withTrashed()
                    ->find($violation->violation_reason);

            $action = \App\Action::withTrashed()
                    ->find($violation->violation_action);

            $voilationDetails = [
                'id' => $violation->id,
                'status' => $status,
                'user_name' => ucwords($user->firstname . ' ' . $user->lastname),
                'property_name' => ucwords($property->name),
                'address' => ucwords($property->address),
                'reason' => $reason->reason,
                'action' => $action->action,
                'images' => $violation->images,
                'type' => $property->type,
                'unit' => empty($unit->unit_number) ? false : $unit->unit_number,
                'isRoute' => $violation->type,
                'unit_address' => ucwords($unit->address1),
                'created_at' => $violation->created_at,
                'special_note' => $violation->special_note,
                'comment' => !empty($violation->comment) ? $violation->comment : '',
                'reminder' => !empty($property->reminder) ? $property->reminder : ''
            ];
            
            if (!empty($building->address)) {
                $voilationDetails['building_address'] = ucwords($building->address);
            }

            if (!empty($building->building_name)) {
                $voilationDetails['building'] = ucwords($building->building_name);
            }
        }
        $this->data['violation'] = $voilationDetails;

        return view('violation.detailsmodal', $this->data);
    }

    public function getViolationPrint(Request $request)
    {
        //In Base Controller.
        $violations = $this->getViolationDetail($request);

        if (!empty($violations)) {
            $this->data['violations'] = $violations;

            return view('violation.violation_print_view', $this->data);
        } else {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'This violation is rolled back by the user.',
                ]
            );
        }
    }

    public function getEmployee($id)
    {
        $details = \App\Property::select('id')
            ->where('id', $id)
            ->with(
                [
                    'getCheckInUser' => function ($query) {
                        $query->select(\DB::raw('DISTINCT users.id'), 'firstname', 'lastname', 'mobile')
                            ->with(
                                [
                                    'checkinUser' => function ($query) {
                                        $query->whereBetween(
                                            \DB::raw("convert_tz(updated_at,'UTC','america/new_york')"),
                                                [
                                                    getStartEndTime()->startTime,
                                                    getStartEndTime()->endTime
                                                ]
                                            );
                                    }
                                ]
                            );
                    }
                ]
            )
        ->first();

        $this->data['detail'] = $details;

        return view('property.checkinemployeelist', $this->data);
    }

    public function sendSmsEmployee(Request $request)
    {
        $html = '';
        $details = \App\Property::where('id', $request->proId)
            ->with('getEmployee')->first();

        if ($details->getEmployee->isNotEmpty()) {
            foreach ($details->getEmployee as $detail) {
                $html .= '<div class="col-md-6 col-sm-6 col-xs-12">';
                $html .= '<div class="checkbox"><label>';
                $html .= '<input type="checkbox" id="employee-id" value="' . $detail->id . '">';
                $html .= ucwords($detail->firstname . ' ' . $detail->lastname) . '<br/>';
                $html .= ' (Mobile: ' . $detail->mobile . ')</label></div></div>';
            }
        } else {
            $html .= 'false';
        }

        return $html;
    }

    public function sendCheckInSms(Request $request)
    {
        $error = '';
        $msg = [];

        $validator = \Validator::make(
            $request->all(),
            [
                'message' => 'required',
                'checkedValues' => 'required',
            ],
            [
                'checkedValues.required' => 'Please select at least one employee',
                'message.required' => 'The message field is required',
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $errors) {
                $error .= '<div class="alert alert-danger"><strong>'
                        . $errors . '.</strong></div>';
            }

            return $error;
        }

        $proDetail = \App\Property::find($request->proId);
        $detail = \App\User::whereIn('id', $request->checkedValues)->get();

        foreach ($detail as $employee) {
            $a = "Trash Scan App *Do Not Reply*\r\n \r\n";

            $a .= 'Hello ' . $employee->firstname . ' '
               . $employee->lastname . ",\r\n \r\n";
            $a .= 'New message from ' . $this->user->firstname;
            $a .= $this->user->lastname . ' regarding '
                    . $proDetail->name . ":\r\n \r\n";
            $a .= $request->message . "\r\n \r\n";
            $a .= '*To reply to user, send message to '
                    . $this->user->mobile . "\r\n \r\n";

            sms('+1' . $employee->mobile, $a);
            //sms('+917974600385', $a);

            $msg[] = $employee->id;
        }

        if (!empty($msg)) {
            \App\CheckInSmsLog::create(
                [
                    'message' => $request->message,
                    'receiver_id' => collect($msg)->toJson(),
                    'sender_id' => $this->user->id,
                    'property_id' => $request->proId,
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now(),
                ]
            );
        }

        if (empty($error)) {
            echo 'true';
        }
    }

    public function buildingDetele(Request $request)
    {
        $building = \App\Building::find($request->id);
        $noBuilding = \App\Building::where('property_id', $building->property_id)->get();
        
        if ($noBuilding->count() > 1) {
            $unit = $building->getUnit()->delete();
            $building->delete();

            $status = true;
            $text = 'Building deleted successfully.';
            $type = 'success';
        } else {
            $status = false;
            $text = "This property has only one building that's why you are not able to delete the building.";
            $type = 'error';
        }

        return response()->json(
            [
                'status' => $status,
                'text' => $text,
                'type' => $type
            ]
        );
    }
    public function buildingList()
    {
        return view('property.buildinglist', $this->data);
    }

    public function getBuilding(Request $request)
    {
        $i = $request->start + 1;
        $search = $request->search['value'];
        $propertyArray = [];

        //Get total result:Start (Todo: merge the both queries)
        $properties = $this->propertyList()
           ->when(
                !empty($search),
                function ($query) use ($search) {
                    $query->where(
                        function ($query) use ($search) {
                            $query->where('name', 'like', "%$search%")
                                ->orWhere('address', 'like', "%$search%")
                                ->orWhere('city', 'like', "%$search%");
                        }
                    );
                }
           )
            ->get();
        //Get total result:End
        //Get result with limit:Start
        $propert = $this->propertyList()
                ->when(
                    !empty($request->propertyId),
                    function ($query) use ($request) {
                        $query->where('id', $request->propertyId);
                    }
                )
            ->when(
                !empty($search),
                function ($query) use ($search) {
                    $query->where(
                        function ($query) use ($search) {
                            $query->where('name', 'like', "%$search%")
                              //->orWhere('units', 'like', "%$search%")
                                ->orWhere('address', 'like', "%$search%")
                                ->orWhere('city', 'like', "%$search%");
                            //->orWhere('state', 'like', "%$search%")
                              //->orWhere('zip', 'like', "%$search%");
                        }
                    );
                }
            )
            ->with(
                [
                    'getState' => function ($query) {
                        $query->select('id', 'name');
                    },
                ]
            )
            ->withCount(
                [
                    'getUnit' => function ($query) {
                        $query->where('is_route', 1);
                    },
                    'getBuilding'
                ]
            )
            ->limit($request->length)->offset($request->start)
            ->latest()
            ->get();
        //Get result with limit:End
        //dd($propert);
        foreach ($propert as $property) {
            //Identify the property type:Start
            if ($property->type == 1) {
                $type = "Curbside Community";
            }
            elseif ($property->type == 2) {
                $type = "Garden Style Apartment";
            }
            elseif ($property->type == 3) {
                $type = "High Rise Apartment";
            }
            elseif ($property->type == 4) {
                $type = "Townhome";
            }
            //Identify the property type:End
            //Prepare the action link according to user role:Start
            $state = !empty($property->getState->name) ? $property->getState->name : '-';

            if ($this->user->hasRole('property_manager')) {
                $action = "<a href='" . url('property-manager/edit-property?property=' . $property->id . '') . "' title='Edit'><li class='fa fa-edit'></li></a> ";

                $action .= "| <a href='" . url('violation') . "' title='Violation' ><li class='fa fa-chain-broken'></li></a> ";

                $action .= "| <a href='" . url('activity/logs?property=' . $property->id) . "' title='Activity' ><li class='fa fa-history'></li></a>";
            }

            // if (!$this->user->hasRole('property_manager')) {
            //     $action = "<a href='" . url('property/' . $property->id) . "' onclick='return deleteProperty(this, event);' title='Delete'><li class='fa fa-trash-o'></li></a> | ";

            //     $action .= "<a href='" . url('property/' . $property->id . '/edit/') . "' title='Edit'><li class='fa fa-edit'></li></a> | ";

            //     $action .= "<a href='" . url('property/qrcode-generate/' . $property->id) . "' title='Qr-Code'><li class='fa fa-qrcode'></li></a> | ";

            //     $action .= "<a href='javascript:void(0);' class='assign-user' data-propertyid=" . $property->id . " title='Assgin User' ><li class='fa fa-user'></li></a> | ";

            //     $cou = $property->get_unit_count ? 'green' : 'red';
            //     $action .= "<a href='" . url('routecheck-point?property=' . $property->id) . "' title='Route Check Point' ><span class='badge bg-$cou'>" . $property->get_unit_count . '</span></a>';
            // }
            //Prepare the action link according to user role:End

            $propertyArray[] = [
                'user_id' => $i++,
                'name' => ucwords($property->name),
                'type' => $type,
                'building' => $property->get_building_count,
                //'unitCount' => $property->get_unit_count,
                'address' => ucwords($property->address . ', ' . $property->city . ', ' . $state . ', ' . $property->zip),
                //'city' => $property->city,
                //'state' => !empty($property->getState->name) ? $property->getState->name : '-',
                //'zip' => $property->zip,
                //'action' => $action,
            ];
        }

        return json_encode(
            [
                'draw' => intval($request->draw),
                'recordsTotal' => !empty($properties) ? $properties->count() : 0,
                'recordsFiltered' => !empty($properties) ? $properties->count() : 0,
                'data' => $propertyArray,
            ]
        );
    }

    public function getQrCodeProperty($propertyId)
    {
        $p = \App\Property::select('id', 'name')->find($propertyId);
        $this->data['qrproperty'] = $p;

        return view('property.qrproperty', $this->data);
    }
}
