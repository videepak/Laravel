<?php

namespace App\Http\Controllers;

use App\State;
use App\Subscriber;
use App\Subscription;
use App\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Notifications\EmailTemplate;
use Illuminate\Support\Facades\Storage;

class UserController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        //  $this->middleware('RoleAndPermission:customers')
        //  ->only(['index', 'create', 'create', 'store', 'edit', 'update', 'destroy']);
        // if ($this->user->is_admin != 1) {
        //     return redirect('home');
        //  }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }

    public function change_password()
    {
        return view('user.changepassword', $this->data);
    }

    public function update_password(Request $request)
    {
        $this->validate(
            $request,
            [
                'current_password' => 'required',
                'password' => 'required|string|confirmed',
                'password_confirmation' => 'required',
            ]
        );

        $user = Auth::user();
            
        $verifyPassword = \Hash::check(
            $request->current_password,
            $user->password
        );

        if ($verifyPassword) {
            $user->password = \Hash::make($request->password);
            $user->save();
            
        //#1354: Super Admin Dashboard Enhancements: Start
            $url = url('/password/reset');
            $contact = url('contact-us/');

            $content = "Your password has been updated successfully.<br/><br/>";
            $content .= "Hi " . ucwords($user->title . " " . $user->firstname . " " . $user->lastname) . ",<br/><br/>";
            $content .= "Nice work!. The password for your " . $user->getSubscriber->company_name . " account "  . $user->email . " has been successfully changed.<br/><br/>";
            $content .= "<b>Don't recognize this activity</b><br/><br/>";
            $content .= "<a href='$url' >Rest your password</a> above and change it to something you haven't used before.You should also <a href='$contact'>contact us</a> immediately to ensure  your account security.<br/>";
        
            try {
                $user->notify(new EmailTemplate($content, 'Change Password'));
            } catch (\Exception $e) {
                dd('Message: ' . $e->getMessage());
            }
        //#1354: Super Admin Dashboard Enhancements: End

            $class = 'success';
            $message = 'Password changed successfully.';
            $data = [
                'title' => 'User Password',
                'text' => $message,
                'class' => $class,
            ];

            return redirect('/home')->with('status', $data);
        } else {
            $class = 'error';
            $message = 'Current Password Does Not Match.';
            $data = [
                'title' => 'User Password',
                'text' => $message,
                'class' => $class,
            ];

            return redirect('changepassword')->with('status', $data);
        }
    }

    public function profile()
    {
        $this->data['states'] = State::all();

        $this->data['subscriber'] = '';
        
        $appPermission = \App\AppPermission::query()
            ->where('subscriber_id', $this->user->subscriber_id)
            ->where('user_id', $this->user->id)
            ->first();
        
        $this->data['appPermission'] = $appPermission;

        return view('user.profile', $this->data);
    }

    public function validateUserEmail(Request $request)
    {
        $email = $request->email;
        $id = $request->id;

        $userExists = User::where('email', $email)
                        ->where('id', '!=', $this->user->id)
                        ->first();

        if (!empty($userExists)) {
            abort(404, 'Email exists.');
        } else {
            return '';
        }
    }

    public function subsprofileupdate(Request $request, $id)
    {
        $this->validate(
            $request,
            [
                'company_name' => 'nullable',
                'title' => 'nullable',
                'first_name' => 'required',
                'last_name' => 'required',
                'city_name' => 'required',
                'timezone' => 'required',
                'state' => 'required',
                'zip' => 'required',
                'mobile' => 'required', 'digits:10',
                'image_type' => 'image|mimes:jpeg,png,jpg',
                'companyLogo' => 'image|mimes:jpeg,png,jpg',
            ],
            [
                'mobile.unique' => 'This number has already been taken.',
            ]
        );

        $subscriber = Subscriber::find($id);
        
        if (!empty($request->company_name)) {
            $subscriber->company_name = $request->company_name;
            $subscriber->address = $request->address;
        }
        
        $subscriber->city = $request->city_name;
        $subscriber->state = $request->state;
        $subscriber->zip = $request->zip;
        
        if ($request->hasFile('companyLogo')) {
            if (!is_null($subscriber->company_logo) && Storage::disk('s3')->exists('uploads/user/' . $subscriber->company_logo)) {
                Storage::disk('s3')->delete('uploads/user/' . $subscriber->company_logo);
            }
            
            $file = $request->file('companyLogo');
            $filename = time() . $file->getClientOriginalName();
            $filename = str_replace(' ', '', $filename);
            $filePath = 'uploads/user/' . $filename;

            Storage::disk('s3')->put($filePath, file_get_contents($file));
            
            //Remove after tesing : Start
            //$file->move(public_path() . '/uploads/user/', $filename);
            //Remove after tesing : End
            
            $subscriber->company_logo = $filename;
        }

        $status = $subscriber->save();
        //$url = Storage::disk('s3')->url('uploads/user/' . $filename);
        
        $user = User::find($request->userid);
        $user->title = $request->title;
        $user->firstname = $request->first_name;
        $user->lastname = $request->last_name;
        //$user->email = $request->email;
        $user->mobile = $request->mobile;
        $user->timezone = $request->timezone;

        if ($request->hasFile('image_type')) {
            if (!is_null($user->image_name) && Storage::disk('s3')->exists('uploads/user/' . $user->image_name)) {
                Storage::disk('s3')->delete('uploads/user/' . $user->image_name);
            }
            
            $file = $request->file('image_type');
            $filename = time() . $file->getClientOriginalName();
            $filename = str_replace(' ', '', $filename);
            $filePath = 'uploads/user/' . $filename;

            Storage::disk('s3')->put($filePath, file_get_contents($file));

            //Remove after tesing : Start
            //$file->move(public_path() . '/uploads/user/', $filename);
            //Remove after tesing : End

            $user->image_name = $filename;
        } else {
            $user->image_name = $request->old_image_name;
        }
        $status = $user->save();

        //For manual pickup (this option is only for subscriber only): Start
        if (!empty($this->user->is_admin)) {
            \App\AppPermission::updateOrCreate(
                [
                    'subscriber_id' => $this->user->subscriber_id,
                    'user_id' => $this->user->id,
                ],
                [
                    'manual_pickup' => !empty($request->manual_pickup) ? 1 : 0,
                    'subscriber_id' => $this->user->subscriber_id,
                    'user_id' => $this->user->id,
                ]
            );
        }
        //For manual pickup (this option is only for subscriber only): End

        $class = ($status) ? 'success' : 'error';
        $message = ($status) ? 'Subscriber Profile updated successfully.'
                    : 'Subscriber profile updation failed.';
        $data = [
            'title' => 'Subscriber',
            'text' => $message,
            'class' => $class,
        ];

        return redirect('profile')->with('status', $data);
    }

    public function userprofileupdate(Request $request, $id)
    {
        $this->validate(
            $request,
            [
                'title' => 'nullable',
                'first_name' => 'required',
                'last_name' => 'required',
                'mobile' => 'required|integer',
                'image_type' => 'image|mimes:jpeg,png,jpg',
            ]
        );

        $user = User::find($request->userid);
        $user->title = $request->title;
        $user->firstname = $request->first_name;
        $user->lastname = $request->last_name;
        $user->mobile = $request->mobile;

        if (!empty($request->file('image_type'))) {
            $file = $request->file('image_type');
            $destinationPath = public_path() . '/uploads/user/';
            $filename = time() . '.' . $file->getClientOriginalName();
            $file->move($destinationPath, $filename);
            $user->image_name = $filename;
        } else {
            $user->image_name = $request->old_image_name;
        }

        $status = $user->save();

        $class = ($status) ? 'success' : 'error';
        $message = ($status) ? 'User Profile updated successfully.'
                    : 'User profile updation failed.';
        $data = ['title' => 'User', 'text' => $message, 'class' => $class];

        return redirect('profile')->with('status', $data);
    }

    public function current_plan()
    {
        return view('payment/current_plan', $this->data);
    }

    public function manage_plan()
    {
        return view('payment/manage_plan', $this->data);
    }

    public function payment_history()
    {
        return view('payment/payment_history', $this->data);
    }

    public function upgradeplan($id)
    {
        // $auth = Auth::user();
        // $p = DB::table('subscribers')->where('user_id', $auth->id)->first();
        // $p_json = json_encode($p);
        // $udata = ['user_id'=>$auth->id,
        // 'plan_info'=>$p_json,
        // 'created_date' => date('Y-m-d H:i:s')
        // ];
        // DB::table('subscribe_history')->insert($udata);

        // $today = $auth->trial_end;
        // $update = ['updated_at'=>date('Y-m-d H:i:s'),
        // 'subscription_id' => $id,
        // 'sub_start_date' => $today,
        // 'sub_end_date' =>  date('Y-m-d', strtotime($today .'+ 30 days'))
        // ];

        // $q = DB::table('subscribers')->where('user_id', $auth->id)->update($update);
        // return redirect()->back()->with('success', 'Subscribe package upgraded successfully.');
        $this->data['id'] = $id;

        return view('payment/upgradeplan', $this->data);
    }

    public function thankspayment($id)
    {
        $payid = base64_decode($id);
        $this->data['payid'] = $payid;

        return view('payment/thankspayment', $this->data);
    }
}
