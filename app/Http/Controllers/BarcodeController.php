<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BarcodeController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('RoleAndPermission:barcodes')
                ->only(['index', 'edit', 'update', 'destroy']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $id = request()->segment(3);
        $status = request()->segment(4);
        $this->data['id'] = request()->segment(3);
        $this->data['status'] = request()->segment(4);
        $properties = $this->propertyList();
        //$this->data['properties'] = $properties->select('id', 'name')->get();

        //Get Total Active Bins:Start
        $this->data['qrcodeActive'] = \App\Units::where(
            [
                'is_active' => 1,
                'is_route' => 0,
            ]
        )
        ->whereIn('property_id', $properties->get()->pluck('id'))
        ->get();
        //Get Total Active Bins:End
        //Get Total Inactive Bins:Start
        $this->data['qrcodeInactive'] = \App\Units::where(
            [
                'is_active' => 0,
                'is_route' => 0,
            ]
        )
        ->whereIn('property_id', $properties->get()->pluck('id'))
        ->get();
        //Get Total Inactive Bins:End

        $this->data['propertyList'] = $this->propertyList()
            //->withTrashed()
            ->orderBy('name')
            ->get();

        return view('barcode/list', $this->data);
    }

    /** 
     * Display the specified resource.
     */
    public function getBarcodeList(Request $request)
    {
        $i = $request->start + 1;
        $list = [];
        $search = $request->search['value'];
        $state = $request->status;
        $type = $request->type;
        $prop = $request->property;
        
        $properies = $this->propertyList()
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
        
        //Get total result:Start (Todo: merge the both queries)
        $units = \App\Units::query()
            ->whereIn('property_id', $properies->pluck('id'))
            ->when(
                $state > -1, ///Status contain zero value for "Inactive" that's why we have campared with -1. To Do: We will find any proper solution.
                function ($query) use ($state) {
                    $query->where('is_active', $state)
                        ->where('is_route', 0);
                }
            )
            ->when(
                $type > -1,
                function ($query) use ($type) {
                    $query->where(
                        function ($query) use ($type) {
                            $query->where('is_route', $type);
                        }
                    );
                }
            )
        ->get();
        //Get total result:Start
        
        //Get result with limit:Start
        $uni = \App\Units::query()
            ->whereIn('property_id', $properies->pluck('id'))
            ->when(
                $state > -1, //Status contain zero value for "Inactive" that's why we have campared with -1. To Do: We will find any proper solution.
                function ($query) use ($state) {
                    $query->where('is_active', $state)
                        ->where('is_route', 0);
                }
            )
            ->when(
                $type > -1,
                function ($query) use ($type) {
                    $query->where(
                        function ($query) use ($type) {
                            $query->where('is_route', $type);
                        }
                    );
                }
            )
            ->with(
                [
                    'getActivityByBarcode' => function ($query) {
                        $query->whereIn('type', [3,6,2])
                            ->latest();
                    }
                ]
            )
        ->latest()
        ->limit($request->length)->offset($request->start)
        ->get();
        //Get result with limit:End

        foreach ($uni as $unit) {
            $address = $unit->getPropertyDetail->address . ', ';
            $address .= $unit->getPropertyDetail->city . ', ';
            $address .= $unit->getPropertyDetail->getState->name . ', ';
            $address .= $unit->getPropertyDetail->zip;
            
            $pro = '<b>Property Name: </b>' . ucwords($unit->getPropertyDetail->name) . '<br/>';
            $pro .= '<b>Property Address: </b>' . ucwords($address) . '<br/>';
            $pro .= '<b>Barcode: </b>' . $unit->barcode_id . '<br/>';

            $isRoute = $unit->is_route ? 'Route Checkpoint' : 'Unit';

            if (!empty($unit->getBuildingDetail->building_name)) {
                $proType = ucwords($unit->getBuildingDetail->building_name);
                $add = ucwords($unit->getBuildingDetail->address);
            
                if ($unit->getPropertyDetail->type == 1) {
                    $pro .= '<b>Streets : </b>' . $proType . '<br/>';
                    $pro .= '<b>Streets Address : </b>' . $add . '<br/>';
                } elseif ($unit->getPropertyDetail->type == 4) {
                    $pro .= '<b>Buildings : </b>' . $proType . '<br/>';
                    $pro .= '<b>Buildings Address : </b>' . $add . '<br/>';
                } elseif ($unit->getPropertyDetail->type == 3) {
                    $pro .= '<b>Floors : </b>' . $proType . '<br/>';
                    $pro .= '<b>Floors Address : </b>' . $add . '<br/>';
                } else {
                    $pro .= '<b>Buildings : </b>' . $proType . '<br/>';
                    $pro .= '<b>Buildings Address : </b>' . $add . '<br/>';
                }
            }
            
            if ($unit->is_active && $unit->getActivityByBarcode->isNotEmpty()) {
                $pro .='<b>Last Scanned : </b>' . \Carbon\Carbon::parse($unit->getActivityByBarcode[0]->created_at)->timezone(getUserTimezone())->format('m-d-Y h:i A');
            }

            $action = "<a href='javascript:void(0)' title='Make as a route checkpoint' class='makeCheckpoint' data-ids='$unit->id' data-status='1'><li class='fa fa-plus' style='color:green'></li></a>";

            $action .= " | <a href='javascript:void(0);' class='deleteEntry' data-id='$unit->id' title='Delete'><li class='fa fa-trash-o' ></li></a>";

            if (!$unit->is_active) {
                $checkBox = "<a href='javascript:void(0);'><input type='checkbox' class='flat datatable-checkbox' style='cursor: pointer' name='table_records' value='$unit->id' ></a>";
            } else {
                $checkBox = "<a href='javascript:void(0);'><input type='checkbox' class='flat datatable-checkbox deactive' style='cursor: pointer' name='table_records' value='$unit->id' checked ></a>";
            }

            if ($unit->is_active || $unit->is_route) {
                $status = '<span class="label label-success">Active</span>';
            } else {
                $status = '<span class="label label-danger">Inactive</span>';
            }

            $list[] = [
                'checkBox' => $checkBox,
                'id' => $i++,
                'prodetail' => $pro,
                'unit_number' => empty($unit->unit_number) ? 'Not Mention.' : $unit->unit_number,
                'type' => $isRoute,
                'barcode' => \QrCode::size(100)->generate($unit->barcode_id),
                'status' => $status,
                'action' => $action,
            ];
        }

        return json_encode(
            [
                'draw' => intval($request->draw),
                'recordsTotal' => !empty($units) ? $units->count() : 0,
                'recordsFiltered' => !empty($units) ? $units->count() : 0,
                'data' => $list,
            ]
        );
    }

    public function bulkActivation(Request $request)
    {
        \App\Units::whereIn('id', $request->id)
            ->update(
                [
                    'is_active' => 1
                ]
            );

        return response()
            ->json(
                [
                    'message' => "Units activated successfully.",
                ]
            );
    }

    public function makeRouteCheckpoint(Request $request)
    {
        \App\Units::whereIn('id', $request->id)
            ->update(
                [
                    'is_route' => $request->type
                ]
            );

        return response()->json(
            [
            'message' => "Units activated successfully.",
            ]
        );
    }

    public function notPickupList()
    {
        //Add permission check:Start
        if (!$this->user->can(['violation', 'employees'])) {
            return redirect('unauthorized');
        }
        //Added permission check:End

        $this->data['id'] = $id = request()->segment(3);
        $this->data['status'] = $status = request()->segment(4);

        $properties = $this->propertyList()
            ->whereHas(
                'service',
                function ($query) {
                    $query->where('pickup_start', '<=', $this->usertime->startTime)
                        ->where('pickup_finish', '>=', $this->usertime->endTime);
                }
            )
        ->get();

        $propertiesId = $properties->map(
            function ($val, $key) {
                return $val->id;
            }
        );

        $this->data['properties'] = $properties;

        $propertyUnits = \App\Units::whereIn('property_id', $propertiesId)
            ->when(
                isset($id) && $id != 0,
                function ($query) use ($id) {
                    $query->where('property_id', $id);
                }
            )
            ->where('is_active', 1)
            ->where('is_route', 0)
            ->orderBy('property_id', 'desc')
            ->with(
                [
                    'getPropertyDetail',
                ]
            )
        ->paginate(50);

        $propertyUnitsMapped = $propertyUnits
            ->mapToGroups(
                function ($property_unit, $key) use ($propertyUnits) {
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
                                $this->usertime->startTime,
                                $this->usertime->endTime,
                            ]
                        )
                    ->get();

                    if (!empty($property->id)
                        && $checkBarcode->isEmpty()) {
                        return [$property->name => $property_unit];
                    } else {
                        return [];
                    }
                }
            );

        $this->data['qrcodeActive'] = \App\Units::whereIn('property_id', $propertiesId)
            ->where('is_route', 0)->where('is_active', 1)->get();

        $this->data['qrcodeInactive'] = \App\Units::whereIn('property_id', $propertiesId)
            ->where('is_route', 0)->where('is_active', 0)->get();

        $this->data['qrcodeDetail'] = $propertyUnitsMapped;
        $this->data['property_units'] = $propertyUnits;

        return view('barcode/notpickuplist', $this->data);
    }

    public function edit($id)
    {
        $this->data['detail'] = \App\Units::findOrFail($id);

        return view('barcode.create', $this->data);
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
        $propertyId = $request->property_id;
        $building = $request->building;
        $floor = $request->floor;

        $this->validate(
            $request,
            [
                'address1' => 'required',
                'unit' => [
                    'required',
                    Rule::unique('units', 'unit_number')
                        ->where(
                            function ($query) use ($propertyId, $floor, $building, $id) {
                                $query->where('property_id', $propertyId)
                                ->where('building', $building)
                                ->where('id', '!=', $id)
                                ->where('floor', $floor);
                            }
                        ),
                    ],
                'floor' => 'nullable',
                'building' => 'nullable',
            ]
        );

        $units = \App\Units::find($id);

        $units->address1 = $request->address1;
        $units->address2 = $request->address1;
        $units->unit_number = $request->unit;
        $units->floor = $request->floor;

        if (isset($request->building) && !empty($request->building)) {
            $units->building_id = $request->building;
            $units->building = \App\Building::find($request->building)
                    ->building_name;
        }

        $status = $units->save();

        $class = ($status) ? 'success' : 'success';
        $message = ($status) ? 'Barcode update successfully.'
            : 'Barcode update successfully.';

        $data = [
            'title' => 'Barcode',
            'text' => $message,
            'class' => $class,
        ];

        return back()->with('status', $data);
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
        $propertyId = \App\Units::where('id', $id)->get();
        $property = \App\Property::find($propertyId[0]->property_id);

        \App\Property::where('id', $property->id)
            ->update(
                [
                    'units' => $property->units - 1
                ]
            );

        $posts = \App\Units::where('id', $id)->delete();

        if (isset($propertyId[0]->barcode_id)
                && !empty($propertyId[0]->barcode_id)) {
            \App\Activitylogs::where('barcode_id', $propertyId[0]->barcode_id)
                ->delete();
        }

        $class = 'success';
        $message = 'Barcode deleted successfully.';
        
        return response()
            ->json(
                [
                    'title' => 'Barcode',
                    'text' => $message,
                    'class' => $class
                ]
            );
    }

    public function deactivation(Request $request)
    {
        \App\Units::where('id', $request->id)
            ->update(
                [
                    'is_active' => 0
                ]
            );

        return response()->json(
            [
            'message' => "Units deactivated successfully.",
            ]
        );
    }
}