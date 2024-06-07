<?php

namespace App\Http\Controllers;

use App\Notifications\EmailTemplate;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        parent::__construct();

        $this->middleware('RoleAndPermission:employees')
            ->only(
                [
                    'index', 'create', 'create',
                    'store', 'edit', 'update',
                    'destroy', 'getassignedproperties',
                ]
            );
    }

    public function index()
    {
        return view('employee/list', $this->data);
    }

    public function misspickupEmployeList(Request $request)
    {
        $employeeArray = [];

        $empolyee = \App\User::select('id', 'title', 'firstname', 'lastname', 'mobile', 'email')
        ->when(
            $this->user->role_id == 1,
            function ($query) {
                $query->where('subscriber_id', Auth::user()->subscriber_id);
            }
        )
        ->when(
            $this->user->role_id != 1,
            function ($query) {
                $query->where('user_id', Auth::user()->id);
            }
        )
        ->where('is_admin', '!=', 1)
        ->whereNotIn('role_id', [10])
        ->withCount('assignedproperties')
        ->with(
            [
                'assignedproperties' => function ($query) {
                    $query->select('id', 'property_id', 'user_id', 'type');
                },
            ]
        )
        ->get();

        foreach ($empolyee as $empolye) {
            $assignedProperty = $empolye->assignedproperties;

            foreach ($assignedProperty as $assignedId) {
                $today = getStartEndTime()->startTime;
                $addDay = getStartEndTime()->endTime;

                $dayNumber = \Carbon\Carbon::parse($today)->format('w');

                $hasProperty = \App\PropertyFrequencies::select('day')
                    ->where('property_id', $assignedId->property_id)
                    ->where('day', $dayNumber)
                    ->get();

                if (isset($assignedId->property_id) && $hasProperty->isNotEmpty()) {
                    $propertyType = $assignedId->getPropertyDetail->service->pickup_type;
                    $unit = $assignedId->getUnitDetail->where('is_active', 1);

                    foreach ($unit as $units) {
                        $pickCount = \App\Activitylogs::where('barcode_id', $units->barcode_id)
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
                            //         $query->where('wast', 1)
                            //             ->where('recycle', 1);
                            //     }
                            // )
                            // ->whereBetween(
                            //     \DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                            //     [
                            //             $today,
                            //             $addDay,
                            //         ]
                            // )
                            ->where(
                                function ($query) {
                                    $query->where('wast', 1)
                                        ->orWhere('recycle', 1);
                                }
                            )
                            ->get();

                        if ($pickCount->isEmpty()) {
                            // $assignedProperties = \App\UserProperties::where('user_id', $empolye->id)->count();
                            $assignedProperties = $empolye->assignedproperties_count;
                            $role = $empolye->roles()->first();

                            $rolename = !empty($role->id) ? $role->display_name : '';

                            $employeeArray[] = [
                                'user_id' => $empolye->id,
                                'firstname' => $empolye->firstname,
                                'lastname' => $empolye->lastname,
                                'rolename' => $rolename,
                                'mobile' => $empolye->mobile,
                                'email' => $empolye->email,
                                'assigned_properties' => $assignedProperties,
                            ];
                            break 2;
                        }
                    }
                }
            }
        }

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $itemCollection = collect($employeeArray);
        $perPage = 10;
        $currentPageItems = $itemCollection->slice(($currentPage * $perPage) - $perPage, $perPage)->all();
        $paginatedItems = new LengthAwarePaginator($currentPageItems, count($itemCollection), $perPage);
        $this->data['employees'] = $paginatedItems->setPath($request->url());

        return view('employee/misspickupemployeelist', $this->data);
    }

    public function getEmployeelist(Request $request)
    {
        $i = $request->start + 1;
        $employeeArray = [];
        $search = $request->search['value'];

        //Get total result:Start
        $employe = \App\User::whereNotIn('role_id', [10])
            ->where('is_admin', '!=', 1)
            ->where('id', '!=', $this->user->id)
            ->where('subscriber_id', $this->user->subscriber_id)
            ->when(
                !empty($search),
                function ($query) use ($search) {
                    $query->where(
                        function ($query) use ($search) {
                            $query->where('firstname', 'like', "%$search%")
                                ->orWhere('lastname', 'like', "%$search%")
                                ->orWhere('title', 'like', "%$search%")
                                ->orWhere('email', 'like', "%$search%")
                                ->orWhere('mobile', 'like', "%$search%")
                                ->orWhere(\DB::raw("CONCAT_WS(' ', `title`, `firstname`, `lastname`)"), 'like', "%$search%");
                        }
                    );
                }
            )
        ->get();
        //Get total result:End
        //Get result with limit:Start (Todo: merge the both queries)
        $employees = \App\User::whereNotIn('role_id', [10])
            ->where('is_admin', '!=', 1)
            ->where('id', '!=', $this->user->id)
            ->where('subscriber_id', $this->user->subscriber_id)
            ->when(
                !empty($search),
                function ($query) use ($search) {
                    $query->where(
                        function ($query) use ($search) {
                            $query->where('firstname', 'like', "%$search%")
                                ->orWhere('lastname', 'like', "%$search%")
                                ->orWhere('title', 'like', "%$search%")
                                ->orWhere('email', 'like', "%$search%")
                                ->orWhere('mobile', 'like', "%$search%")
                                ->orWhere(\DB::raw("CONCAT_WS(' ', `title`, `firstname`, `lastname`)"), 'like', "%$search%");
                        }
                    );
                }
            )
            ->latest()
            ->limit($request->length)->offset($request->start)
            ->get();
        //Get result with limit:End

        if (count($employees) > 0) {
            foreach ($employees as $employee) {
                $role = $employee->roles()->first();
                $rolename = '';

                if (!empty($role->id)) {
                    $rolename = $role->display_name;
                }

                //Prepare the action link :Start
                $action = "<a href='" . url('employee/' . $employee->id) . "' onclick='return deleteEmployee(this, event);' title='Delete' ><li class='fa fa-trash-o'></li></a>";

                $action .= " | <a href='" . url('employee/' . $employee->id . '/edit/') . "' title='Edit'><li class='fa fa-edit'></li></a>";

                $action .= " | <a href='" . url('employee/properties/' . $employee->id) . "' title='View'><li class='fa fa-eye'></li></a>";

                $action .= " | <a class='makeaction' href='" . url('welcomeEmail/' . $employee->id) . "' title='Welcome Email'><li class='fa fa-send'></li></a>";
                //Prepare the action link :End

                if ($employee->id == Auth::user()->id && !is_null($employee->prevoius_login)) {
                    $lastLogin = \Carbon\Carbon::parse($employee->prevoius_login)
                        ->timezone(getUserTimezone())
                        ->format('m-d-Y h:i A');
                } elseif ($employee->id != Auth::user()->id && !is_null($employee->last_login)) {
                    $lastLogin = \Carbon\Carbon::parse($employee->last_login)
                        ->timezone(getUserTimezone())
                        ->format('m-d-Y h:i A');
                } else {
                    $lastLogin = "";
                }

                $employeeArray[] = [
                    'user_id' => $i++,
                    'name' => ucwords($employee->title . ' ' . $employee->firstname . ' ' . $employee->lastname),
                    'rolename' => $rolename,
                    'mobile' => $employee->mobile,
                    'email' => $employee->email,
                    'last_login' => $lastLogin,
                    'action' => $action,
                ];
            }
        }

        return json_encode(
            [
                'draw' => intval($request->draw),
                'recordsTotal' => !empty($employe) ? $employe->count() : 0,
                'recordsFiltered' => !empty($employe) ? $employe->count() : 0,
                'data' => $employeeArray,
            ]
        );
    }

    // private function missedPichupEmployee()
    // {
    //     for ($i = 4; $i > -1; --$i) {
    //         $days[] = \Carbon\Carbon::now()->subDays($i)->format('Y-m-d');
    //     }

    //     $empolyeId = [];
    //     $empolyee = \App\User::when(
    //         $this->user->role_id == 1,
    //         function ($query) {
    //             $query->where('subscriber_id', Auth::user()->subscriber_id);
    //         }
    //     )
    //     ->when(
    //         $this->user->role_id != 1,
    //         function ($query) {
    //             $query->where('user_id', Auth::user()->id);
    //         }
    //     )
    //     ->where('is_admin', '!=', 1)->whereNotIn('role_id', [10])->get();

    //     foreach ($days as $day) {
    //         foreach ($empolyee as $empolye) {
    //             $assignedProperty = $empolye->assignedproperties;

    //             foreach ($assignedProperty as $assignedId) {
    //                 $today = \Carbon\Carbon::parse($day)
    //                         ->format('Y-m-d').' 06:00:00';
    //                 $addDay = \Carbon\Carbon::parse($day)
    //                         ->addDay(1)->format('Y-m-d').' 05:59:59';
    //                 $dayNumber = \Carbon\Carbon::parse($day)->format('w');
    //                 $hasProperty = \App\PropertyFrequencies::select('day')
    //                         ->where('property_id', $assignedId->property_id)
    //                         ->where('day', $dayNumber)
    //                         ->get();

    //                 if (isset($assignedId->property_id) && $hasProperty->isNotEmpty()) {
    //                     $propertyType = $assignedId->getPropertyDetail->service->pickup_type;
    //                     $unit = $assignedId->getUnitDetail->where('is_active', 1);

    //                     foreach ($unit as $units) {
    //                         $pickCount = \App\Activitylogs::where('barcode_id', $units->barcode_id)
    //                             ->whereBetween('created_at', [$today, $addDay])
    //                             ->when(
    //                                 $propertyType == 1,
    //                                 function ($query) {
    //                                     $query->where('wast', 1);
    //                                 }
    //                             )
    //                             ->when(
    //                                 $propertyType == 2,
    //                                 function ($query) {
    //                                     $query->where('recycle', 1);
    //                                 }
    //                             )
    //                             ->when(
    //                                 $propertyType == 3,
    //                                 function ($query) {
    //                                     $query->where('wast', 1)
    //                                         ->where('recycle', 1);
    //                                 }
    //                             )
    //                         ->get();

    //                         if ($pickCount->isEmpty()) {
    //                             $empolyeId[] = $empolye->id;
    //                             break 2;
    //                         }
    //                     }
    //                 }
    //             }
    //         }
    //     }

    //     return array_unique($empolyeId);
    // }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $users = \App\User::where('subscriber_id', '=', $this->user->subscriber_id)
            ->get()
            ->pluck('id');

        $roles = \App\Role::whereIn('user_id', $users)
            ->orWhere('user_id', 0)
            ->whereNotIn('id', [10])->get();

        //For task: #694: Start
        $adminUser = \App\User::select('id', \DB::raw("CONCAT_WS(' ', `title`, `firstname`, `lastname`) AS name"))
            ->where('subscriber_id', $this->user->subscriber_id)
            ->where('role_id', 1)
            ->orderBy('is_admin', 'decs')
            ->latest()->get();
        //For task: #694: End

        $this->data['property'] = \App\Property::where('subscriber_id', $this->user->subscriber_id)->get();
        $this->data['roles'] = $roles;

        $this->data['adminUser'] = $adminUser;

        return view('employee/create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'title' => 'nullable',
                'first_name' => 'required',
                'timezone' => 'required',
                'last_name' => 'required',
                'mobile' => 'required|min:10|max:10',
                'role' => 'required|integer',
                'employee_type' => 'required|integer',
                'reportingManagerId' => 'required|integer',
                'serviceInTime' => 'nullable|required_with:serviceOutTime',
                'serviceOutTime' => 'nullable|required_with:serviceInTime',
                'frequency' => 'required|array|min:1',
            ]
        );

        //CHECK SUBCRIPTION LIMIT FOR ADMIN AND EMPOLYEE ACCOUNT.
        $details = $this->subscriptionDetails();

        if ($request->role == 1) {
            if ($details['total_admin'] <= $details['employees_role']) {
                $data = [
                    'title' => 'Employee',
                    'text' => 'You have exceeded the limit of creating admin.',
                    'class' => 'error',
                ];

                return redirect()->back()->with('status', $data);
            }
        } elseif ($details['total_subs'] <= $details['employees_created_subs']) {
            $data = [
                'title' => 'Employee',
                'text' => 'You have exceeded the limit of creating an employee.',
                'class' => 'error',
            ];

            return redirect()->back()->with('status', $data);
        }
        //CHECK SUBCRIPTION LIMIT FOR ADMIN AND EMPOLYEE ACCOUNT.

        $body = [];
        $user = new User();

        $mobile = '+1' . $request->mobile . '';
        $rawpassword = $this->generatePassword();
        $password = Hash::make($rawpassword);
        $send_to = $request->email;

        $user->title = $request->title;
        $user->firstname = $request->first_name;
        $user->lastname = $request->last_name;
        $user->email = $request->email;
        $user->mobile = $request->mobile;
        $user->timezone = $request->timezone;
        $user->employee_type = $request->employee_type;
        $user->device_token = '';
        $user->platform = '';
        $user->is_admin = 0;

        //Task #1136: Start
        $user->clockinout_frequency_day = implode(',', $request->frequency);
        //Task #1136: End

        //Task #694: End
        $user->service_in_time = \Carbon\Carbon::parse($request->serviceInTime)->toTimeString();
        $user->service_out_time = \Carbon\Carbon::parse($request->serviceOutTime)->toTimeString();
        $user->reporting_manager_id = $request->reportingManagerId;
        //Task #694: Start

        $role = $request->role;

        $user->subscriber_id = $this->user->subscriber_id;
        $user->user_id = $this->user->id;
        $user->password = $password;

        $user->role_id = $role;
        $status = $user->save();
        $user->attachRole($role);

        //Assgin Properties to employee: Start
        if (isset($request->property_id) && !empty($request->property_id)) {
            foreach ($request->property_id as $propertyId) {
                \App\UserProperties::create(
                    [
                        'property_id' => $propertyId,
                        'user_id' => $user->id,
                        'type' => 1,
                    ]
                );
            }
        }
        //Assgin Properties to employee : End

        $subscriber_info = \App\Subscriber::find($this->user->subscriber_id);

        $content = 'Dear ' . ucwords($request->first_name . ' ' . $request->last_name) . ',<br/>';
        $content .= 'Welcome to Trash Scan! You'
                . ' have been invited by ' . $subscriber_info->company_name;
        $content .= ' to use our one of a kind'
                . ' mobile solution on their behalf.';
        $content .= ' You should have received a'
                . ' text message with a download link of the Trash Scan App';
        $content .= ' to access the Trash Scan mobile app '
                    . 'as well as our online portal.<br/><br/>';
        $content .= ' Username: ' . $request->email . '<br/><br/>';
        $content .= ' Password: ' . $rawpassword . '<br/><br/>';
        $content .= 'Learn how to get started.<br/><br/>';
        $content .= 'If you did not receive the'
                . ' text message invite, please contact us for assistance.';
        $content .= ' To manage your account,'
                . " <a href='" . url('/login') . "'>Sign in</a><br/><br/>";
        $content .= 'Best regards,<br/><br/>';
        $content .= 'Trash Scan Customer Support.<br/><br/>';

        try {
            $user->notify(new EmailTemplate($content, 'Welcome to Trash Scan'));
        } catch (\Exception $e) {
            //echo 'Message: ' .$e->getMessage();
        }

        $class = ($status) ? 'success' : 'error';
        $message = ($status) ? 'Employee created successfully.' : 'Employee creation failed.';

        $data = [
            'title' => 'Employee',
            'text' => $message,
            'class' => $class,
        ];

        $text = 'Welcome to Trash Scan! Your login '
                . 'credentials are Username: ' . $request->email;
        $text .= ' and password: ' . $rawpassword . '';
        $text .= ' Please visit www.TrashScanApp.com/...'
                . ' to download our mobile app';
        sms($mobile, $text);

        return redirect('employee')
            ->with('status', $data);
    }

    public function welcomeEmail($id)
    {
        $rawPassword = $this->generatePassword();
        $password = Hash::make($rawPassword);

        $request = \App\User::find($id);
        $subscriberInfo = \App\Subscriber::find($request->subscriber_id);

        $request->password = $password;
        $request->save();

        if (!empty($request)) {
            $content = 'Dear ' . ucwords($request->firstname . ' ' . $request->lastname) . ',<br/>';
            $content .= 'Welcome to Trash Scan! You have '
                    . 'been invited by ' . $subscriberInfo->company_name;
            $content .= ' to use our one of a kind mobile'
                    . ' solution on their behalf.';
            $content .= ' You should have received a text '
                    . 'message with a download link of the Trash Scan App';
            $content .= ' to access the Trash Scan mobile app '
                    . 'as well as our online portal.<br/><br/>';
            $content .= 'Username: ' . $request->email . '<br/><br/>';
            $content .= 'Password: ' . $rawPassword . '<br/><br/>';
            $content .= 'Learn how to get started.<br/><br/>';
            $content .= 'If you did not receive the text '
                    . 'message invite, please contact us for assistance.';
            $content .= ' To manage your account, '
                    . "<a href='" . url('/login') . "'>Sign in</a><br/><br/>";
            $content .= 'Best regards,<br/><br/>';
            $content .= 'Trash Scan Customer Support.<br/><br/>';

            try {
                $request->notify(new EmailTemplate($content, 'Welcome to Trash Scan'));
            } catch (\Exception $e) {
                //echo 'Message: ' .$e->getMessage();
            }
            $status = true;
        } else {
            $status = false;
        }

        $class = ($status) ? 'success' : 'error';
        $message = ($status) ? 'Welcome email sent successfully.' : 'Welcome email not sent.';

        $data = [
            'title' => 'Welcome Email',
            'text' => $message,
            'class' => $class,
        ];

        return redirect('employee')->with('status', $data);
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
        // $start = new \Carbon\Carbon('05:00:00');
        // $end   = new \Carbon\Carbon('06:00:00');
        // echo $start->diffInHours($end);
        // dd();

        //Check validate record:Start
        if ($this->checkEmployeePermission($id)) {
            return redirect('unauthorized');
        }
        //Check validate record:End

        $this->data['total_subs'] = '';
        $this->data['employees_created_subs'] = '';
        $employee = \App\User::find($id);

        $propertyId = $permissionId = [];
        $array = \App\UserProperties::select('property_id')
            ->where('user_id', $id)->get();

        foreach ($array as $arrays) {
            $propertyId[] = $arrays->property_id;
        }

        $users = \App\User::where('subscriber_id', '=', $this->user->subscriber_id)
            ->get()->pluck('id');

        $roles = \App\Role::whereIn('user_id', $users)->orWhere('user_id', 0)
            ->whereNotIn('id', [10])->get();

        //For task: #694: Start
        $adminUser = \App\User::select('id', \DB::raw("CONCAT_WS(' ', `title`, `firstname`, `lastname`) AS name"))
            ->where('subscriber_id', $this->user->subscriber_id)
            ->where('role_id', 1)
            ->where('id', '!=', $id)
            ->orderBy('is_admin', 'decs')
            ->get();

        $this->data['adminUser'] = $adminUser;
        //For task: #694: End

        $this->data['property'] = \App\Property::where('subscriber_id', $this->user->subscriber_id)->get();

        $this->data['roles'] = $roles;

        $this->data['propertyCheck'] = $propertyId;

        $this->data['employee'] = $employee;

        //Task: 1136 : Start
        $this->data['clockinoutFrequencyDay'] = explode(',', $employee->clockinout_frequency_day);
        //Task: 1136 : End

        return view('employee.create', $this->data);
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
        //Check validate record:Start
        if ($this->checkEmployeePermission($id)) {
            return redirect('unauthorized');
        }
        //Check validate record:End

        $this->validate(
            $request,
            [
                'title' => 'nullable',
                'first_name' => 'required',
                'last_name' => 'required',
                'timezone' => 'required',
                'mobile' => 'required|min:10|max:10',
                'role' => 'required|integer',
                'employee_type' => 'required|integer',
                'reportingManagerId' => 'required|integer',
                'serviceInTime' => 'nullable|required_with:serviceOutTime',
                'serviceOutTime' => 'nullable|required_with:serviceInTime',
                'frequency' => 'required|array|min:1',
            ]
        );

        $details = $this->subscriptionDetails();
        $user = \App\User::find($id);

        //CHECK SUBCRIPTION LIMIT FOR ADMIN AND EMPOLYEE ACCOUNT.
        if ($request->role == \config('constants.adminRoleId')
            && $request->role != $user->role_id) {
            if ($details['total_admin'] <= $details['employees_role']) {
                $data = [
                    'title' => 'Employee',
                    'text' => 'You have exceeded the limit of creating admin.',
                    'class' => 'error',
                ];

                return redirect()->back()->with('status', $data);
            }
        } elseif ($details['total_subs'] <= $details['employees_created_subs']
            && $user->role_id == \config('constants.adminRoleId')
            && $request->role != $user->role_id) {
            $data = [
                'title' => 'Employee',
                'text' => 'You have exceeded the limit of creating an employee.',
                'class' => 'error',
            ];

            return redirect()->back()->with('status', $data);
        }
        //CHECK SUBCRIPTION LIMIT FOR ADMIN AND EMPOLYEE ACCOUNT.

        //Task: #694, #1153 (Comment: #25, #24): Start
        if ($user->role_id == \config('constants.adminRoleId')
            && $request->role != $user->role_id) {
            \App\User::where('reporting_manager_id', $user->id)
                ->update(
                    [
                        'reporting_manager_id' => $this->subscriber->user_id,
                    ]
                );

            \App\User::where('id', $user->id)
                ->update(
                    [
                        'api_token' => null,
                    ]
                );
        }

        if ($user->role_id != \config('constants.adminRoleId')
            && $request->role == \config('constants.adminRoleId')) {
            \App\User::where('id', $user->id)
                ->update(
                    [
                        'api_token' => null,
                    ]
                );
        }
        //Task: #694, #1153 (Comment: #25, #24): End

        $user->firstname = $request->first_name;
        $user->lastname = $request->last_name;
        $user->title = $request->title;
        //$user->email = $request->email;
        $user->mobile = $request->mobile;
        $user->employee_type = $request->employee_type;
        $user->timezone = $request->timezone;
        $user->user_id = $this->user->id;

        //Task #1136: Start
        $user->clockinout_frequency_day = implode(',', $request->frequency);
        //Task #1136: End

        //Task #694: End
        $user->service_in_time = \Carbon\Carbon::parse($request->serviceInTime)->toTimeString();
        $user->service_out_time = \Carbon\Carbon::parse($request->serviceOutTime)->toTimeString();
        $user->reporting_manager_id = $request->reportingManagerId;
        //Task #694: Start

        $role = $request->role;

        $user->role_id = $role;
        $user->roles()->sync($role);

        //Delete assgin properties
        \App\UserProperties::where('user_id', $user->id)->delete();
        //Delete assgin properties

        //Assgin Properties to employee: Start
        if (isset($request->property_id) && !empty($request->property_id)) {
            foreach ($request->property_id as $propertyId) {
                \App\UserProperties::create(
                    [
                        'property_id' => $propertyId,
                        'user_id' => $user->id,
                        'type' => 1,
                    ]
                );
            }
        }
        //Assgin Properties to employee: End

        $status = $user->save();
        $class = ($status) ? 'success' : 'error';
        $message = ($status) ? 'Employee updated successfully.'
                : 'Employee updation failed.';
        $data = [
            'title' => 'Employee',
            'text' => $message,
            'class' => $class,
        ];

        return redirect('employee')->with('status', $data);
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
        //Check validate record:Start
        if ($this->checkEmployeePermission($id)) {
            return redirect('unauthorized');
        }
        //Check validate record:End

        $checkExistingRole = \App\UserProperties::where('user_id', $id)
            ->first();

        if ($checkExistingRole) {
            $class = 'error';
            $message = 'You can not delete the employee'
                    . ' because property already assigned to this employee.';

            $data = [
                'title' => 'Employee',
                'text' => $message,
                'class' => $class,
            ];

            return redirect('employee')->with('status', $data);
        } else {
            $user = \App\User::find($id);
            $user->delete();

            $class = 'success';
            $message = 'Employee deleted successfully.';
            $data = [
                'title' => 'Employee',
                'text' => $message,
                'class' => $class,
            ];

            return redirect('employee')->with('status', $data);
        }
    }

    public function getassignedproperties($id)
    {
        $empPrperty = \App\UserProperties::where('user_id', '=', $id)->get();

        if (count($empPrperty) > 0) {
            foreach ($empPrperty as $property) {
                $propertyDetails = \App\Property::where('id', $property->property_id)
                    ->with(['getState'])->get();

                foreach ($propertyDetails as $propDetails) {
                    $pdetails[] = [
                        'property_name' => $propDetails->name,
                        'type' => $propDetails->type,
                        'add_type' => $propDetails->address_type,
                        'units' => $propDetails->units,
                        'address' => $propDetails->address,
                        'city' => $propDetails->city,
                        'state' => $propDetails->getState->name,
                        'zip' => $propDetails->zip,
                    ];

                    $this->data['propertydetails'] = $pdetails;
                }
            }
        }

        $this->data['employee_details'] = \App\User::findOrFail($id);

        return view('employee.property', $this->data);
    }

    public function subscriptionDetails()
    {
        $subscriberPackCount = \App\Subscriber::join(
            'subscriptions',
            'subscriptions.id',
            '=',
            'subscribers.subscription_id'
        )
        ->select('subscriptions.package_field_collector', 'subscriptions.package_admin')
        ->where('subscribers.id', $this->user->subscriber_id)
        ->first();

        $this->data['total_subs'] = $subscriberPackCount['package_field_collector'];
        $this->data['total_admin'] = $subscriberPackCount['package_admin'];
        $this->data['admin_created_subs'] = \App\User::where('subscriber_id', $this->user->subscriber_id)
            ->where('role_id', 1)->count();

        $this->data['employees_created_subs'] = \App\User::where('subscriber_id', $this->user->subscriber_id)
            ->where('is_admin', '!=', 1)
            ->where('role_id', '!=', 1)
            ->where('role_id', '!=', 10)
            ->count();
        $this->data['employees_role'] = \App\User::where('subscriber_id', $this->user->subscriber_id)
            ->where('role_id', 1)
            ->count();

        return $this->data;
    }
}
