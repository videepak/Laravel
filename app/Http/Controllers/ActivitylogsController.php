<?php

namespace App\Http\Controllers;

use App\Activitylogs;
use App\Subscriber;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivitylogsController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('RoleAndPermission:employees,properties');
    }

    public function index()
    {
        $userDetails = $this->user = Auth::user();
        $this->data['user_details'] = $userDetails;
        $activity = $this->user->logs()->paginate(50);
        $this->data['activity'] = $activity;

        return view('activitylogs.index', $this->data);
    }

    // public function viewlogs($id)
    // {

    //     $userDetails = User::find($id);

    //     $this->data['activitylogs'] = \App\Activitylogs::where('type', 2)
    //         ->where(
    //             function ($query) use ($id) {
    //                             $query->where('user_id', $id)
    //                                 ->orWhere('updated_by', $id);
    //                         }
    //                     )
    //                     ->with('getUserDetail')->latest()->paginate(50);

    //     $this->data['user_details'] = $userDetails;
    //     return view('employee.empactivitylogs', $this->data);
    // }

    public function propertyManagerLog(Request $request)
    {
        if (!empty($request->property)) {
            $this->data['urlId'] = $request->property;
        } else {
            $this->data['urlId'] = $request->user;
        }

        $employee = \App\User::query()
            ->select('id', \DB::raw("CONCAT_WS(' ', `title`, `firstname`, `lastname`) as name"))
            ->whereIn(
                'id',
                function ($query) use ($request) {
                    $query->select('user_id')
                        ->from('user_properties')
                        ->when(
                            !empty($request->property),
                            function ($query) use ($request) {
                                $query->where('property_id', $request->property);
                            },
                            function ($query) {
                                $query->whereIn('property_id', $this->propertyList()->pluck('id'));
                            }
                        )
                        ->where('type', 1)
                        ->whereNull('deleted_at');
                }
            )
            //->where('subscriber_id', $this->user->subscriber_id)
            //->whereNotIn('role_id', [10])
            ->orderBy('title')
            //->withTrashed()
            ->get();

        $this->data['empolyee'] = $employee;

        return view('employee.empactivitylogs', $this->data);
    }

    public function logPropertyManager(Request $request)
    {
        $propertyOrUserId = $request->id;
        $i = $request->start + 1;
        $search = $request->search['value'];
        $usename = $request->username;
        $property = $username = $email = $text = $info = '';
        $activityArray = [];
        $startTime = \Carbon\Carbon::parse($request->startTime, getUserTimezone())->addHours(6)->copy();
        $endTime = \Carbon\Carbon::parse($request->endTime, getUserTimezone())
        ->addDays(1)->addHours(5)->addMinutes(59)->addSeconds(59)->copy();
        //$type = !$this->user->hasRole('property_manager') ? [2] : [1, 2, 3, 5, 6];
        $type = !$this->user->hasRole('property_manager') ? [2] : [2, 3, 4, 7, 8, 9, 10, 11, 12, 13];
        
        //Get total result: Start
        $activi = \App\Activitylogs::query()
            ->whereBetween(
                \DB::raw("convert_tz(created_at, 'UTC','" . getUserTimezone() . "')"),
                [
                    $startTime,
                    $endTime,
                ]
            )
            ->whereIn('type', $type)
            ->when(
                !$this->user->hasRole('property_manager'),
                function ($query) use ($propertyOrUserId) {
                    $query->where('user_id', $propertyOrUserId);
                },
                function ($query) use ($propertyOrUserId) {
                    $query->where('property_id', $propertyOrUserId);
                }
                // function ($query) use ($propertyOrUserId) {
                //     $query->whereIn(
                //         'barcode_id',
                //         function ($query) use ($propertyOrUserId) {
                //             $query->select('barcode_id')
                //                 ->from('units')
                //                 ->where('property_id', $propertyOrUserId)
                //                 ->whereNull('deleted_at')
                //                 ->where('is_active', 1);
                //         }
                //     );
                // }
            )
            ->when(
                !empty($usename),
                function ($query) use ($usename) {
                    $query->where('user_id', $usename);
                }
            )
        ->get();
        //Get total result: End
        
        //Get result with limit:Start
        $activities = \App\Activitylogs::query()
            ->whereBetween(
                \DB::raw("convert_tz(created_at, 'UTC','" . getUserTimezone() . "')"),
                [
                    $startTime,
                    $endTime,
                ]
            )
            ->whereIn('type', $type)
            ->when(
                !$this->user->hasRole('property_manager'),
                function ($query) use ($propertyOrUserId) {
                    $query->where('user_id', $propertyOrUserId);
                },
                function ($query) use ($propertyOrUserId) {
                    $query->where('property_id', $propertyOrUserId);
                }
                // function ($query) use ($propertyOrUserId) {
                //     $query->whereIn(
                //         'barcode_id',
                //         function ($query) use ($propertyOrUserId) {
                //                 $query->select('barcode_id')
                //                 ->from('units')
                //                 ->where('property_id', $propertyOrUserId)
                //                 ->whereNull('deleted_at')
                //                 ->where('is_active', 1);
                //         }
                //     );
                // }
            )
            ->when(
                !empty($usename),
                function ($query) use ($usename) {
                    $query->where('user_id', $usename);
                }
            )
            ->with(
                [
                    'getUserDetail' => function ($query) {
                        $query->select('id', 'mobile', 'email', 'subscriber_id', \DB::raw("CONCAT_WS(' ', `title`, `firstname`, `lastname`) as name"))
                            ->withTrashed();
                    },
                    'getPropertyDetailByPropertyId' => function ($query) {
                        $query->select('id', 'name', 'address_type', 'same_address', 'main_address', 'address', 'city', 'state', 'zip', 'subscriber_id', 'user_id', 'customer_id', 'type')
                            ->withTrashed();
                    },
                    'unit' => function ($query) {
                        $query->select('id', 'address1', 'unit_number', 'property_id', 'building_id', 'barcode_id');
                    },
                ]
            )
            ->latest()
            ->limit($request->length)->offset($request->start)
        ->get();
        //Get result with limit:End
        
        foreach ($activities as $activity) {
            if ($activity->getPropertyDetailByPropertyId->type == 1) {
                $propertyType = "Curbside Community";
            } elseif ($activity->getPropertyDetailByPropertyId->type == 2) {
                $propertyType = "Garden Style Apartment";
            } elseif ($activity->getPropertyDetailByPropertyId->type == 3) {
                $propertyType = "High Rise Apartment";
            } elseif ($activity->getPropertyDetailByPropertyId->type == 4) {
                $propertyType = "Townhome";
            }

            $property = '<b>Property Name:</b> ' . ucwords($activity->getPropertyDetailByPropertyId->name) . '<br/>';

            $property .= '<b>Property Address:</b> ' . ucwords($activity->getPropertyDetailByPropertyId->address);

            if (!empty($activity->getPropertyDetailByPropertyId->city)) {
                $property .= ucwords($activity->getPropertyDetailByPropertyId->city) . ' ';
            }

            if (!empty($activity->getPropertyDetailByPropertyId->getState->name)) {
                $property .= ucwords($activity->getPropertyDetailByPropertyId->getState->name) . ' ';
            }

            if (!empty($activity->getPropertyDetailByPropertyId->zip)) {
                $property .= ucwords($activity->getPropertyDetailByPropertyId->zip) . ' ';
            }

            if (!empty($activity->getPropertyDetailByPropertyId->zip)) {
                $property .= ucwords($activity->getPropertyDetailByPropertyId->zip) . ' ';
            }

            if (!empty($activity->unit->getBuildingDetail->building_name)) {
                ($activity->unit->getBuildingDetail->building_name);
            }

            if (!empty($activity->unit->getBuildingDetail->address)) {
                $property .= '<br/><b>Building Address</b> ' . ucwords($activity->unit->getBuildingDetail->address);
            }

            if (!empty($activity->unit->unit_number)) {
                $property .= '<br/><b>Unit:</b> ' . ucwords($activity->unit->unit_number);
            }

            if (!empty($propertyType)) {
                $property .= '<br/><b>Property Type:</b> ' . ucwords($propertyType);
            }

            if (!empty($activity->getUserDetail->name)) {
                $username = ucwords($activity->getUserDetail->name);
            }

            if ($activity->getUserDetail->email) {
                $email = $activity->getUserDetail->email;
            }

            if ($activity->text) {
                $text = ucwords($activity->text);
            }

            $info = '<b>Created At:</b> ' . \Carbon\Carbon::parse($activity->created_at)->timezone(getUserTimezone())->format('m-d-Y h:i A') . '<br/>';

            $info .= '<b>Updated At:</b> ' . \Carbon\Carbon::parse($activity->updated_at)->timezone(getUserTimezone())->format('m-d-Y h:i A') . '<br/>';

            $activityArray[] = [
                'id' => $i++,
                'property' => $property,
                'username' => $username,
                'email' => $email,
                'text' => $text,
                'info' => $info,
            ];
        }

        return json_encode(
            [
                'draw' => intval($request->draw),
                'recordsTotal' => !empty($activi) ? $activi->count() : 0,
                'recordsFiltered' => !empty($activi) ? $activi->count() : 0,
                'data' => $activityArray,
            ]
        );
    }

    public function logPropertyManagerBackup(Request $request)
    {
        $propertyOrUserId = $request->id;
        $i = $request->start + 1;
        $search = $request->search['value'];
        $usename = $request->username;
        $property = $username = $email = $text = $info = '';
        $activityArray = [];
        $startTime = \Carbon\Carbon::parse($request->startTime, getUserTimezone())->addHours(6)->copy();
        $endTime = \Carbon\Carbon::parse($request->endTime, getUserTimezone())
        ->addDays(1)->addHours(5)->addMinutes(59)->addSeconds(59)->copy();
        //$type = !$this->user->hasRole('property_manager') ? [2] : [1, 2, 3, 5, 6];
        $type = !$this->user->hasRole('property_manager') ? [2] : [2, 3, 4, 7, 8, 9, 10, 11, 12, 13];
        
        //Get total result: Start
        $activi = \App\Activitylogs::whereBetween(
            \DB::raw("convert_tz(created_at, 'UTC','" . getUserTimezone() . "')"),
            [
                $startTime,
                $endTime,
            ]
        )
        ->whereIn('type', $type)
        ->when(
            !$this->user->hasRole('property_manager'),
            function ($query) use ($propertyOrUserId) {
                $query->where('user_id', $propertyOrUserId);
            },
            function ($query) use ($propertyOrUserId) {
                $query->whereIn(
                    'barcode_id',
                    function ($query) use ($propertyOrUserId) {
                        $query->select('barcode_id')
                            ->from('units')
                            ->where('property_id', $propertyOrUserId)
                            ->whereNull('deleted_at')
                            ->where('is_active', 1);
                    }
                );
            }
        )
        ->when(
            !empty($usename),
            function ($query) use ($usename) {
                $query->where('user_id', $usename);
            }
        )
        ->get();
        //Get total result: End

        //Get result with limit:Start
        $activities = \App\Activitylogs::whereBetween(
            \DB::raw("convert_tz(created_at, 'UTC','" . getUserTimezone() . "')"),
            [
                $startTime,
                $endTime,
            ]
        )
        ->whereIn('type', $type)
        ->when(
            !$this->user->hasRole('property_manager'),
            function ($query) use ($propertyOrUserId) {
                $query->where('user_id', $propertyOrUserId);
            },
            function ($query) use ($propertyOrUserId) {
                $query->whereIn(
                    'barcode_id',
                    function ($query) use ($propertyOrUserId) {
                            $query->select('barcode_id')
                            ->from('units')
                            ->where('property_id', $propertyOrUserId)
                            ->whereNull('deleted_at')
                            ->where('is_active', 1);
                    }
                );
            }
        )
        ->when(
            !empty($usename),
            function ($query) use ($usename) {
                $query->where('user_id', $usename);
            }
        )
        ->with(
            [
                'getUserDetail' => function ($query) {
                    $query->select('id', 'mobile', 'email', 'subscriber_id', \DB::raw("CONCAT_WS(' ', `title`, `firstname`, `lastname`) as name"))
                        ->withTrashed();
                },
                'getPropertyDetailByPropertyId' => function ($query) {
                    $query->select('id', 'name', 'address_type', 'same_address', 'main_address', 'address', 'city', 'state', 'zip', 'subscriber_id', 'user_id', 'customer_id', 'type')
                        ->withTrashed();
                },
                'unit' => function ($query) {
                    $query->select('id', 'address1', 'unit_number', 'property_id', 'building_id', 'barcode_id');
                },
            ]
        )
        ->latest()
        ->limit($request->length)->offset($request->start)
        ->get();
        //Get result with limit:End

        foreach ($activities as $activity) {
            if ($activity->getPropertyDetailByPropertyId->type == 1) {
                $propertyType = 'Single Family Home';
            } elseif ($activity->getPropertyDetailByPropertyId->type == 2) {
                $propertyType = 'Garden Style Apartment';
            } elseif ($activity->getPropertyDetailByPropertyId->type == 3) {
                $propertyType = 'High Rise Apartment';
            } elseif ($activity->getPropertyDetailByPropertyId->type == 4) {
                $propertyType = 'Townhome';
            }

            $property = '<b>Property Name:</b> ' . ucwords($activity->getPropertyDetailByPropertyId->name) . '<br/>';

            $property .= '<b>Property Name:</b> ' . ucwords($activity->getPropertyDetailByPropertyId->address);

            if (!empty($activity->getPropertyDetailByPropertyId->city)) {
                $property .= ucwords($activity->getPropertyDetailByPropertyId->city) . ' ';
            }

            if (!empty($activity->getPropertyDetailByPropertyId->getState->name)) {
                $property .= ucwords($activity->getPropertyDetailByPropertyId->getState->name) . ' ';
            }

            if (!empty($activity->getPropertyDetailByPropertyId->zip)) {
                $property .= ucwords($activity->getPropertyDetailByPropertyId->zip) . ' ';
            }

            if (!empty($activity->getPropertyDetailByPropertyId->zip)) {
                $property .= ucwords($activity->getPropertyDetailByPropertyId->zip) . ' ';
            }

            if (!empty($activity->unit->getBuildingDetail->building_name)) {
                ($activity->unit->getBuildingDetail->building_name);
            }

            if (!empty($activity->unit->getBuildingDetail->address)) {
                $property .= '<br/><b>Building Address</b> ' . ucwords($activity->unit->getBuildingDetail->address);
            }

            if (!empty($activity->unit->unit_number)) {
                $property .= '<br/><b>Unit:</b> ' . ucwords($activity->unit->unit_number);
            }

            if (!empty($propertyType)) {
                $property .= '<br/><b>Property Type:</b> ' . ucwords($propertyType);
            }

            if (!empty($activity->getUserDetail->name)) {
                $username = ucwords($activity->getUserDetail->name);
            }

            if ($activity->getUserDetail->email) {
                $email = $activity->getUserDetail->email;
            }

            if ($activity->text) {
                $text = ucwords($activity->text);
            }

            $info = '<b>Created At:</b> ' . \Carbon\Carbon::parse($activity->created_at)->timezone(getUserTimezone())->format('m-d-Y h:i A') . '<br/>';

            $info .= '<b>Updated At:</b> ' . \Carbon\Carbon::parse($activity->updated_at)->timezone(getUserTimezone())->format('m-d-Y h:i A') . '<br/>';

            $activityArray[] = [
                'id' => $i++,
                'property' => $property,
                'username' => $username,
                'email' => $email,
                'text' => $text,
                'info' => $info,
            ];
        }

        return json_encode(
            [
                'draw' => intval($request->draw),
                'recordsTotal' => !empty($activi) ? $activi->count() : 0,
                'recordsFiltered' => !empty($activi) ? $activi->count() : 0,
                'data' => $activityArray,
            ]
        );
    }

    public function allActivitiesLog()
    {
        $subscriberId = $this->user->subscriber_id;
        $isAdmin = $this->user->is_admin;
        $roleId = $this->user->role_id;

        $logs = \App\Activitylogs::when(
            $this->user->role_id === 1,
            function ($query) use ($subscriberId) {
                $query->where(
                    function ($query) use ($subscriberId) {
                        $query->whereIn(
                            'user_id',
                            function ($query) use ($subscriberId) {
                                $query->select('id')
                                ->from('users')
                                ->where('subscriber_id', $subscriberId)
                                ->whereNull('deleted_at');
                            }
                        )
                        ->orWhereIn(
                            'updated_by',
                            function ($query) use ($subscriberId) {
                                $query->select('id')
                                ->from('users')
                                ->whereNull('deleted_at')
                                ->where('subscriber_id', $subscriberId);
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
        ->where('type', 2)
        ->with(['logs', 'unit'])
        ->latest()->paginate(10);

        $this->data['activitylogs'] = $logs;
        return view('employee.allactivitieslog', $this->data);
    }
}
