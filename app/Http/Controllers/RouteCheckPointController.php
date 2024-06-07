<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RouteCheckPointController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('RoleAndPermission:properties')
            ->only(
                [
                    'index', 'create', 'store',
                    'edit', 'update', 'destroy',
                    'printBarcodes', 'routeCheckPoint',
                ]
            );
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //Permission check :Start
        if ($this->checkPropertyPermission($request->property)) {
            return redirect('unauthorized');
        }
        //Permission check :End

        $property = \App\Property::with(
            [
                'getBuilding' => function ($query) {
                    $query->select('id', 'property_id', 'building_name');
                },
            ]
        )
        ->find($request->property);

        $this->data['property'] = $property;

        return view('routecheckpoint.list', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $route = new \App\RouteCheckIn();
        $route->name = $request->name;
        $route->address1 = $request->addressOne;
        $route->address2 = $request->addressTwo;
        $route->description = $request->description;
        $route->building_id = $request->buildingId;
        $route->property_id = $request->propertyId;
        $route->is_required = $request->isRequired;
        $route->save();
        $route->barcode_id = \Hashids::encode($route->id) . '-RCP';
        $route->save();

        return response()
            ->json(
                [
                    'status' => true,
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
        $template = \App\RouteCheckIn::select('id', 'name', 'address1', 'address2', 'description', 'building_id', 'is_required')
            ->find($id);

        return response()
            ->json(
                [
                    'detail' => $template,
                    'result' => true,
                ]
            );
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
        $route = \App\RouteCheckIn::find($id);
        $route->name = $request->name;
        $route->address1 = $request->addressOne;
        $route->address2 = $request->addressTwo;
        $route->description = $request->description;
        $route->building_id = $request->buildingId;
        $route->is_required = $request->isRequired;
        $route->save();

        return response()
            ->json(
                [
                    'status' => true,
                ]
            );
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
        \App\Units::where('id', $id)->delete();

        return response()
        ->json(
            [
                'status' => true,
            ]
        );
        //     App\RouteCheckIn::where('id', $id)
        //     ->delete();

        // return response()
        //     ->json(
        //         [
        //             'status' => true,
        //         ]
        //     );
    }

    public function printBarcodes(Request $request)
    {
        $pdfName = time() . '.pdf';

        $routes = \App\RouteCheckIn::select('barcode_id', 'property_id', 'building_id')
            ->where('property_id', $request->propertyId)
            ->with(
                [
                    'getProperty' => function ($query) {
                        $query->select('id', 'type', 'name');
                    },
                    'getBuilding' => function ($query) {
                        $query->select('id', 'building_name');
                    },
                ]
            )
            ->get();

        if ($routes->isNotEmpty()) {
            $this->data['routes'] = $routes;

            return view('routecheckpoint.checkpointpdf', $this->data);
            // $pdf = \PDF::loadView('routecheckpoint.checkpointpdf', $this->data);
            // $pdf->save(public_path().'/uploads/pdf/'.$pdfName);
        }

        return url('/uploads/pdf/' . $pdfName . '');
    }

    public function routeCheckPoint(Request $request)
    {
        $routeArray = [];
        $i = $request->start + 1;
        $searchText = $request->search['value'];
        $buildingId = $request->building;
        // Get total result:Start (Todo: merge the both queries)

        $routesPoint = \App\Units::where('property_id', $request->propertyId)
            ->where('is_route', 1)
            ->when(
                !empty($searchText),
                function ($query) use ($searchText) {
                    $query->where(
                        function ($query) use ($searchText) {
                            $query->WhereRaw("building_id in (select `id` from `buildings` where `building_name` LIKE '%$searchText%')")
                                ->orWhereRaw("property_id in (select `id` from `properties` where `name` LIKE '%$searchText%')")
                                ->orWhereRaw("unit_number in (select `unit_number` from `units` where `unit_number` LIKE '%$searchText%')");
                        }
                    );
                }
            )
            ->when(
                !empty($buildingId),
                function ($query) use ($buildingId) {
                    $query->where('building_id', $buildingId);
                }
            )
            ->get();
        //Get total result:End

        //Get result with limit:Start
        $routes = \App\Units::where('property_id', $request->propertyId)
            ->where('is_route', 1)
            ->when(
                !empty($searchText),
                function ($query) use ($searchText) {
                    $query->where(
                        function ($query) use ($searchText) {
                            $query->WhereRaw("building_id in (select `id` from `buildings` where `building_name` LIKE '%$searchText%')")
                                ->orWhereRaw("property_id in (select `id` from `properties` where `name` LIKE '%$searchText%')")
                                ->orWhereRaw("barcode_id in (select `barcode_id` from `units` where `unit_number` LIKE '%$searchText%')");
                        }
                    );
                }
            )
            ->when(
                !empty($buildingId),
                function ($query) use ($buildingId) {
                    $query->where('building_id', $buildingId);
                }
            )
            ->with(
                [
                    'getPropertyDetail' => function ($query) {
                        $query->select('id', 'type', 'name');
                    },
                    'getBuildingDetail' => function ($query) {
                        $query->select('id', 'building_name', 'address')
                            ->withTrashed();
                    },
                ]
            )
            
        ->limit($request->length)->offset($request->start)
        ->latest()
        ->get();
        //Get result with limit: End

        foreach ($routes as $route) {
            $editUrl = url('/routecheck-point/change-name');

            $property = "<b>Name :</b> <a href='#' class='change-status' data-type='text' data-pk='" . $route->id . "' data-url='" . $editUrl . "' data-title='Route Checkpoint Name:'>" . ucwords($route->unit_number) . "</a>";

            //$property = '<b>Name :</b> ' . ucwords($route->unit_number);
            
            $property .= '<br/><b>Barcode Id :</b> ' . ucwords($route->barcode_id);
            $property .= '<br/><b>Property :</b> ' . ucwords($route->getPropertyDetail->name);

            // if (!empty($route->getBuildingDetail->building_name)
            //     && ($route->getPropertyDetail->type != 4 && $route->getPropertyDetail->type != 1)) {
            //     $property .= '<br/><b>Building :</b> ' . $route->getBuildingDetail->building_name;
            // } else {
            //     $property .= '<br/><b>Building :</b> Not Mention';
            // }

            // $property .= '<br/><b>Address1: </b> ' . ucwords($route->address1);

            // if (!empty($route->address2)) {
            //     $property .= '<br/><b>Address2: </b> ' . ucwords($route->address2);
            // }

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

            //$description = !empty($route->description) ? ucwords($route->description) : 'Not Mention';

            $barcode = \QrCode::size(120)->generate($route->barcode_id);

            // $mandatory = !$route->is_required
            //     ? '<i class="fa fa-close" style="color:red"></i>'
            //     : '<i class="fa fa-check" style="color:green"></i>';

            // $action = '<a href="javascript:void(0);" class="edit-route" data-id="' . $route->id . '"><i class="fa fa-edit"></i></a> | ';

            $action = "<a href='javascript:void(0)' title='Make as a unit' class='makeUnit' data-ids='$route->id' data-status='0'><i class='fa fa-plus' style='color:green'></i></a>";

            $action .= ' | <a href="javascript:void(0);" class="user-trash" data-id="' . $route->id . '"><i class="fa fa-trash"></i></a>';

            $routeArray[] = [
                'id' => $i++,
                'barcode' => $barcode,
                'property' => $property,
               // 'description' => $description,
               // 'mandatory' => $mandatory,
                'action' => $action,
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

    public function changeName(Request $request)
    {
        $query = \App\Units::where('id', $request->pk)
            ->update(
                [
                    'unit_number' => $request->value
                ]
            );

        return response()
            ->json(
                [
                    'status' => $query ? true : false,
                ],
                200
            );
    }

    public function routeCheckPointBackup(Request $request)
    {
        $routeArray = [];
        $i = $request->start + 1;

        //Get total result:Start (Todo: merge the both queries)
        $routesPoint = \App\RouteCheckIn::where('property_id', $request->propertyId)
            ->get();
        //Get total result:End

        //Get result with limit:Start
        $routes = \App\RouteCheckIn::where('property_id', $request->propertyId)
            ->with(
                [
                    'getProperty' => function ($query) {
                        $query->select('id', 'type', 'name');
                    },
                    'getBuilding' => function ($query) {
                        $query->select('id', 'building_name');
                    },
                ]
            )
        ->limit($request->length)->offset($request->start)
        ->latest()
        ->get();
        //Get result with limit: End

        foreach ($routes as $route) {
            $property = '<b>Name :</b> ' . ucwords($route->name);
            $property .= '<br/><b>Barcode Id :</b> ' . ucwords($route->barcode_id);
            $property .= '<br/><b>Property :</b> ' . ucwords($route->getProperty->name);

            if (!empty($route->getBuilding->building_name)
                && ($route->getProperty->type != 4 && $route->getProperty->type != 1)
            ) {
                $property .= '<br/><b>Building :</b> ' . $route->getBuilding->building_name;
            } else {
                $property .= '<br/><b>Building :</b> Not Mention';
            }

            $property .= '<br/><b>Address1: </b> ' . ucwords($route->address1);

            if (!empty($route->address2)) {
                $property .= '<br/><b>Address2: </b> ' . ucwords($route->address2);
            }

            $description = !empty($route->description) ? ucwords($route->description) : 'Not Mention';

            $barcode = \QrCode::size(120)->generate($route->barcode_id);

            $mandatory = !$route->is_required
                ? '<i class="fa fa-close" style="color:red"></i>'
                : '<i class="fa fa-check" style="color:green"></i>';

            $action = '<a href="javascript:void(0);" class="edit-route" data-id="' . $route->id . '"><i class="fa fa-edit"></i></a>';

            $action .= ' | <a href="javascript:void(0);" class="user-trash" data-id="' . $route->id . '"><i class="fa fa-trash"></i></a>';

            $routeArray[] = [
                'id' => $i++,
                'barcode' => $barcode,
                'property' => $property,
                'description' => $description,
                'mandatory' => $mandatory,
                'action' => $action,
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

    public function checkPointQrCode(Request $request)
    {
        $barcodeData = \App\Units::where(
            [
                'property_id' => $request->id,
                'is_route' => 1
            ]
        )
        ->where('is_route', 1)
        ->latest()->paginate(50)
        ->appends(request()->query());

        $this->data['data'] = $barcodeData;

        return view('routecheckpoint.qrcode', $this->data);
    }

    public function makeCheckpoint(Request $request)
    {
        $u = \App\Units::find($request->id);
        $u->is_route = $request->status;
        $u->save();
    }
}
