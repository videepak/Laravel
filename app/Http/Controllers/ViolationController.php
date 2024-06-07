<?php

namespace App\Http\Controllers;

use App\Subscriber;
use App\User;
use App\Violation;
use App\Resident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PDF;
use Mail;
use App\Mail\ViolationResidentSendEmail;
use Illuminate\Support\Facades\Storage;

class ViolationController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        //$this->middleware('RoleAndPermission:violation');
    }

    public function index()
    {
        // if ($this->user->hasRole('property_manager')) {
        //     return redirect('property-manager/violation');
        // }

        //Permission: users access this
        // section which has permission (Violation):Start
        if (!$this->user->can('violation')) {
            return redirect('unauthorized');
        }
        //Permission: users access this
        // section which has permission (Violation):End

        $status = request()->segment(2);
        $propertyList = $this->propertyList()
            //->withTrashed()
            ->orderBy('name')
            ->get();
        
        //Get violation reason according to subscriber id:Start

        $reasonFirst = \App\Reason::select('reason as text', 'id as value')
                ->where('user_id', $this->user->subscriber_id)
                ->whereNotNull('user_id')
                ->withTrashed()
                ->get();

        $reasonSec = \App\Reason::select('reason as text', 'id as value')
                ->whereIn(
                    'id',
                    function ($query) {
                        $query->select('violation_reason')
                        ->from('violations')
                        ->whereIn(
                            'user_id',
                            function ($query) {
                                $query->select('id')
                                  ->from('users')
                                  //->whereNull('deleted_at')
                                  ->where('subscriber_id', $this->user->subscriber_id);
                            }
                        );
                        //->whereNull('deleted_at');
                    }
                )
                ->whereNull('user_id')
                ->withTrashed()
                ->get();

        $reasonMerge = array_merge($reasonFirst->toArray(), $reasonSec->toArray());
        $reason = collect($reasonMerge);
        //Get violation reason according to subscriber id:End
        //Get violation action according to subscriber id:Start
        $action = \App\Action::select('action as text', 'id as value')
                    ->where(
                        function ($query) {
                            $query->where('company_id', $this->user->subscriber_id)
                            ->orWhere('type', 0);
                        }
                    )
                ->withTrashed()
                ->get();
        //Get violation action according to subscriber id:End
                        
        //Get violation action according to subscriber id: Start
        $employee = \App\User::select('id', \DB::raw("CONCAT_WS(' ', `title`, `firstname`, `lastname`) as name"))
            ->when( //#1275: Username filter in Property Manager Portal
                $this->user->hasRole('property_manager'),
                function ($query) {
                    $query->whereIn('id', function ($query) {
                        $query->select('user_id')
                            ->from('user_properties')
                            ->whereIn('property_id', $this->propertyList()->pluck('id'))
                            ->where('type', 1)
                            ->whereNull('deleted_at');
                    });
                },
                function ($query) {
                    $query->where('subscriber_id', $this->user->subscriber_id);
                }
            )
            //->where('subscriber_id', $this->user->subscriber_id)
            ->orderBy('title')
            ->whereNotIn('role_id', [10])
            //->withTrashed()
            ->get();

        //Get violation action according to subscriber id: End
       

        $this->data['reasons'] = $reason;
        $this->data['actions'] = $action;
        $this->data['empolyee'] = $employee;
        $this->data['propertyList'] = $propertyList;
        $this->data['newViolation'] = !empty($status) ? 1 : '';
       
        //Get template:Start
        $template = $this->getViolationEmailContent();
        $this->data['defaultTemplate'] = $template['defaultTemplate']->isNotEmpty()
                ? $template['defaultTemplate'][0]->content
                : \Config::get('constants.violationEmailBody');
        $this->data['templateSubject'] = $template['defaultTemplate']->isNotEmpty()
                ? $template['defaultTemplate'][0]->subject
                : \Config::get('constants.violationEmailSubject');
        $this->data['allTemplate'] = $template['allTemplate']->isNotEmpty()
                ? $template['allTemplate']
                : '';

        $this->data['violationEmailBody'] = \Config::get('constants.violationEmailBody');
        $this->data['violationEmailSubject'] = \Config::get('constants.violationEmailSubject');
        //Get template:End
        
        return view('violation.index', $this->data);
    }

    public function getViolations(Request $request)
    {
        $i = $request->start + 1;
        $image = 0;
        $violationArray = [];
        $state = $request->status;
        $prop = $request->property;
        $username = $request->username;
        $reason = $request->reason;
        $action = $request->action;
        $searchText = $request->search['value'];
        
        $startTime = \Carbon\Carbon::parse($request->startTime, getUserTimezone())->addHours(6)->copy();
        
        $endTime = \Carbon\Carbon::parse($request->endTime, getUserTimezone())
        ->addDays(1)->addHours(5)->addMinutes(59)->addSeconds(59)->copy();
        $col = $this->user->hasRole('property_manager')
            ? 'manager_status' : 'status';
        
        $statusVal = $this->user->hasRole('property_manager') ? [6, 11] : [6];

        $propertyList = $this->propertyList()
            ->when(
                !empty($prop),
                function ($query) use ($prop) {
                    $query->where('id', $prop);
                }
            )
            ->withTrashed()
        ->get();

        //Get total record count: Start
        $violation = \App\Violation::whereBetween(
            \DB::raw("convert_tz(created_at, 'UTC','" . getUserTimezone() . "')"),
            [
                $startTime,
                $endTime,
            ]
        )
        //Remove when API version 8 live: Start
        ->when(
            !$this->user->hasRole('admin') && !$this->user->hasRole('property_manager'),
            function ($query) {
                $query->where('user_id', $this->user->id);
            }
        )
        ->when(
            $this->user->hasRole('admin'),
            function ($query) {
                $query->whereIn(
                    'user_id',
                    function ($query) {
                        $query->from('users')
                            ->select('id')
                            ->where(
                                [
                                    'subscriber_id' => $this->user->subscriber_id,
                                ]
                            );
                    }
                );
            }
        )
        ->when(
            !empty($username),
            function ($query) use ($username) {
                $query->where('user_id', $username);
            }
        )
        ->when(
            !empty($reason),
            function ($query) use ($reason) {
                $query->where('violation_reason', $reason);
            }
        )
        ->when(
            !empty($action),
            function ($query) use ($action) {
                $query->where('violation_action', $action);
            }
        )
        ->when(
            $state > -1, //Status contain zero value for "NEW" that's why
            //we have campared with -1.To Do: We will find any proper solution.
            function ($query) use ($state, $col) {
                $query->where($col, $state);
            },
            function ($query) use ($statusVal, $col) {
                $query->whereNotIn($col, $statusVal);
            }
        )
        ->when(
            !empty($searchText),
            function ($query) use ($searchText) {
                $query->where(
                    function ($query) use ($searchText) {
                        $query->whereRaw("barcode_id in (select `barcode_id` from `units` where `unit_number` LIKE '%$searchText%')")
                            ->orWhereRaw("building_id in (select `id` from `buildings` where `building_name` LIKE '%$searchText%')")
                            ->orWhereRaw("special_note in (select `special_note` from `violations` where `special_note` LIKE '%$searchText%' and `deleted_at` is null)")
                            ->orWhereRaw("property_id in (select `id` from `properties` where `name` LIKE '%$searchText%')");
                    }
                );
            }
        )
        ->whereIn('property_id', $propertyList->pluck('id'))
        ->withTrashed()
        ->get();
        //Get total record count: End

        //Get record according to limit: Start
        $vio = \App\Violation::whereBetween(
            \DB::raw("convert_tz(created_at, 'UTC','" . getUserTimezone() . "')"),
            [
                $startTime,
                $endTime,
            ]
        )
        //Remove when API version 8 live: Start
        ->when(
            !$this->user->hasRole('admin') && !$this->user->hasRole('property_manager'),
            function ($query) {
                $query->where('user_id', $this->user->id);
            }
        )
        ->when(
            $this->user->hasRole('admin'),
            function ($query) {
                $query->whereIn(
                    'user_id',
                    function ($query) {
                        $query->from('users')
                            ->select('id')
                            ->where(
                                [
                                    'subscriber_id' => $this->user->subscriber_id,
                                ]
                            );
                    }
                );
            }
        )
        ->when(
            !empty($username),
            function ($query) use ($username) {
                $query->where('user_id', $username);
            }
        )
        ->when(
            !empty($reason),
            function ($query) use ($reason) {
                $query->where('violation_reason', $reason);
            }
        )
        ->when(
            !empty($action),
            function ($query) use ($action) {
                $query->where('violation_action', $action);
            }
        )
        ->when(
            $state > -1, //Status contain zero value for "NEW" that's why
            //we have campared with -1.To Do: We will find any proper solution.
            function ($query) use ($state, $col) {
                $query->where($col, $state);
            },
            function ($query) use ($statusVal, $col) {
                $query->whereNotIn($col, $statusVal);
            }
        )
        ->when(
            !empty($searchText),
            function ($query) use ($searchText) {
                $query->where(
                    function ($query) use ($searchText) {
                        $query->whereRaw("barcode_id in (select `barcode_id` from `units` where `unit_number` LIKE '%$searchText%')")
                            ->orWhereRaw("building_id in (select `id` from `buildings` where `building_name` LIKE '%$searchText%')")
                            ->orWhereRaw("special_note in (select `special_note` from `violations` where `special_note` LIKE '%$searchText%' and `deleted_at` is null)")
                            ->orWhereRaw("property_id in (select `id` from `properties` where `name` LIKE '%$searchText%')");
                    }
                );
            }
        )
        ->whereIn('property_id', $propertyList->pluck('id'))
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
                    $query->select('id', 'unit_number', 'barcode_id', 'property_id', 'building_id','is_route')
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
        //Get record according to limit: Start
        
        foreach ($vio as $vios) {
            $property = '-';
            // Mantain two diffrent status first one for admin
            //and second one for property manager (Task: 640 comment: #17)): Start
            $vioStatus = $this->user->hasRole('property_manager')
                ? $vios->manager_status : $vios->status;
            // Mantain two diffrent status first one for admin
            // and second one for property manager (Task: 640 comment: #17)): End

            $url = url('violationdetails/' . $vios->id);
            $violId = $vios['id'];
            $vioUnit = $vios->getUnitNumber->id;
            
            $resiEmail = \App\Resident::select('email')
                            ->where('unit_id',$vioUnit)
                            ->first();

            $checkBox = "<a href='javascript:void(0);'><input type='checkbox' class='flat datatable-checkbox' style='cursor: pointer' name='table_records' value='$violId' disabled></a>";

            $icon = "<a href='javascript:void(0);' data-remote='" . $url . "' data-toggle='modal' data-id='$violId' data-status='1' title='View' data-target='#violationDetails'><li class='fa fa-eye'></li></a> ";

            $icon .= "| <a href='javascript:void(0);' data-unitid='$vioUnit' data-id='$violId' title='Send Mail' class='send-mail'><li class='fa fa-mail-forward'></li></a> ";

            $icon .= "| <a href='javascript:void(0);' data-id='$violId' data-status='1' title='Print Violation' class='print-view'><li class='fa fa-print'></li></a>";


            //Identify the notes type:Start
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
            //Identify the notes type:End

            if (isset($vios->getUser->name)) {
                $name = ucwords($vios->getUser->name);
            }

            if (isset($vios->getReason->reason)) {
                if (!$this->user->hasRole(['property_manager'])) {
                    $url = url('violation/get-violation-for-update');
                    $rule = "<a href='#' class='reason-change' data-type='select' data-name='reason' data-pk='" . $vios->id . "' data-url='" . $url . "' data-title='Select Rule'>" . ucwords($vios->getReason->reason) . '</a>';
                } else {
                    $rule = ucwords($vios->getReason->reason);
                }
            }

            if (isset($type)) {
                $url = url('violation/get-violation-for-update');
                $status = "<a href='#' class='statu-chane' data-type='select' data-name='status' data-pk='" . $vios->id . "' data-url='" . $url . "' data-title='Select Status'>" . $type . '</a>';
            }

            if (isset($vios->getAction->action)) {
                if (!$this->user->hasRole(['property_manager'])) {
                    $url = url('violation/get-violation-for-update');
                    $action = "<a href='#' class='action-change' data-type='select' data-pk='" . $vios->id . "' data-url='" . $url . "' data-title='Select Action'>" . ucwords($vios->getAction->action) . '</a>';
                } else {
                    $action = ucwords($vios->getAction->action);
                }
            }

            $uni = \App\Units::where('barcode_id', $vios->barcode_id)
                    ->withTrashed()->first();

            $property = isset($uni->getPropertyDetail->name) ? $uni->getPropertyDetail->name : '';


            if ($vios->images_count) {
                $url = url('/violationimages/' . $vios->id);

                $detail = '<b>No.of Image: </b>' . "<a href='javascript:void(0);' data-type='images' data-toggle='modal' data-remote='" . $url . "' data-target='#myModal'>$vios->images_count</a>";
                $imagesCount = $vios->images_count;
            } else {
                $url = url('/violationimages/' . $vios->id);
                $detail = '<b>No.of Image: </b> 0';
                $imagesCount = 0;
            }

            if (empty($vios->type) && isset($vios->getUnitNumber->unit_number)) {
                $detail .= '<br/><b>Unit Number: </b>' . $vios->getUnitNumber->unit_number;
                $unitNumber = $vios->getUnitNumber->unit_number;
                $disableClass = !empty($resiEmail['email']) ? ' ' : 'resemailDisable';
                $titleresi = !empty($resiEmail['email']) ? 'Send Mail to Resident' : 'No Resident Assign';
                $residata = !empty($resiEmail['email']) ? $resiEmail['email'] : '';
                $icon .= "| <a href='javascript:void(0);' data-email='$residata' data-unitid='$vioUnit' title='$titleresi' id='resident_mail_violation' class='resident_mail_violation $disableClass'><li class='fa fa-mail-forward'></li></a> ";
            }

            if (!empty($vios->type) && isset($vios->getUnitNumber->unit_number)) {
                $detail .= '<br/><b>Route Checkpoint: </b>' . $vios->getUnitNumber->unit_number;
            }

            if (isset($vios->created_at)) {
                $createdAt = \Carbon\Carbon::parse($vios->created_at)
                ->timezone(getUserTimezone())->format('m-d-Y h:i A');
            }

            if (isset($vios->special_note)) {
                $specail = ucwords($vios->special_note);
            } else {
                $specail = "";
            }

            if (isset($vios->getBuilding->building_name)) {
                $building = ucwords($vios->getBuilding->building_name);
            } else {
                $building = "";
            }

            $violationArray[] = [
                'plusIcon' => '',
                'id' => $i++,
                'name' => $name,
                'property' => $property,
                'rule' => $rule,
                'action' => $action,
                //'image' => $image,
                'created_at' => $createdAt,
                'specail' => $specail,
                //'unit' => $unit,
                'status' => $status,
                'checkBox' => $checkBox,
                'icon' => $icon,
                'detail' => $detail,
                'building' => $building,
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

    public function getSpacialNoteUpdate(Request $request)
    {
        $pk = $request->pk;
        $name = $request->name;
        $value = $request->value;
           
        $spacialNotes = \App\Violation::where('id', $pk)
            ->update(
                [
                    'special_note' => $value,
                ]
            );
            
        return response()
            ->json(
                [
                    'message' => 'Violation spacial notes update successfully.'
                ]
            );
    }

    public function getViolationForUpdate(Request $request)
    {
        if ($request->name == 'reason') {
            $data = [
                'violation_reason' => $request->value,
            ];
        } elseif ($request->name == 'status') {
            if ($this->user->hasRole('property_manager')) {
                $data = ['manager_status' => $request->value];
            } else {
                $data['status'] = $request->value;

                if ($request->value == 2) {
                    //"NEW" status for property manager.
                    $data['manager_status'] = 0;
                } elseif ($request->value == 0) {
                    //Hide for the property manager.
                    $data['manager_status'] = 11;
                }
            }
        } else {
            $data = [
                'violation_action' => $request->value,
            ];
        }

        $status = \App\Violation::where('id', $request->pk)->update($data);

        if ($status) {
            echo '<div class="alert alert-success">'
            . '<strong>Violation update successfully.</strong></div>';
        } else {
            echo '<div class="alert alert-danger">'
            . '<strong>Some thing went try after sometime.</strong></div>';
        }
    }

    public function changeViolationStatus(Request $request)
    {
        $data = [];

        foreach ($request->id as $id) {
            //check is rollback violation:Start.
            if (isNotRollbackViolation($id)) {
                $data[] = $id;
            }
        }

        if (!empty($data)) {
            if ($this->user->hasRole('property_manager')) {
                $status = ['manager_status' => $request->value];
            } else {
                $status['status'] = $request->value;

                if ($request->value == 2) {
                    //"NEW" status for property manager.
                    $status['manager_status'] = 0;
                } elseif ($request->value == 0) {
                    //Hide for the property manager.
                    $status['manager_status'] = 11;
                }
            }

            \App\Violation::whereIn('id', $data)->update($status);

            $status = true;
            $message = '';
        } else {
            $status = false;
            $message = 'This violation is rolled back by the user.';
        }

        return response()->json(
            [
            'status' => $status,
            'message' => $message,
            ]
        );
    }

    public function manageViolationAction()
    {
        //Permission: only admin can access:Start
        if (!$this->user->hasRole(['admin'])) {
            return redirect('unauthorized');
        }
        //Permission: only admin can access:End

        $action = \App\Action::query()
            ->where('company_id', $this->user->subscriber_id)
            ->orWhere('type', 0)
            ->whereDoesntHave(
                'removeAction',
                function ($query) {
                    $query->where('subscriber_id', $this->user->subscriber_id);
                }
            )
            ->paginate(50);
        
        $this->data['action'] = $action;

        return view('action.index', $this->data);
    }

    public function violationActionCreate()
    {
        //Permission: only admin can access:Start
        if (!$this->user->hasRole(['admin'])) {
            return redirect('unauthorized');
        }
        //Permission: only admin can access:End

        return view('action.create', $this->data);
    }

    public function actionStore(Request $request)
    {
        //Permission: only admin can access:Start
        if (!$this->user->hasRole(['admin'])) {
            return redirect('unauthorized');
        }
        //Permission: only admin can access:End

        $this->validate(
            $request,
            [
            'action' => 'required',
            ]
        );

        $status = \App\Action::create(
            [
                'action' => $request->action,
                'company_id' => $this->user->subscriber_id,
                'type' => 1,
            ]
        );

        $class = ($status) ? 'success' : 'error';
        $message = ($status) ? 'Violation Action created successfully.'
                    : 'Violation Action creation failed.';
        $data = [
            'title' => 'Violation Action',
            'text' => $message,
            'class' => $class,
        ];

        return redirect('manage-violation-action')->with('status', $data);
    }

    public function actionEdit($id)
    {
        //Permission: only admin can access:Start
        if (!$this->user->hasRole(['admin'])) {
            return redirect('unauthorized');
        }
        //Permission: only admin can access:End

        $reason = \App\Action::where('id', $id)
                ->where('company_id', $this->user->subscriber_id)
                ->get();

        if ($reason->isNotEmpty()) {
            $this->data['action'] = $reason;

            return view('action.edit', $this->data);
        } else {
            return redirect('manage-violation-action');
        }
    }

    public function actionUpdate(Request $request)
    {
        //Permission: only admin can access:Start
        if (!$this->user->hasRole(['admin'])) {
            return redirect('unauthorized');
        }
        //Permission: only admin can access:End

        $this->validate(
            $request,
            [
            'action' => 'required',
            ]
        );

        $role = \App\Action::find($request->id);
        $role->action = $request->action;
        $status = $role->save();

        $class = ($status) ? 'success' : 'error';
        $message = ($status) ? 'Violation Action Update successfully.'
                    : 'Violation Action Update failed.';
        $data = [
            'title' => 'Violation Action',
            'text' => $message,
            'class' => $class,
        ];

        return redirect('manage-violation-action')->with('status', $data);
    }

    public function actionDestroy($id)
    {
        //Permission: only admin can access:Start
        if (!$this->user->hasRole(['admin'])) {
            return redirect('unauthorized');
        }
        //Permission: only admin can access:End

        $checkExisting = \App\Violation::query()
            ->where('violation_action', $id)
            ->whereIn(
                'user_id',
                function ($query) {
                    $query->select('id')
                        ->from('users')
                        ->where('subscriber_id', $this->user->subscriber_id);
                }
            )
            ->get();
                
        if ($checkExisting->isNotEmpty()) {
            $class = 'error';
            $message = 'This action can not be deleted';
            $message .= ' because violation is already';
            $message .= ' created for this action.';

            $data = [
                'title' => 'Action',
                'text' => $message,
                'class' => $class,
            ];

            return redirect('manage-violation-action')->with('status', $data);
        } else {
            $status = \App\Action::find($id);
            
            if ($status->type) {
                $status->destroy($id);
            } else {
                \App\RemoveAction::create(
                    [
                        'action_id' => $status->id,
                        'subscriber_id' => $this->user->subscriber_id,
                        'user_id' => $this->user->id,
                    ]
                );
            }
            
            $class = ($status) ? 'success' : 'error';
            $message = ($status) ? 'Violation Action delete successfully.'
                            : 'Violation Action delete failed.';
            $data = [
                'title' => 'Violation Action',
                'text' => $message,
                'class' => $class,
            ];

            return redirect('manage-violation-action')
                        ->with('status', $data);
        }
    }

    public function getManagerEmailForViolation(Request $request)
    {
        $userDetail = collect();
        $propertyId = 0;

        if (!empty($request->propertyId)) {
            $propertyId = $request->propertyId;
        } else {
            $violation = \App\Violation::whereIn('id', $request->violtionId)
                ->get();

            if ($violation->isNotEmpty()) {
                // if (empty($violation[0]->type)) {
                //     $unit = \App\Units::where('barcode_id', $violation[0]->barcode_id)
                //         ->first();
                // } else {
                //     $unit = \App\RouteCheckIn::where('barcode_id', $violation[0]->barcode_id)
                //         ->first();
                // }

                $unit = \App\Units::where('barcode_id', $violation[0]->barcode_id)
                    ->first();
                
                $propertyId = $unit->property_id;
            }
        }

        if (isset($propertyId) && !empty($propertyId)) {
            $propertyManager = \App\UserProperties::where(
                [
                    'property_id' => $propertyId,
                    'type' => 2,
                ]
            )
            ->get();
        
            $userId = $propertyManager->pluck('user_id')->toArray();
            
            $userDetail = \App\User::select('email')
                ->whereIn('id', $userId)->get();
                    
            $userEmail = $userDetail->implode('email', ',');

            $residentEmail = \App\Resident::select('email')
                            ->where('unit_id',$request->unitdata)
                            ->get();
            
            $unitEmail = $residentEmail->implode('email',',');
        }

        return response()
            ->json(
                [
                'result' => $userEmail,
                'unit' => $unitEmail,
                ]
            );
    }

    public function violationSendMail(Request $request)
    {
        $error = $toEmail = $pdfName = $pdfUrl = '';
        $url = url('/violation/'); //For multiple violation.

        $validator = Validator::make(
            $request->all(),
            [
                    'toEmail' => 'required',
                    'body' => 'required',
                    'ccEmail' => 'nullable',
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $errors) {
                $error .= '<div class="alert alert-danger"><strong>'
                        . $errors . '</strong></div>';
            }

            return $error;
        }

        $violation = collect(explode(',', $request->violationId));
        $toEmail = collect(explode(',', $request->toEmail));

        // if (isset($violation) && count($violation) == 1) {
        //     //For single violation:Start.
        //     $encrypted = \Hashids::encode($violation[0]);
        //     $subcriberId = \Hashids::encode($this->user->subscriber_id);
        //     $url = url('/violation-detail-by-link/' . $encrypted . '/' . $subcriberId);
        // } else {
            //For multiple violation:Start.
            $pdfName = $this->createVolationPdf($violation);
            //$pdfUrl = public_path() . '/uploads/pdf/' . $pdfName;
            $url = url('/uploads/pdf/' . $pdfName);
        //}
        //dd($url);
        $cc = [];

        $user = new User();
        $user->email = $toEmail;
        $user->url = $url;

        if (!empty($request->ccEmail)) {
            $cc = explode(',', $request->ccEmail);
        }

        if ($request->isCheck == 'true') {
            $cc[] = $this->user->email;
        }

        $user->cc = $cc;

        $data = [$request->subject, $request->body, $pdfUrl];

        try {
            $status = $user->notify(new \App\Notifications\ViolationMail($data));
        } catch (\Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }

        return response()->json(['result' => true]);
    }

    protected function createVolationPdf($violationIds)
    {
        $pdfName = time() . '.pdf';
        $voilationDetails = [];
        $col = $this->user->hasRole('property_manager') ? 'manager_status' : 'status';

        $violations = \App\Violation::whereIn('id', $violationIds)
            //->whereNotIn('status', [1]) //Remove rollback violation
            //->latest()
            ->withTrashed()
            ->get();

        if (isset($violations) && $violations->isNotEmpty()) {
            foreach ($violations as $violation) {
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

                if (!empty($property->image) && Storage::disk('s3')->exists('uploads/property/' . $property->image_name)) {
                    $logo = url('/uploads/property') . '/' . $property->image;
                } else {
                    $logo = logoPublicPath()['logo'];
                }

                $voilationDetails[] = [
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
                    'building_address' => !empty($building->address) ? ucwords($building->address) : 'Not Mention',
                    'building' => !empty($building->building_name) ? ucwords($building->building_name) : 'Not Mention',
                    'created_at' => $violation->created_at,
                    'special_note' => $violation->special_note,
                    'logo' => $logo,
                    'comment' => !empty($violation->comment) ? $violation->comment : '',
                    'reminder' => !empty($property->reminder) ? $property->reminder : ''
                ];
            }
        }

        if (!empty($voilationDetails)) {
            $this->data['violations'] = $voilationDetails;
            $this->data['logo'] = $logo;
            $pdf = PDF::loadView('violation.violationpdf', $this->data);
            $pdf->save(public_path() . '/uploads/pdf/' . $pdfName);

            //return $pdfName;
            return $pdfName;
        } else {
            return false;
        }
    }

    public function downloadViolationPdf(Request $violation)
    {
        $id = explode(',', $violation->id);
        $pdfName = $this->createVolationPdf($id);
       
        if (!empty($pdfName)) {
            return response()
                ->json(
                    [
                        'result' => true,
                        'data' => $pdfName,
                    ]
                );
        } else {
            return response()
                ->json(
                    [
                        'result' => false,
                    ]
                );
        }
    }

    public function manageTemplate()
    {
        //Permission: only admin can access this panel:Start
        if ($this->user->hasRole('admin') || $this->user->hasRole('property_manager')) {
            // $templates = \App\TemplateContent::where(
            //     [
            //         'subscriber_id' => $this->user->subscriber_id,
            //     ]
            // )->latest()->paginate(50);
            $templates = \App\TemplateContent::query()
                ->when(
                    $this->user->hasRole('admin'),
                    function($query){
                        $query->where('subscriber_id', $this->user->subscriber_id)->where('user_id','=','!0');
                    },
                    function($query){
                        $query->where('user_id', $this->user->id);
                    }
                )
            ->latest()
            ->paginate(50); 
                    
            $offset = paginateOffset($templates->currentPage(), 50);
    
            $this->data['templates'] = $templates;
            $this->data['offset'] = $offset;
    
            return view('violation.templatelist', $this->data);
           
        }
        //Permission: only admin can access this panel:End
         return redirect('unauthorized');
        
    }

    public function setTemplateStatus($id)
    {
        //Permission: only admin can access this panel:Start
        if ($this->user->hasRole('admin') || $this->user->hasRole('property_manager')) {
            $templates = \App\TemplateContent::query()
                    ->when(
                        $this->user->hasRole('admin'),
                        function($query){
                            $query->where('subscriber_id', $this->user->subscriber_id);
                        },
                        function($query){
                            $query->where('user_id', $this->user->id);
                        }
                        )
                    ->update(['status' => 0]);
    
            $template = \App\TemplateContent::where(['id' => $id])
                    ->update(['status' => 1]);
    
            $class = ($template) ? 'success' : 'error';
            $message = ($template) ? 'Set default template successfully.'
                        : 'Some error occur please try after sometime.';
    
            $data = [
                'title' => 'Manage Template',
                'text' => $message,
                'class' => $class,
            ];
    
            return redirect('violation-templates')
                    ->with('status', $data);
        }
                //Permission: only admin can access this panel:End
                // \App\TemplateContent::where(
                    //     [
                        //         'subscriber_id' => $this->user->subscriber_id,
                        //     ]
                        // )
            return redirect('unauthorized');
    }

    public function deleteTemplate($id)
    {
        //Permission: only admin can access this panel:Start
        if ($this->user->hasRole('admin') || $this->user->hasRole('property_manager')) {
            $template = \App\TemplateContent::where(
                [
                    'id' => $id,
                ]
            )->delete();
    
            $class = ($template) ? 'success' : 'error';
            $message = ($template) ? 'Template delete  successfully.'
                        : 'Some error occur please try after sometime.';
    
            $data = [
                'title' => 'Delete Template',
                'text' => $message,
                'class' => $class,
            ];
    
            return redirect('violation-templates')
                    ->with('status', $data);
        }
        //Permission: only admin can access this panel:End
        return redirect('unauthorized');

    }

    public function addTemplate(Request $request)
    {
        //Permission: only admin can access this panel:Start
        if ($this->user->hasRole('admin') || $this->user->hasRole('property_manager')) {
        // if (!$this->user->hasRole('admin')) {
        //     return redirect('unauthorized');
        // }
        //Permission: only admin can access this panel:End

        // $template = \App\TemplateContent::create($request->all());
        // dd($request->all());
         $template = \App\TemplateContent::insert(
                [
                    'template_id' => $request->template_id,
                    'subscriber_id' => $request->subscriber_id,
                    'content' => $request->content,
                    'subject' => $request->subject,
                    'name' => $request->name,
                    'user_id' => $this->user->id,
                    'is_user' => $this->user->hasRole('admin') ? '1' : '2',
                ]
                );

        $class = ($template) ? 'success' : 'error';
        $message = ($template) ? 'Template added successfully.'
                    : 'Some error occur please try after sometime.';

        $data = [
            'title' => 'Add Template',
            'text' => $message,
            'class' => $class,
        ];

        return redirect('violation-templates')
            ->with('status', $data);
        }
        return redirect('unauthorized');
    }

    public function getTemplateDetail($id)
    {
        //Permission: only admin can access this panel:Start
        if ($this->user->hasRole('admin') || $this->user->hasRole('property_manager')) {
            $template = \App\TemplateContent::select('content', 'subject', 'id', 'name')
                   ->find($id);
    
            return response()
                ->json(
                    [
                        'detail' => $template,
                        'result' => true,
                    ]
                );
        }
        //Permission: only admin can access this panel:End

        return redirect('unauthorized');
    }

    public function updateTemplateDetail(Request $request, $id)
    {
        //Permission: only admin can access this panel:Start
        if ($this->user->hasRole('admin') || $this->user->hasRole('property_manager')) {
            $template = \App\TemplateContent::where('id', $id)
                ->update(
                    [
                      'subject' => $request->subject,
                      'content' => $request->content,
                      'name' => $request->name,
                    ]
                );
    
            $class = ($template) ? 'success' : 'error';
            $message = ($template) ? 'Template updated successfully.'
                        : 'Some error occur please try after sometime.';
    
            $data = [
                'title' => 'Edit Template',
                'text' => $message,
                'class' => $class,
            ];
    
            return redirect('violation-templates')
                    ->with('status', $data);
        }
            
            return redirect('unauthorized');
        //Permission: only admin can access this panel:End
    }

    public function updateComment(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'comments' => 'required',
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $errors) {
                $error .= $errors;
            }
            
            return response()->json(
                [
                    'error' => $error,
                    'status' => false
                ]
            );
        }

        $vio = \App\Violation::find($request->id);
        $vio->comment = $request->comments;
        $query = $vio->save();

        return response()->json(['status' => true]);
    }

    public function residentEmailViolation(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                    'body' => 'required',
                    'ccEmail' => 'nullable',
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $errors) {
                $error .= '<div class="alert alert-danger"><strong>'
                        . $errors . '</strong></div>';
            }

            return $error;
        }

        $data = [
            'subject' => $request->subject, 
            'body' => $request->body,
            'logo' => getLogo()['logo'],
            'companyName' => getLogo()['companyName'],
        ];

        $toEmail = explode(',', $request->email);
        //dd($toEmail);
        $cc = [];
        if (!empty($request->cc)) {
            $cc = explode(',', $request->cc);
        }

        // $toemails = [];
        // if (!empty($request->email)) {
        //     $toemails = explode(',', $request->email);
        // }

        try {
            Mail::to($toEmail)->cc($cc)->send(new ViolationResidentSendEmail($data));
        }catch (\Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
        return response()->json(['result' => true]);
    }

    public function templateOnChange(Request $request)
    {
        $getSubject = \App\TemplateContent::select('subject','content')
                    ->where('id', $request->id)
                    ->get();
            
            foreach($getSubject as $subjectvalue){
                $subjectvalue;
            }
            
        return response()
                    ->json(
                        [
                            'subject' => $subjectvalue->subject,
                            'content' => $subjectvalue->content,
                        ]
                        );
    }

    public function residentTemplates(Request $request)
    {
        $unit = \App\Units::where('id',$request->id)
                    ->first();

        $propertyId = $unit->property_id;

        $getvioSub = \App\TemplateContent::select('id','name')
                    ->whereIn(
                        'user_id',
                        function ($query) use ($propertyId) {
                            $query->select('user_id')
                            ->from('user_properties')
                            ->where('property_id', $propertyId)
                            ->where('type', 2);
                        }
                    )
                    ->orWhere('user_id', $this->user->id)
                    ->get();
       
        return response()
                    ->json(
                            [ 
                                'data' => $getvioSub,
                            ]
                        );
    }

}
