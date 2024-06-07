<?php

namespace App\Http\Controllers;

use App\Notifications\EmailTemplate;
use Illuminate\Http\Request;
use App\Subscription;
use App\Subscriber;
use Illuminate\Support\Facades\Mail;
use App\User;
use App\State;
use App\Activitylogs;
use App\Customer;
use App\PaymentResponse;
use App\Property;
use App\PropertyFrequencies;
use App\Service;
use App\Units;
use App\UserProperties;
use App\Violation;
use DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ManageSubscribers extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        // $subscriber = Subscriber::with('getState')->latest()->paginate(50);
        // $this->data['subscriber'] = $subscriber;
        return view('admin.subscribers.subsciber_list');
    }

    public function getSubscriber(Request $request)
    {
        $i = $request->start + 1;
        $subscriberArray = [];
        $email = $mobile = '';
        $search = $request->search['value'];
        
        //Get total record count: Start
        $subscribersCount = \App\Subscriber::has('user')->get();
        //Get total record count: End

        $subscribers = \App\Subscriber::query()
            ->when(
                !empty($search),
                function ($query) use ($search) {
                    $query->where('company_name', 'like', "%$search%")
                        ->orWhere('created_at', 'like', "%$search%")
                        ->orWhereRaw("user_id in (select `id` from `users` where `email` LIKE '%" . $search . "%' or  `mobile` LIKE '%" . $search . "%' and `deleted_at` is null)");
                }
            )
            ->latest()
            ->limit($request->length)->offset($request->start)
            ->has('user')
            ->with(
                [
                    'getState',
                    'user' => function ($query) {
                        $query->select(\DB::raw("CONCAT_WS(' ', `firstname`, `lastname`) as name"), 'id', 'email', 'mobile', 'last_login', 'prevoius_login');
                    }
                ]
            )
        ->get();
           
        foreach ($subscribers as $subscriber) {
            if (isset($subscriber->company_name)) {
                $campanyName = ucwords($subscriber->company_name);
            }

            if (isset($subscriber->user->email)) {
                $email = ucwords($subscriber->user->email);
            }

            if (isset($subscriber->user->mobile)) {
                $mobile = ucwords($subscriber->user->mobile);
            }

            if (isset($subscriber->created_at)) {
                $createdAt = \Carbon\Carbon::parse($subscriber->created_at)
                ->timezone(getUserTimezone())->format('m-d-Y');
            }

            if (isset($subscriber->created_at)) {
                if ($subscriber->payment == 1) {
                    if (isset($subscriber->sub_start_date)) {
                        $subStartDate = \Carbon\Carbon::parse($subscriber->sub_start_date)
                        ->timezone(getUserTimezone())->format('m-d-Y');
                    }
                } else {
                    $subStartDate = '<span class="label label-danger">Payment Pending</span>';
                }
            }

            if (isset($subscriber->created_at)) {
                if ($subscriber->payment == 1) {
                    if (isset($subscriber->sub_end_date)) {
                        $subEndDate = \Carbon\Carbon::parse($subscriber->sub_end_date)
                        ->timezone(getUserTimezone())->format('m-d-Y');
                    }
                } else {
                    $subEndDate = '<span class="label label-danger">Payment Pending</span>';
                }
            }

            if ($subscriber->payment == 1) {
                $status = '<td><span class="label label-success">Confirm</span></td>';
            } else {
                $status = '<td><span class="label label-danger">Pending</span></td>';
            }

            $url = url('admin/deletesubscriber/' . $subscriber->id);
            $url1 = url('admin/viewsubscriber/' . $subscriber->id);
            $url2 = url('admin/subscribers/welcomeEmail/' . $subscriber->id);

            $action = '<a href="' . $url . '" title="Delete"><li class="fa fa-trash-o"></li></a>';
            
            $action .= ' | <a href="' . $url1 . '" title="Edit"><li class="fa fa-edit"></li></a>';

            $action .= ' | <a href="' . $url2 . '" title="Welcome Email"><li class="fa fa-send"></li></a>';

            if ($subscriber->resident_alert) {
                $url3 = url('admin/subscribers/residents-alert/' . $subscriber->id . '/' . 0);

                $action .= ' | <a href="' . $url3 . '" title="Disable Residents Service Alert "><li class="fa fa-bell-o" style="color:green"></li></a>';
            } else {
                $url3 = url('admin/subscribers/residents-alert/' . $subscriber->id . '/' . 1);
                
                $action .= ' | <a href="' . $url3 . '" title="Enable Residents Service Alert "><li class="fa fa-bell-o" style="color:red"></li></a>';
            }

            if (!empty($subscriber->user->id)) {
                $action .= ' | <a href="javascript:void(0);" data-id="' . $subscriber->user->id . '" class="get-detail"  title="Reset Password"><li class="fa fa-key"></li></a>';
            }

            if ($subscriber->user->id == \Auth::user()->id && !is_null($$subscriber->user->prevoius_login)) {
                $lastLogin = \Carbon\Carbon::parse($subscriber->user->prevoius_login)
                    ->timezone(getUserTimezone())
                    ->format('m-d-Y h:i A');
            } elseif ($subscriber->user->id != \Auth::user()->id && !is_null($subscriber->user->last_login)) {
                $lastLogin = \Carbon\Carbon::parse($subscriber->user->last_login)
                    ->timezone(getUserTimezone())
                    ->format('m-d-Y h:i A');
            } else {
                $lastLogin = "";
            }
            
            $subscriberArray[] = [
                'id' => $i++,
                'companyName' => $campanyName,
                'createdAt' => $createdAt,
                'subStartDate' => $subStartDate,
                'subEndDate' => $subEndDate,
                'status' => $status,
                'action' => $action,
                'email' => $email,
                'last_login' => $lastLogin,
                'mobile' => $mobile
            ];
        }

        return json_encode(
            [
                'draw' => intval($request->draw),
                'recordsTotal' => !empty($subscribersCount) ? $subscribersCount->count() : 0,
                'recordsFiltered' => !empty($subscribersCount) ? $subscribersCount->count() : 0,
                'data' => $subscriberArray,
            ]
        );
    }

    public function residentAlert($id, $statu)
    {
        $status = \App\Subscriber::query()
            ->where('id', $id)
            ->update(
                [
                    'resident_alert' => $statu
                ]
            );

        $class = ($status) ? 'success' : 'error';
        $message = ($statu) ? 'Service Alert Enable Successfully.' : 'Service Alert Disable Successfully.';
        
        $data = array(
            'title' => 'Subscriber Service Alert',
            'text' => $message,
            'class' => $class
        );

        return redirect()->back()->with('status', $data);
    }

    public function add_subscriber()
    {
        $this->data['states'] = State::all();
        $this->data['subscriber'] = '';
        $this->data['subscription_type'] = Subscription::all();
        return view('admin.subscribers.add_subscriber', $this->data);
    }

    public function subscriber_add(Request $request)
    {

        $this->validate(
            $request,
            [
            'company_name' => 'required',
            'phone' => [
                'required',
                Rule::unique('users', 'mobile')
                    ->where(
                        function ($query) {
                            $query->whereNull('deleted_at');
                        }
                    )
            ],
            'email' => [
                'required',
                Rule::unique('users')
                    ->where(
                        function ($query) {
                            $query->whereNull('deleted_at');
                        }
                    )
                ]
            ]
        );

        $mobile = "+1" . $request->phone . "";
        $rawPassword = $this->generatePassword();
        $password = Hash::make($rawPassword);
        $to = $request->email;
        $user = new User();
        $user->title = $request->title;
        $user->firstname = $request->first_name;
        $user->lastname = $request->last_name;
        $user->email = $request->email;
        $user->password = $password;
        $user->is_admin = 1;
        $user->mobile = $request->phone;
        $user->device_token = '';
        $user->timezone = $request->timezone;
        $user->platform = '';
        $user->role_id = 1; /* Apply Admin role. */
        $user->subscriber_id = 0;
        $user->save();

        $user->attachRole(1); /* Attach Admin role. */

        $addsub = new Subscriber();
        $addsub->company_name = $request->company_name;
        $addsub->address = $request->address;
        $addsub->city = $request->city_name;
        $addsub->state = $request->state;
        $addsub->zip = $request->zip;
        $addsub->subscription_id = $request->subscription_type;
        $addsub->auto_renew = $request->renew;
        $addsub->payment = 0;
        $addsub->user_id = $user->id;
        $status = $addsub->save();

        /* Update the subscriber id :start */
        $user->subscriber_id = $addsub->id;
        $user->save();
        /* Update the subscriber id :end */

        #1354: Super Admin Dashboard Enhancements: Start
        $supportUser = new User();
        $supportUser->title = $request->title;
        $supportUser->firstname = 'Customer 1' . $addsub->id;
        $supportUser->lastname = 'Support';
        $supportUser->email = "support_1" . $addsub->id . "@trashscanapp.com";
        $supportUser->password = Hash::make('password');
        $supportUser->is_admin = 1;
        $supportUser->mobile = '18007706963';
        $supportUser->timezone = $request->timezone;
        $supportUser->role_id = 1;
        $supportUser->subscriber_id = $addsub->id;
        $supportUser->save();
        $supportUser->attachRole(1);
        #1354: Super Admin Dashboard Enhancements: End

        $content = "Dear " . $request->first_name . " " . $request->last_name . ",<br/>";
        $content .= "Thank you for subscribing to Trash Scan,"
                . " our one of a kind valet waste solution."
                . " Enclosed below are your login credentials"
                . " to access the Trash Scan online dashboard.<br/><br/>";
        $content .= "Username: " . $request->email . "<br/><br/>";
        $content .= "Password: " . $rawPassword . "<br/><br/>";
        $content .= "Learn how to get started.<br/><br/>";
        $content .= "To manage your account, "
                . "<a href='" . url('/login') . "'>Sign in</a><br/><br/>";
        $content .= "Best regards,<br/><br/>";
        $content .= "Trash Scan Customer Support.<br/><br/>";

        try {
            $user->notify(new EmailTemplate($content, 'Welcome to Trash Scan'));
            sms($mobile);
        } catch (\Exception $e) {
            // echo 'Message: ' .$e->getMessage();
        }
        
        $class = ($status) ? 'success' : 'error';
        $message = ($status) ? 'Subscriber created successfully.' : 'Subscriber creation failed.';
        
        $data = array(
            'title' => 'Subscriber',
            'text' => $message,
            'class' => $class
        );

        return redirect('admin/subscribers')->with('status', $data);
    }

    public function welcomeEmail($id)
    {
        $rawPassword = $this->generatePassword();
        $password = Hash::make($rawPassword);

        $subscriberInfo = Subscriber::find($id)->first();
        $requests = User::where("is_admin", 1)->where("subscriber_id", $id);
        $request = $requests->get();

        \App\User::where("is_admin", 1)
                ->where("subscriber_id", $id)
                ->update(
                    [
                        "password" => $password
                    ]
                );

        if (!empty($request[0])) {
            $content = "Dear " . $request[0]->firstname . " " . $request[0]->lastname . ",<br/>";
            $content .= "Welcome to Trash Scan! "
                    . "You have been invited by " . $subscriberInfo->company_name . " to use our one of "
                    . "a kind mobile solution on their behalf.  "
                    . "You should have received a text message with a "
                    . "download link of the Trash Scan App. "
                    . "Enclosed below are your login credentials "
                    . "to access the Trash Scan mobile app "
                    . "as well as our online portal.<br/><br/>";
            $content .= "Username: " . $request[0]->email . "<br/><br/>";
            $content .= "Password: " . $rawPassword . "<br/><br/>";
            $content .= "Learn how to get started.<br/><br/>";
            $content .= "If you did not receive the text message invite,"
                    . " please contact us for assistance. "
                    . "To manage your account,"
                    . " <a href='" . url('/login') . "'>Sign in</a><br/><br/>";
            $content .= "Best regards,<br/><br/>";
            $content .= "Trash Scan Customer Support.<br/><br/>";


            try {
                foreach ($request as $user) {
                    $user->notify(new EmailTemplate($content, 'Welcome to Trash Scan'));
                }
            } catch (\Exception $e) {
                //echo 'Message: ' .$e->getMessage();
            }

            $status = true;
        } else {
            $status = false;
        }

        //$request->notify(new EmailTemplate($content));


        $class = ($status) ? 'success' : 'error';
        $message = ($status) ? 'Welcome email sent successfully.'
                : 'Welcome email not sent.';
        $data = array(
            'title' => 'Welcome Email',
            'text' => $message,
            'class' => $class
        );

        return redirect('admin/subscribers')
                ->with('status', $data);
    }

    public function subscriber_delete($id)
    {
        $userId = \App\User::select('id')->where('subscriber_id', $id)->get();
        $usersId = $userId->map(
            function ($item, $key) {
                return $item->id;
            }
        );

        $propertyId = \App\Property::select('id')
                ->where('subscriber_id', $id)->get();
        
        $propertiesId = $propertyId->map(
            function ($item, $key) {
                return $item->id;
            }
        );

        if ($usersId->isNotEmpty()) {
            /* activity_log */
            $activity = Activitylogs::whereIn('user_id', $usersId);
            $activity->delete();

            /* customers------------- */
            $customer = Customer::whereIn('user_id', $usersId);
            $customer->delete();

            /* payment */
            $payment = PaymentResponse::whereIn('user_id', $usersId);
            $payment->delete();

            /* violation */
            $violation = Violation::whereIn('user_id', $usersId);
            $violation->delete();

            /* Barcode Note */
            $barcodeNotes = \App\BarcodeNotes::whereIn('user_id', $usersId);
            $barcodeNotes->delete();

            /* Reason */
            $reason = \App\Reason::whereIn('user_id', $usersId);
            $reason->delete();

            /* unit_properties */
            $user_properties = UserProperties::whereIn('user_id', $usersId);
            $user_properties->delete();
        }

        if ($propertiesId->isNotEmpty()) {
            /* services */
            $services = Service::whereIn('property_id', $propertiesId);
            $services->delete();

            /* units */
            $units = Units::whereIn('property_id', $propertiesId);
            $units->delete();

            /* walk through records */
            $walkThroughRecord = \App\walkThroughRecord::whereIn('property_id', $propertiesId);
            $walkThroughRecord->delete();

            /* Building */
            $building = \App\Building::whereIn('property_id', $propertiesId);
            $building->delete();
        }

        //Property
        $property = Property::where('subscriber_id', $id);
        $property->delete();

        //Users
        $user = User::where('subscriber_id', $id);
        $user->delete();

        $subscriber = Subscriber::find($id);
        $subscriber->delete();

        $class = 'success';
        $message = 'Subscriber deleted successfully.';
        $data = array(
            'title' => 'Subscriber',
            'text' => $message,
            'class' => $class
        );

        return redirect('admin/subscribers')
                ->with('status', $data);
    }

    public function view_subscriber($id)
    {
        $this->data['states'] = State::all();
        $subscriber = Subscriber::find($id);
        $this->data['subscriber'] = $subscriber;
        $this->data['user'] = User::find($subscriber->user_id);
        $this->data['subscription_type'] = Subscription::all();

        return view('admin.subscribers.add_subscriber', $this->data);
    }

    public function update_subscriber(Request $request, $id)
    {

        $addsub = Subscriber::find($id);
        $addsub->company_name = $request->company_name;
        $addsub->address = $request->address;
        $addsub->city = $request->city_name;
        $addsub->state = $request->state;
        $addsub->zip = $request->zip;
        $addsub->subscription_id = $request->subscription_type;
        $addsub->auto_renew = $request->renew;
        $status = $addsub->update();

        $userId = $addsub->user_id;

        $this->validate($request, [
            'company_name' => 'required',
            'phone' => [
                'required',
                Rule::unique('users', 'mobile')
                    ->where(
                        function ($query) use ($userId) {
                            $query->where('id', '!=', $userId)
                                    ->whereNull('deleted_at');
                        }
                    )
                ],
            'email' => [
                'required',
                Rule::unique('users')
                    ->where(
                        function ($query) use ($userId) {
                            $query->where('id', '!=', $userId)
                                    ->whereNull('deleted_at');
                        }
                    )
                ]
            ]);

        $user = User::find($addsub->user_id);
        $user->title = $request->title;
        $user->firstname = $request->first_name;
        $user->lastname = $request->last_name;
        $user->email = $request->email;
        $user->timezone = $request->timezone;
        $user->mobile = $request->phone;
        $user->save();

        $class = ($status) ? 'success' : 'error';
        $message = ($status) ? 'Subscriber updated successfully.'
                : 'Subscriber updation failed.';
        $data = array(
            'title' => 'Subscriber',
            'text' => $message,
            'class' => $class
        );

        return redirect('admin/subscribers')->with('status', $data);
    }

    function generatePassword()
    {
        $seed = str_split('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
        // and any other characters
        shuffle($seed); // probably optional since array_is randomized; this may be redundant
        $rand = '';
        foreach (array_rand($seed, 6) as $k) {
            $rand .= $seed[$k];
        }

        return $rand;
    }

    public function validateEmail(Request $request)
    {

        $email = $request->email;
        $id = $request->id;


        $userExists = \App\User::where('email', $email)
                ->where('id', '!=', $id)->exists();

        if ($userExists) {
            abort(404, 'Email exists.');
        } else {
            return '';
        }

        //$id = $request->id;
//        $user_exists = DB::table('users')->whereRaw('email = ?', [$email])->get()->first();
//
//        if (!empty($id)) {
//            $user = User::find($id);
//
//            if (!empty($user->id) && ($email == $user->email)) {
//                return '';
//            } elseif (empty($user_exists->id)) {
//                return '';
//            } else {
//                abort(404, 'Email exists.');
//            }
//        } elseif (!empty($user_exists->id)) {
//            abort(404, 'Email exists.');
//        }
    }

    public function validateMobile(Request $request)
    {

        $mobile = $request->phone;
        $id = $request->id;

        $userExists = \App\User::where('mobile', $mobile)
            ->where('id', '!=', $id)->exists();

        if ($userExists) {
            abort(404, 'Mobile exists.');
        } else {
            return '';
        }
//        $id = $request->id;
//
//        $user_exists = DB::table('users')->whereRaw('mobile = ?', [$mobile])->get()->first();
//
//        if (!empty($id)) {
//            $user = User::find($id);
//
//            if (!empty($user->id) && ($mobile == $user->mobile)) {
//                return '';
//            } elseif (empty($user_exists->id)) {
//                return '';
//            } else {
//                abort(404, 'Mobile exists.');
//            }
//        } elseif (!empty($user_exists->id)) {
//            abort(404, 'Mobile exists.');
//        }
    }

    public function superAdminIndex()
    {
        return view('admin.superadmin.list');
    }

    public function superAdminDelete(Request $request)
    {
        $admin = \App\Admin::find($request->id)->delete();

        if ($admin) {
            $class = 'success';
            $message = 'Super admin user deleted successfully.';
            $data = [
                'title' => 'Super aAdmin User',
                'text' => $message,
                'class' => $class,
            ];
        } else {
            $class = 'danger';
            $message = 'Some error occur please try after sometime.';
            $data = [
                'title' => 'Super aAdmin User',
                'text' => $message,
                'class' => $class,
            ];
        }

        return redirect('admin/super-admin')->with('status', $data);
    }

    public function getSuperAdmin(Request $request)
    {
        $i = $request->start + 1;
        $subscriberArray = [];
        $email = $mobile = '';
        $search = $request->search['value'];
        
        //Get total record count: Start
        $subscribersCount = \App\Admin::query()
            ->when(
                !empty($search),
                function ($query) use ($search) {
                    $query->where('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%");
                }
            )
        ->get();
        //Get total record count: End

        $subscribers = \App\Admin::query()
            ->when(
                !empty($search),
                function ($query) use ($search) {
                    $query->where('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%");
                }
            )
            ->latest()
            ->limit($request->length)->offset($request->start)
        ->get();
                    
        foreach ($subscribers as $subscriber) { //dd($subscriber);
            if (isset($subscriber->name)) {
                $name = ucwords($subscriber->name);
            }

            if (isset($subscriber->email)) {
                $email = ucwords($subscriber->email);
            }

            $url = url('admin/super-admin-delete/' . $subscriber->id);
            $text = 'Are you sure, you want to delete it?';

            $action = '<a href="' . $url . '" onclick="return confirm(' . $text . ')" ><li class="fa fa-trash-o"></li></a>';
            
            $action .= ' | <a href="javascript:void(0);" data-id="' . $subscriber->id . '" class="get-detail" ><li class="fa fa-edit"></li></a>';

            if ($subscriber->id == \Auth::user()->id && !is_null($subscriber->prevoius_login)) {
                $lastLogin = \Carbon\Carbon::parse($subscriber->prevoius_login)
                    ->timezone(getUserTimezone())
                    ->format('m-d-Y h:i A');
            } elseif ($subscriber->id != \Auth::user()->id && !is_null($subscriber->last_login)) {
                $lastLogin = \Carbon\Carbon::parse($subscriber->last_login)
                    ->timezone(getUserTimezone())
                    ->format('m-d-Y h:i A');
            } else {
                $lastLogin = "";
            }

            $subscriberArray[] = [
                'id' => $i++,
                'name' => $name,
                'email' => $email,
                'last_login' => $lastLogin,
                'action' => $action
            ];
        }

        return json_encode(
            [
                'draw' => intval($request->draw),
                'recordsTotal' => !empty($subscribersCount) ? $subscribersCount->count() : 0,
                'recordsFiltered' => !empty($subscribersCount) ? $subscribersCount->count() : 0,
                'data' => $subscriberArray,
            ]
        );
    }

    public function addSuperAdmin(Request $request)
    {
        $superAdmin = \App\Admin::create(
            [
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]
        );

        $class = ($superAdmin) ? 'success' : 'error';
        $message = ($superAdmin) ? 'Super admin added successfully.'
            : 'Some error occur please try after sometime.';

        $data = [
            'title' => 'Super Admin User',
            'text' => $message,
            'class' => $class,
        ];

        return redirect('admin/super-admin')
            ->with('status', $data);
    }

    public function getSuperDetail($id)
    {
        $template = \App\Admin::find($id);

        return response()
            ->json(
                [
                    'detail' => $template,
                    'result' => true,
                ]
            );
    }

    public function updateSuperDetail(Request $request, $id)
    {
        if (!empty($request->password)) {
            $data = ['name' => $request->name,'password' => Hash::make($request->password)];
        } else {
            $data = ['name' => $request->name];
        }
        
        $template = \App\Admin::where('id', $id)
            ->update($data);

        $class = ($template) ? 'success' : 'error';
        $message = ($template) ? 'Super admin updated successfully.'
                    : 'Some error occur please try after sometime.';

        $data = [
            'title' => 'Super Admin',
            'text' => $message,
            'class' => $class,
        ];

        return redirect('admin/super-admin')->with('status', $data);
    }

    public function resetSubscriberPassword(Request $request)
    {
        $template = \App\User::with(
            [
                'getSubscriber' => function ($query) {
                    $query->select('id', 'company_name');
                }
            ]
        )
        ->find($request->subscriber_id);
        
        $template->password = Hash::make($request->password);
        $template->save();

        $url = url('admin/forget/password/');
        $contact = url('contact-us/');

        $content = "Dear " . ucwords($template->title . " " . $template->firstname . " " . $template->lastname) . ",<br/>";
        $content .= "Your password is reset by the subscriber.<br/><br/>";
        $content .= "Username: " . $template->email . "<br/><br/>";
        $content .= "Password: " . $request->password . "<br/><br/>";
        $content .= "Learn how to get started.<br/><br/>";
        $content .= "To manage your account, "
            . "<a href='" . url('/login') . "'>Sign in</a><br/><br/>";
        $content .= "Best regards,<br/><br/>";
        $content .= "Trash Scan Customer Support.<br/><br/>";

        try {
            $template->notify(new EmailTemplate($content, 'Change Password'));
        } catch (\Exception $e) {
          //  dd('Message: ' . $e->getMessage());
        }
        
        //#1354: Super Admin Dashboard Enhancements: Start
        $content = "Your password has been updated successfully.<br/><br/>";
        $content .= "Hi " . ucwords($template->title . " " . $template->firstname . " " . $template->lastname) . ",<br/><br/>";
        $content .= "Nice work!. The password for your " . $template->getSubscriber->company_name . " account "  . $template->email . " has been successfully changed.<br/><br/>";
        $content .= "<b>Don't recognize this activity</b><br/><br/>";
        $content .= "<a href='$url' >Reset your password</a> above and change it to something you haven't used before.You should also <a href='$contact'>contact us</a> immediately to ensure  your account security.<br/>";
        
        try {
            $template->notify(new EmailTemplate($content, 'Change Password'));
        } catch (\Exception $e) {
            //dd('Message: ' . $e->getMessage());
        }
        //#1354: Super Admin Dashboard Enhancements: End

        $class = ($template) ? 'success' : 'error';
        $message = ($template) ? 'Password updated successfully.'
            : 'Some error occur please try after sometime.';

        $data = [
            'title' => 'Subscriber',
            'text' => $message,
            'class' => $class,
        ];

        return redirect()->back()->with('status', $data);
    }

    public function userIndex()
    {
        return view('admin.users.list');
    }

    public function getUser(Request $request)
    {
        $i = $request->start + 1;
        $userArray = [];
        $email = $mobile = $name = $companyName = $rolename = '';
        $search = $request->search['value'];
        
        //Get total record count: Start
        $userCount = \App\User::select('id')
            ->when(
                !empty($search),
                function ($query) use ($search) {
                    $query->where('firstname', 'like', "%$search%")
                        ->orWhere('mobile', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%")
                        ->orWhereRaw("subscriber_id in (select `id` from `subscribers` where `company_name` LIKE '%" . $search . "%' and `deleted_at` is null)")
                        ->orWhereRaw("role_id in (select `roles`.id from `roles` inner join `role_user` on `roles`.`id` = `role_user`.`role_id` where `display_name` LIKE '%" . $search . "%' and `roles`.`deleted_at` is null)");
                }
            )
        ->get();
        //Get total record count: End

        $users = \App\User::select('id', 'email', 'mobile', 'subscriber_id', 'is_admin', 'last_login', 'prevoius_login', \DB::raw("CONCAT_WS(' ', `title`, `firstname`, `lastname`) AS name"))
            ->when(
                !empty($search),
                function ($query) use ($search) {
                    $query->where('firstname', 'like', "%$search%")
                        ->orWhere('mobile', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%")
                        ->orWhereRaw("subscriber_id in (select `id` from `subscribers` where `company_name` LIKE '%" . $search . "%' and `deleted_at` is null)")
                        ->orWhereRaw("role_id in (select `roles`.id from `roles` inner join `role_user` on `roles`.`id` = `role_user`.`role_id` where `display_name` LIKE '%" . $search . "%' and `roles`.`deleted_at` is null)");
                }
            )
            ->with(
                [
                    'getSubscriber'
                ]
            )
            ->latest()
            ->limit($request->length)->offset($request->start)
        ->get();
         
        foreach ($users as $user) {
            $role = $user->roles()->first();

            if (!empty($role->id)) {
                $rolename = !empty($user->is_admin) ? "Admin" : $role->display_name;
            }

            if (isset($user->name)) {
                $name = ucwords($user->name);
            }

            if (isset($user->getSubscriber->company_name)) {
                $companyName = ucwords($user->getSubscriber->company_name);
            }

            if (isset($user->email)) {
                $email = $user->email;
            }

            if (isset($user->mobile)) {
                $mobile = ucwords($user->mobile);
            }

            if (!empty($user->id)) {
                $action = '<a href="javascript:void(0);" data-id="' . $user->id . '" class="get-detail"  title="Reset Password"><li class="fa fa-key"></li> </a>';
                $action .= '<a href="javascript:void(0);" data-id="' . $user->id . '" class="delete-users" title="Delete Users"><li class="fa fa-trash-o"></li></a>';
            }

            if ($user->id == \Auth::user()->id && !is_null($user->prevoius_login)) {
                $lastLogin = \Carbon\Carbon::parse($user->prevoius_login)
                    ->timezone(getUserTimezone())
                    ->format('m-d-Y h:i A');
            } elseif ($user->id != \Auth::user()->id && !is_null($user->last_login)) {
                $lastLogin = \Carbon\Carbon::parse($user->last_login)
                    ->timezone(getUserTimezone())
                    ->format('m-d-Y h:i A');
            } else {
                $lastLogin = "";
            }

            $userArray[] = [
                'id' => $i++,
                'name' => $name,
                'rolename' => $rolename,
                'email' => $email,
                'mobile' => $mobile,
                'companyName' => $companyName,
                'last_login' => $lastLogin,
                'action' => $action
            ];
        }

        return json_encode(
            [
                'draw' => intval($request->draw),
                'recordsTotal' => !empty($userCount) ? $userCount->count() : 0,
                'recordsFiltered' => !empty($userCount) ? $userCount->count() : 0,
                'data' => $userArray,
            ]
        );
    }
  
    public function adminUserDelete(Request $request)
    {
        $user_id = $request->userid;
        $delete = \App\User::where('id', $user_id)->delete();
        
        if (!empty($delete)) {
            $response = [
                'status' => 0,
                'message' => 'User Delete Successfully',
            ];
        }

       return response()->json($response);
    }

    public function getUserDetail($id)
    {
        $template = \App\Admin::find($id);

        return response()
            ->json(
                [
                    'detail' => $template,
                    'result' => true,
                ]
            );
    }

    public function archiveUsers()
    {
        return view('admin.users.archive-users');
    }

    public function archiveUsersList(Request $request)
    {
        $i = $request->start + 1;
        $userArray = [];
        $email = $mobile = $name = $title = '';
        $lastLogin = '-- / -- / --';
        $rolename = 'No Role';
        $companyName = 'Not  Assigned';
        $search = $request->search['value'];

        $archiveUserCount = \App\User::onlyTrashed()->get();

        $archiveUsers = \App\User::select('id', 'email', 'mobile', 'subscriber_id', 'last_login','role_id',\DB::raw("CONCAT_WS(' ', `title`, `firstname`, `lastname`) AS name"))
            ->when(
                !empty($search),
                function ($query) use ($search) {
                    $query->Where('mobile', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%")
                        ->orWhere(\DB::raw("CONCAT_WS(' ', `title`, `firstname`, `lastname`)"), 'like', "%$search%")
                        ->orWhereRaw("subscriber_id in (select `id` from `subscribers` where `company_name` LIKE '%" . $search . "%' and `deleted_at` is null)")
                        ->orWhereRaw("role_id in (select `roles`.id from `roles` inner join `role_user` on `roles`.`id` = `role_user`.`role_id` where `display_name` LIKE '%" . $search . "%' and `roles`.`deleted_at` is not null)");
                }
            )
            ->onlyTrashed()
            ->orderBy('deleted_at', 'DESC')
            ->latest()
            ->limit($request->length)->offset($request->start)
            ->get();

        foreach ($archiveUsers as $user) {
            $role = $user->roles()->first();

            if (!empty($role->id)) {
                $rolename = !empty($user->is_admin) ? "Admin" : $role->display_name;
            }

            if (isset($user->name)) {
                $name =  ucwords($user->name);
            }

            if (isset($user->getSubscriber->company_name)) {
                $companyName = ucwords($user->getSubscriber->company_name);
            }

            if (isset($user->email)) {
                $email = $user->email;
            }

            if (isset($user->mobile)) {
                $mobile = ucwords($user->mobile);
            }

            if ($user->id == \Auth::user()->id && !is_null($user->prevoius_login)) {
                $lastLogin = \Carbon\Carbon::parse($user->prevoius_login)
                    ->timezone(getUserTimezone())
                    ->format('m-d-Y h:i A');
            } elseif ($user->id != \Auth::user()->id && !is_null($user->last_login)) {
                $lastLogin = \Carbon\Carbon::parse($user->last_login)
                    ->timezone(getUserTimezone())
                    ->format('m-d-Y h:i A');
            }

            $userArray[] = [
                'id' => $i++,
                'name' => $name,
                'rolename' => $rolename,
                'email' => $email,
                'mobile' => $mobile,
                'companyName' => $companyName,
                'lastlogin' => $lastLogin,
            ];
        }

        return json_encode(
            [
                'draw' => intval($request->draw),
                'recordsTotal' => !empty($archiveUserCount) ? $archiveUserCount->count() : 0,
                'recordsFiltered' => !empty($archiveUserCount) ? $archiveUserCount->count() : 0,
                'data' => $userArray,
            ]
        );
    }
}
