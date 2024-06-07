<?php

namespace App\Http\Controllers;

use App\Notifications\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class CustomerController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
        /*
        * Customer can handel the property manager add,edit,delete,
        * view etc functionality because it's under the manage
        * customer in nav bar.
        */
        $this->middleware('RoleAndPermission:customers');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $cusDetails = \App\Customer::query()
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
        
        $this->data['customers'] = $cusDetails;

        return view('customer.list', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $this->data['states'] = \App\State::all();
        $this->data['allCustomers'] = \App\Customer::query()
            ->whereNotIn(
                'id',
                function ($query) {
                    $query->select('customer_id')
                        ->from('customer_subscribers')
                        ->where('user_id', $this->user->user_id)
                        ->where('subscriber_id', $this->user->subscriber_id);
                }
            )
            ->orderBy('name', 'ASC')
            ->get();

        if ($request->type == 'modal') {
            $this->data['redirect_to'] = url('property/create');

            return view('modals.customer_create', $this->data);
        } else {
            return view('customer/create', $this->data);
        }
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
                'customer_name' => 'required',
                'phone' => 'required|numeric',
                'address' => 'required',
                'city' => 'required',
                'zip' => 'required|numeric',
                //'email' => 'required|unique:customers,email,NULL,id,deleted_at,NULL',
            ]
        );

        if (!$request->customerRadio) {
            $customer = new \App\Customer();

            $customer->name = $request->customer_name;
            $customer->email = $request->email;
            $customer->phone = $request->phone;
            $customer->address = $request->address;
            $customer->city = $request->city;
            $customer->state = $request->state;
            $customer->zip = $request->zip;
            $customer->user_id = $this->user->id;
            $customer->subscriber_id = $this->user->subscriber_id;

            $status = $customer->save();
        }
        
        #1464: Remove constraint for adding Customers : Start
        $customerId = $request->customerRadio ? $request->existingCustomer : $customer->id;
        
        $status = \App\CustomerSubscriber::create(
            [
                    'customer_id' => $customerId,
                    'user_id' => $this->user->id,
                    'subscriber_id' => $this->user->subscriber_id,
                ]
        );
        #1464: Remove constraint for adding Customers : End

        $class = ($status) ? 'success' : 'error';
        $message = ($status) ? 'Customer created successfully.'
                : 'Customer creation failed.';
        
        $data = [
            'title' => 'Customer',
            'text' => $message,
            'class' => $class,
        ];

        //if (!empty($request->redirect_to)) {
            //return redirect($request->redirect_to)->with('status', $data);
        //}

        if (!empty($request->localStorage)) {
            return redirect()->action('PropertyController@create', ['id' => $customerId]);
        } else {
            return redirect('customer')->with('status', $data);
        }
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
        //Permission check:Start
        if ($this->checkCustomerPermission($id)) {
            return redirect('unauthorized');
        }
        //Permission check:End
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
        //Permission check:Start
        if ($this->checkCustomerPermission($id)) {
            return redirect('unauthorized');
        }
        //Permission check:End

        $this->data['customer'] = \App\Customer::find($id);
        $this->data['states'] = \App\State::all();

        return view('customer.create', $this->data);
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
        //Permission check:Start
        if ($this->checkCustomerPermission($id)) {
            return redirect('unauthorized');
        }
        //Permission check:End

        $this->validate(
            $request,
            [
                'customer_name' => 'required',
                'phone' => 'required|numeric',
                'address' => 'required',
                'city' => 'required',
                'zip' => 'required|numeric',
            ]
        );

        $customer = \App\Customer::find($id);

        $customer->name = $request->customer_name;
        $customer->phone = $request->phone;
        $customer->address = $request->address;
        $customer->city = $request->city;
        $customer->state = $request->state;
        $customer->zip = $request->zip;
        $status = $customer->save();

        $class = ($status) ? 'success' : 'error';
        $message = ($status) ? 'Customer updated successfully.'
                : 'Customer updation failed.';
        $data = [
            'title' => 'Customer',
            'text' => $message,
            'class' => $class,
        ];

        return redirect('customer')
                ->with('status', $data);
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
        //Permission check:Start
        if ($this->checkCustomerPermission($id)) {
            return redirect('unauthorized');
        }
        //Permission check:End

        $checkProperty = \App\Property::query()
            ->where('customer_id', $id)
            ->where('subscriber_id', $this->user->subscriber_id)
            ->first();

        if (!empty($checkProperty) && !is_null($checkProperty)) {
            $class = 'error';
            $message = 'You can not delete this customer some '
                    . 'properties are assosiated with it.';
            $data = [
                'title' => 'Customer',
                'text' => $message,
                'class' => $class,
            ];

            return redirect('customer')->with('status', $data);
        } else {
            $customer = \App\CustomerSubscriber::query()
                ->where('customer_id', $id)
                ->where('subscriber_id', $this->user->subscriber_id)
                ->where('user_id', $this->user->user_id)
                ->delete();

            $class = 'success';
            $message = 'Customer deleted successfully.';
            $data = [
                'title' => 'Customer',
                'text' => $message,
                'class' => $class,
            ];

            return redirect('customer')->with('status', $data);
        }
    }

    public function validateEmail(Request $request)
    {
        $email = $request->email;
        $id = $request->id;

        $customerExists = \App\Customer::where('email', $email)
                ->where('id', '!=', $id)->first();

        $userExists = \App\User::where('email', $email)
                ->where('customer_id', '!=', $id)->first();

        if (!empty($customerExists || !empty($userExists))) {
            abort(404, 'Email exists.');
        } else {
            return '';
        }
    }

    public function validateMobile(Request $request)
    {
        $phone = $request->phone;
        $id = $request->id;

        $mobExists = \App\Customer::where('phone', $phone)
            ->where('id', '!=', $id)->first();
        
        $userMobExists = \App\User::where('mobile', $phone)
            ->where('customer_id', '!=', $id)->first();

        if (!empty($mobExists) || !empty($userMobExists)) {
            abort(404, 'Phone number already exists');
        } else {
            return '';
        }
    }

    public function property(Customer $customer, Request $request)
    {
        if (isset($customer->id) && !empty($customer->id)) {
            $this->data['states'] = \App\State::all();
            $this->data['current_customer'] = $customer;
            $this->data['customers'] = $this->user->customers;
            $this->data['days'] = days();

            return view('property/create', $this->data);
        } else {
            return redirect('customer');
        }
    }

    public function details($id)
    {
        $this->data['customer'] = \App\Customer::findOrFail($id);
        $this->data['properties'] = \App\Property::where('customer_id', $id)
            ->where('subscriber_id', $this->user->subscriber_id)
            ->with('getState')
            ->get();

        $this->data['cus_image'] = \App\User::select('image_name')
                ->where('subscriber_id', $id)->first();

        return view('customer.details', $this->data);
    }

    public function propertyManagerIndex()
    {
        //Permission: only admin can access:Start
        if (!$this->user->hasRole(['admin'])) {
            return redirect('unauthorized');
        }
        //Permission: only admin can access:End

        $employees = \App\User::query()
            ->where('subscriber_id', $this->user->subscriber_id)
            ->where('role_id', 10)
            ->latest()
            ->get();
        
        $this->data['offset'] = 0;
        $this->data['employees'] = $employees;

        return view('manager.list', $this->data);
    }

    public function propertyManagerCreate()
    {
        //Permission: only admin can access:Start
        if (!$this->user->hasRole(['admin'])) {
            return redirect('unauthorized');
        }
        //Permission: only admin can access:End

        $this->data['property'] = \App\Property::where('subscriber_id', $this->user->subscriber_id)
                ->get();

        return view('manager/create', $this->data);
    }

    public function propertyManagerEdit($id)
    {
        //Permission: only admin can access:Start
        if (!$this->user->hasRole(['admin']) || $this->checkManagerPermission($id)) {
            return redirect('unauthorized');
        }
        //Permission: only admin can access:End

        $propertyId = $permissionId = [];

        $array = \App\UserProperties::select('property_id')
                ->where('user_id', $id)->get();

        $permissions = \App\UserPermissions::select('permission_id')
                ->where('user_id', $id)->get();

        foreach ($array as $arrays) {
            $propertyId[] = $arrays->property_id;
        }
        foreach ($permissions as $permission) {
            $permissionId[] = $permission->permission_id;
        }

        $this->data['propertyCheck'] = $propertyId;
        $this->data['permission_id'] = $permissionId;
        $this->data['property'] = \App\Property::where('subscriber_id', $this->user->subscriber_id)
                ->get();
        $this->data['employee'] = \App\User::where('id', $id)->get();

        return view('manager/create', $this->data);
    }

    public function propertyManagerUpdate(Request $request, $id)
    {
        //Permission: only admin can access:Start
        if (!$this->user->hasRole(['admin']) || $this->checkManagerPermission($id)) {
            return redirect('unauthorized');
        }
        //Permission: only admin can access:End

        $this->validate(
            $request,
            [
                'title' => 'nullable',
                'first_name' => 'required',
                'timezone' => 'required',
                'last_name' => 'required',
                'mobile' => 'required|min:10|max:10',
                'role' => 'required|integer',
            ]
        );

        $user = \App\User::find($id);
        $user->title = $request->title;
        $user->firstname = $request->first_name;
        $user->lastname = $request->last_name;
        $user->timezone = $request->timezone;
        $user->mobile = $request->mobile;

        if (!empty($request->role)) {
            $role = getPropertyManageId();
        }

        $user->role_id = $role;
        $user->roles()->sync($role);

        $status = $user->save();

        //Delete assgin property and permission
        \App\UserProperties::where('user_id', $user->id)->delete();
        \App\UserPermissions::where('user_id', $user->id)->delete();
        //Delete assgin property and permission
        //Assgin Property for property manager: Start
        if (isset($request->property_id) && !empty($request->property_id)) {
            foreach ($request->property_id as $propertyId) {
                \App\UserProperties::create(
                    [
                        'property_id' => $propertyId,
                        'user_id' => $user->id,
                        'type' => 2,
                    ]
                );
            }
        }
        //Assgin Property for property manager: End
        //Permission assgin for property manager
        //(eg. Manager Report, Manage Violation): Start
        if (isset($request->permission) && !empty($request->permission)) {
            foreach ($request->permission as $permissionId) {
                \App\UserPermissions::create(
                    [
                        'user_id' => $user->id,
                        'permission_id' => $permissionId,
                    ]
                );
            }
        }
        //Permission assgin for property manager
        //(eg. Manager Report, Manage Violation): End

        $class = ($status) ? 'success' : 'error';
        $message = ($status) ? 'Property Manager updated successfully.'
                : 'Property Manager updation failed.';
        $data = [
            'title' => 'Property Manager',
            'text' => $message,
            'class' => $class,
        ];

        return redirect('property-manager')->with('status', $data);
    }

    public function propertyManagerDestory($id)
    {
        //Permission: only admin can access:Start
        if (!$this->user->hasRole(['admin']) || $this->checkManagerPermission($id)) {
            return redirect('unauthorized');
        }
        //Permission: only admin can access:End

        $user = \App\User::find($id)->delete();
        $user = \App\UserProperties::where('user_id', $id)
                ->where('type', 2)->delete();
        \App\UserPermissions::where('user_id', $id)->delete();

        $class = 'success';
        $message = 'Property Manager deleted successfully.';
        $data = [
            'title' => 'Property Manager',
            'text' => $message,
            'class' => $class,
        ];

        return redirect('property-manager')
                ->with('status', $data);
    }

    public function propertyManagerStore(Request $request)
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
            ]
        );

        $body = [];
        $user = new \App\User();

        $mobile = '+1' . $request->mobile . '';
        $rawPassword = $this->generatePassword();
        $password = Hash::make($rawPassword);
        $send_to = $request->email;

        $user->title = $request->title;
        $user->firstname = $request->first_name;
        $user->lastname = $request->last_name;
        $user->email = $request->email;
        $user->mobile = $request->mobile;
        $user->timezone = $request->timezone;
        $user->device_token = '';
        $user->platform = '';
        $user->is_admin = 0;
        if (!empty($request->role)) {
            $role = 10;
        }

        $user->subscriber_id = $this->user->subscriber_id;
        $user->password = $password;

        $user->role_id = $role;
        $status = $user->save();
        $user->attachRole($role);

        //Assgin Property for property manager: Start
        if (isset($request->property_id) && !empty($request->property_id)) {
            foreach ($request->property_id as $propertyId) {
                \App\UserProperties::create(
                    [
                        'property_id' => $propertyId,
                        'user_id' => $user->id,
                        'type' => 2,
                    ]
                );
            }
        }
        //Assgin Property for property manager: End
        //Permission assgin for property manager
        //(eg. Manager Report, Manage Violation): Start
        if (isset($request->permission) && !empty($request->permission)) {
            foreach ($request->permission as $permissionId) {
                \App\UserPermissions::create(
                    [
                        'user_id' => $user->id,
                        'permission_id' => $permissionId,
                    ]
                );
            }
        }
        //Permission assgin for property
        //manager (eg. Manager Report, Manage Violation): End
        //User::activeLog('Added Property
        // Manager', $this->user->id, NULL, $request->ip(), NULL);

        $subscriber_info = \App\Subscriber::find($this->user->subscriber_id);

        $content = 'Dear ' . $request->first_name . ' ' . $request->last_name . ',<br/>';
        $content .= 'Welcome to Trash Scan!'
                . ' You have been invited by ' . $subscriber_info->company_name;
        $content .= ' as the property manager.<br/><br/>You are able to access';
        $content .= ' the trash pickup status on the associated'
                . ' property.<br/><br/>Enclosed below are your';
        $content .= ' login credentials to access the'
                . ' Trash Scan online portal.<br/><br/>';
        $content .= 'Username: ' . $request->email . '<br/><br/>';
        $content .= 'Password: ' . $rawPassword . '<br/><br/>';
        $content .= 'Learn how to get started.<br/><br/>';
        $content .= 'If you did not receive the text message invite';
        $content .= ' please contact us for assistance.';
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
        $message = ($status) ? 'Property Manager created successfully.'
                : 'Property Manager creation failed.';
        $data = [
            'title' => 'Property Manager',
            'text' => $message,
            'class' => $class,
        ];

        //Send SMS:Start
        $text = 'Welcome to Trash Scan! Your login'
                . ' credentials are Username: ' . $request->email;
        $text .= 'and password: ' . $rawPassword;
        $text .= 'Please visit www.TrashScanApp.com/...'
                . ' to download our mobile app';
        sms($mobile, $text);
        //Send SMS:End

        return redirect('property-manager')
                ->with('status', $data);
    }

    public function exsitingCustomer(Request $request)
    {
        return response()
            ->json(
                [
                    'response' => \App\Customer::find($request->id),
                ]
            );
    }
}
