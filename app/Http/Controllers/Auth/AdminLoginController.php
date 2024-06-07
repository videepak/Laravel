<?php

namespace App\Http\Controllers\Auth;

use App\Notifications\EmailTemplate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use App\Admin;

class AdminLoginController extends Controller
{

    public function __construct()
    {
        $this->middleware('guest:admin');
    }

    public function showLoginForm()
    {
        return view('auth.admin-login');
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $loginDetail = ['email' => $request->email, 'password' => $request->password];
        
        if (Auth::guard('admin')->attempt($loginDetail, $request->has('remember'))) {
            \App\Admin::where(
                [
                    'email' => $request->email
                ]
            )
            ->update(
                [
                    'prevoius_login' => \DB::raw("last_login"),
                    'last_login' => \Carbon\Carbon::now()->toDateTimeString()
                ]
            );

            return redirect()->intended(route('admin.dashboard'));
        }


        // redirect to login for on faliure
        return redirect()->back()->withInput($request->only('email', 'remember'));
    }

    public function forgetPassword(Request $rquest)
    {

        return view('auth.forget-password');
    }

    public function forgetCheckEmail(Request $request)
    {

        //validate form data
        $this->validate($request, [
            'email' => 'required|email',
        ]);

        $email = $request->email;

        $admin = Admin::where('email', $email)->first();
        if (!empty($admin)) {
            $content = "Hello! ,<br/>";
            $content .= "You are receiving this email because we received";
            $content .= "a password reset request for your account.<br/><br/>";
            $content .= "<a href='" . url('/admin/reset/password') . "'>Reset Password</a><br/><br/>";
            $content .= "If you did not request a password reset, no further action is required.";
            $content .= "Best regards,<br/><br/>";
            $content .= "Trash Scan Customer Support.<br/><br/>";
            $admin->notify(new EmailTemplate($content, 'Reset Password'));

            $admin->remember_token = 1;
            $admin->save();

            session()->flash('message', 'Please check your email to reset password.');
            return redirect()->back();

            //return view('auth.reset');
        } else {
            session()->flash('correct_email', 'Please provide your correct email address.');
            return redirect()->back()->withInput($request->only('email', 'remember'));
        }




        // redirect to dashboard if success
        //return redirect()->intended(route('admin.dashboard'));
    }

    public function resetPassword(Request $request)
    {

        return view('auth.reset');
    }

    public function adminPasswordReset(Request $request)
    {

        //validate form data
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|min:6',
            'password_confirmation' => 'required|same:password',
        ]);


        $admin = new Admin();

        $email = $request->email;
        $password = bcrypt($request->password);

        $admin = Admin::where('email', $email)->where('remember_token', 1)->first();
        if (!empty($admin)) {
            $admin->password = $password;
            $admin->remember_token = 0;
            $admin->save();
            return redirect()->intended(route('admin.login'));
        } else {
            session()->flash('message', 'Email does not exists / this link has already used.');
            return redirect()->back()->withInput($request->only('email', 'remember'));
        }
    }

    public function contactUs(Request $request)
    {

        //validate form data
        $this->validate($request, [
            'email' => 'required|email',
            'name' => 'required',
            'message' => 'required',
        ]);

        $email = $request->email;
        $name = $request->name;
        $message = $request->message;

        $admin = new \App\User();
        $admin->email = 'info@trashscan.com';
        $content = "Username: " . $name . '<br>';
        $content .= "Email: " . $email . '<br>';
        $content .= "Message: " . $message . '<br>';

        try {
            $admin->notify(new EmailTemplate($content, 'New contact receive.'));
        } catch (\Exception $e) {
            //echo 'Message: ' .$e->getMessage();
        }
        echo true;
    }
}
