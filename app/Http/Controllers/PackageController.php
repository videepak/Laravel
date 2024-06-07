<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Hash;
use DB;
use Session;

class PackageController extends Controller
{

    public function __construct()
    {
       // parent::__construct();
    }

    public function index()
    {
        //return view('home', $this->data);
    }

    public function step1()
    {
        return view('step1');
    }

    public function nvtDemo()
    {
        return view('nvtdemo');
    }

    public function step2()
    {
        if (session('email') != '') {
            return view('step2');
        } else {
            return redirect('request-demo');
        }
    }

    public function step3()
    {
        if (session('email') != '') {
            return view('step3');
        } else {
            return redirect('request-demo');
        }
    }
    
    public function step3demo()
    {
        return view('step3');
    }

    public function step4()
    {
        if (session('email') != '') {
            $udata = DB::table('temp_signup')->where('email', session('email'))->first();
            
            return view('step4', ['udata' => $udata]);
        } else {
            return redirect('request-demo');
        }
    }

    public function step5()
    {
        if (session('email') != '') {
            return view('step5');
        } else {
            return redirect('request-demo');
        }
    }

    public function login_welcome()
    {
        $emailid = base64_decode($_GET['uid']);
        $count = DB::table('temp_signup')->where('email', $emailid)->count();
        
        if ($count > 0) {
            $uinfo = DB::table('temp_signup')->where('email', $emailid)->first();
            $today = date('Y-m-d');
            $trial_end = date('Y-m-d', strtotime($today . '+ ' . TRIAL_DAYS . ' days'));
            $udata = [
                "firstname" => $uinfo->firstname,
                "lastname" => $uinfo->lastname,
                "job_title" => $uinfo->job_title,
                "mobile" => $uinfo->mobile,
                "is_admin" => "1",
                "employee_type" => "1",
                "subscriber_menu" => "yes",
                "email" => $uinfo->email,
                "password" => $uinfo->password,
                "company" => $uinfo->company,
                "field_employee" => $uinfo->employee,
                "country" => $uinfo->country,
                "created_at" => date("Y-m-d H:i:s"),
                "trial" => 'yes',
                "trial_start" => $today,
                "trial_end" => $trial_end
            ];
            
            DB::table('users')->insert($udata);
            $id = DB::getPdo()->lastInsertId();

            //#1315: Cannot assign reporting manager for new trial user employees: Start
            \App\User::where('id', $id)->update(["role_id" => 1, 'user_id' => $id, 'reporting_manager_id' => $id]);
            #1315: Cannot assign reporting manager for new trial user employees: End
            
            DB::table('role_user')->insert(['user_id' => $id, 'role_id' => '1']);
            
            $start = date('Y-m-d', strtotime($today . '+ 31 days'));
            $end = date('Y-m-d', strtotime($today . '+ 60 days'));
            
            $subinfo = [
                'company_name' => $uinfo->company,
                'user_id' => $id,
                'created_at' => date("Y-m-d H:i:s"),
                'subscription_id' => $uinfo->subscription_id,
                'auto_renew' => 0,
                'sub_start_date' => $today,
                'sub_end_date' => $trial_end,
                'payment' => 0
            ];

            DB::table('subscribers')->insert($subinfo);
            $id2 = DB::getPdo()->lastInsertId();
                        
            $q = DB::table('users')->where('id', $id)->update(['subscriber_id' => $id2]);
            
            DB::table('temp_signup')->where('email', $emailid)->delete();


            #1354: Super Admin Dashboard Enhancements: Start
            $supportUser = new \App\User();
            //$supportUser->title = $request->title;
            $supportUser->firstname = $uinfo->firstname;
            $supportUser->lastname = $uinfo->lastname;
            $supportUser->email = "support_1" . $id2 . "@trashscanapp.com";
            $supportUser->password = Hash::make('password');
            $supportUser->is_admin = 1;
            $supportUser->mobile = $uinfo->mobile;
            //$supportUser->timezone = $request->timezone;
            $supportUser->role_id = 1;
            $supportUser->subscriber_id = $id2;
            $supportUser->save();
            
            DB::table('role_user')->insert(['user_id' => $supportUser->id, 'role_id' => '1']);
            #1354: Super Admin Dashboard Enhancements: End
        }
        
        return view('login_welcome');
    }
    
    public function signup(Request $request)
    {
        $this->validate(
            $request,
            [
                "firstname" => 'required|string|min:3|max:100',
                "lastname" => 'required|string|min:3|max:100',
                "job_title" => 'required|string|min:3|max:100',
                "phone" => 'required|numeric',
                "email" => 'required|email',
                "company" => 'required|string|min:3'
            ]
        );
        
        $count = DB::table('users')->where('email', $request->email)->count();
        
        if ($count > 0) {
            return redirect()->back()->with('error', 'This email id already registered with us. Please login to continue.');
        } else {
            DB::table('temp_signup')->where('email', $request->email)->delete();
        
            DB::table('temp_signup')
                ->insert(
                    [
                        "firstname" => $request->firstname,
                        "lastname" => $request->lastname,
                        "job_title" => $request->job_title,
                        "mobile" => $request->phone,
                        "email" => $request->email,
                        "company" => $request->company,
                        "employee" => $request->employee,
                        "country" => $request->country,
                        "created_at" => \Carbon\Carbon::now(),
                    ]
                );
        
            session(['email' => $request->email]);
        
            return redirect('free-trial');
        }
    }
    
    public function try_for_free(Request $request)
    {
        $packId = 28; //free pack id $request->pack_id;
       
        $q = DB::table('temp_signup')
            ->where('email', session('email'))
            ->update(
                [
                    'subscription_id' => $packId
                ]
            );

        return redirect('confirmation');
    }
    
    public function subscription_pack(Request $request)
    {
        //Role id require to login and subscriber in user table
        $this->validate(
            $request,
            [
                "firstname" => 'required|string|min:3|max:100',
                "lastname" => 'required|string|min:3|max:100',
                "job_title" => 'required|string|min:3|max:100',
                "phone" => 'required|numeric',
                "email" => 'required|email',
                "company" => 'required|string|min:3'
            ]
        );
        
        $count = DB::table('users')->where('email', $request->email)->count();
        
        if ($count > 0) {
            return redirect()->back()
                ->with('error', 'This email id already registered with us. Please login to continue.');
        }
        
        $password = $request->pwd;
        
        $udata = [
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'job_title' => $request->job_title,
            'mobile' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($password),
            'company' => $request->company,
            'employee' => $request->employee,
            'country' => 'USA'
        ];

        $q = DB::table('temp_signup')->where('email', session('email'))->update($udata);
        //DB::table('users')->insert($udata);
        //DB::table('temp_signup')->where('email', $request->email)->delete();
        
        $pkinfo = DB::table('subscriptions')->where('id', '28')->first();
        $pkname = $pkinfo->package_offering;
        $today = date('d-m-Y');
        $trial_end = date('d, M, Y', strtotime($today . '+ ' . TRIAL_DAYS . ' days'));
        
        $url = url('my-login-welcome-page?uid=' . base64_encode(session('email')));
        $to = $request->email;
        $subject = 'TrashScan: Your Free Trial Successfully Subscribed!';
        $message_content = 'Greetings  <b>' . $request->firstname . ' ' . $request->lastname . '</b>,<br/><br/>
		We are delighted to inform you that your free trial of Trash Scan is just one click away.  Please click on the following link to activate your trial subscription.<br/><br/>
		<a href=' . $url . '>' . $url . '</a><br/><br/>
		<b>Company Name: ' . $request->company . '</b><br/><br/>
		<b>Login Credentials</b><br/><br/>
		Username: ' . $request->email . '
		<br/><br/>
		Package Name: <b>' . $pkname . '</b><br/>
		Trial  Start Date: <b>' . date('d, M, Y') . '</b><br/>
		Trial  End Date: <b>' . $trial_end . '</b><br/><br/>
		<b>Trial includes:</b><br/><br/>
		' . nl2br($pkinfo->features) . '<br/><br/>
		Thanks & Regards<br/>
		TrashScan Support Team.<br/>
		<img src="' . url('assets/images/logo.png') . '" height="78px" width="85px"><br/>
		Please feel free to email us at support@trashscanapp.com<br/>
		Toll Free Number: +1(800) 770-6963';
        
        $html = '
<table style="font-family:Avenir,Helvetica,sans-serif;box-sizing:border-box;background-color:#f5f8fa;padding:0;width:100%;max-width:500px;border:1px solid #aeaeae;margin:0px auto" width="100%" cellspacing="0" cellpadding="0">
   <tbody>
      <tr>
         <td style="font-family:Avenir,Helvetica,sans-serif;box-sizing:border-box" align="center">
            <table style="font-family:Avenir,Helvetica,sans-serif;box-sizing:border-box;margin:0;padding:0;width:100%" width="100%" cellspacing="0" cellpadding="0">
               <tbody>
                  <tr>
                     <td style="font-family:Avenir,Helvetica,sans-serif;box-sizing:border-box;padding:25px 0;text-align:center">
                        <a href="http://subscriber.trashscanapp.com" style="font-family:Avenir,Helvetica,sans-serif;box-sizing:border-box;color:#bbbfc3;font-size:19px;font-weight:bold;text-decoration:none" target="_blank" data-saferedirecturl="https://www.google.com/url?q=http://subscriber.trashscanapp.com&amp;source=gmail&amp;ust=1591194427226000&amp;usg=AFQjCNH4IYfzl2MTXhs4s4gXhiO1Mvdfpg">
                           <img src="https://ci4.googleusercontent.com/proxy/6BW816oZGSgsOpIXrQTclwCxH5wbza8Cw-momomju6m_a7uhZ_uAFmpJuCpMj7-j8-cmbp0uZzMF2DIJR5a7knVEQHIizN2cav5jIiaAAyoHMQ=s0-d-e1-ft#http://subscriber.trashscanapp.com/assets/images/email_logo.png" style="font-family:Avenir,Helvetica,sans-serif;box-sizing:border-box;max-width:100%;border:none" class="CToWUd" width="90" height="90">
                           <div style="font-family:Avenir,Helvetica,sans-serif;box-sizing:border-box">Trash Scan</div>
                        </a>
                     </td>
                  </tr>
                  <tr>
                     <td cellpadding="0" cellspacing="0" style="font-family:Avenir,Helvetica,sans-serif;box-sizing:border-box;background-color:#ffffff;border-bottom:1px solid #edeff2;border-top:1px solid #edeff2;margin:0;padding:0;width:100%" width="100%">
                        <table style="font-family:Avenir,Helvetica,sans-serif;box-sizing:border-box;background-color:#ffffff;margin:0 auto;padding:0;width:570px" width="570" cellspacing="0" cellpadding="0" align="center">
                           <tbody>
                              <tr>
                                 <td style="font-family:Avenir,Helvetica,sans-serif;box-sizing:border-box;padding:35px">
                                    <p style="font-family:Avenir,Helvetica,sans-serif;box-sizing:border-box;color:#74787e;font-size:16px;line-height:1.5em;margin-top:0;text-align:left">
									' . $message_content . '
									</p>
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                     </td>
                  </tr>
                  <tr>
                     <td style="font-family:Avenir,Helvetica,sans-serif;box-sizing:border-box">
                        <table style="font-family:Avenir,Helvetica,sans-serif;box-sizing:border-box;margin:0 auto;padding:0;text-align:center;width:570px" width="570" cellspacing="0" cellpadding="0" align="center">
                           <tbody>
                              <tr>
                                 <td style="font-family:Avenir,Helvetica,sans-serif;box-sizing:border-box;padding:35px" align="center">
                                    <p style="font-family:Avenir,Helvetica,sans-serif;box-sizing:border-box;line-height:1.5em;margin-top:0;color:#aeaeae;font-size:12px;text-align:center">© 2020 TrashScan.</p>
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                     </td>
                  </tr>
               </tbody>
            </table>
         </td>
      </tr>
   </tbody>
</table> ';
        
        #mail with attachment
        
        $filename = 'Mangro-SAAS-Service-Agreement-Generic.pdf';
        $path = $_SERVER['DOCUMENT_ROOT'] . '';//$_SERVER['DOCUMENT_ROOT'];
        
        $file = $path . "/" . $filename;

        $mailto = $to;
        
        

        /*$content = file_get_contents($file);
        $content = chunk_split(base64_encode($content));

        // a random hash will be necessary to send mixed content
        $separator = md5(time());

        // carriage return type (RFC)
        $eol = "\r\n";

        // main header (multipart mandatory)
        $headers = "From: TrashScanApp  <Donotreply@trashscanapp.com>" . $eol;
        $headers .= "MIME-Version: 1.0" . $eol;
        $headers .= "Content-Type: multipart/mixed; boundary=\"" . $separator . "\"" . $eol;
        $headers .= "Content-Transfer-Encoding: 7bit" . $eol;
        $headers .= "This is a MIME encoded message." . $eol;

        // message
        $body = "--" . $separator . $eol;
        $body .= "Content-Type: text/html; charset=\"iso-8859-1\"" . $eol;
        $body .= "Content-Transfer-Encoding: 8bit" . $eol;
        $body .= $message . $eol;

        // attachment
        $body .= "--" . $separator . $eol;
        $body .= "Content-Type: application/octet-stream; name=\"" . $filename . "\"" . $eol;
        $body .= "Content-Transfer-Encoding: base64" . $eol;
        $body .= "Content-Disposition: attachment" . $eol;
        $body .= $content . $eol;
        $body .= "--" . $separator . "--";*/
        Mail::raw([], function ($message) use ($mailto, $subject, $file, $html) {
            $message->to($mailto)->subject($subject);
            $message->attach($file);
            $message->setBody($html, 'text/html');
        });
        //mail($mailto, $subject, $body, $headers);
        return redirect('thanks');
    }
    
    public function auto_billing_response(Request $request)
    {        
        $payload = @file_get_contents('php://input');
        $event = json_decode($payload);
        // if($event->type == 'invoice.payment_succeeded'){
            // print_r($event);
        // }

        $udatacount = DB::table('users')->where('email', $event->data->object->customer_email)->count();
        if ($udatacount > 0) {
            $udata = DB::table('users')->where('email', $event->data->object->customer_email)->first();
            $uid = $udata->id;
            $name = $udata->firstname . ' ' . $udata->lastname;
        } else {
            $uid = '00';
            $name = 'Testing';
            $udata = new stdClass();
            $udata->trial_end = date('Y-m-d');
        }
        
        $packname  = $event->data->object->lines->data[0]->plan->product;
        $pack_arr = explode("-", $packname);
        $packid = end($pack_arr);
        
        $chkpk = DB::table('subscriptions')->where('id', $packid)->count();
        if ($chkpk > 0) {
            $packinfo = DB::table('subscriptions')->where('id', $packid)->first();
            $packid = $packinfo->id;
            $packnm = $packinfo->package_offering;
        } else {
            $packid = '0';
            $packnm = 'Testing';
        }
        
        
        if ($event->data->object->status == 'paid' && $event->data->object->paid == 'true') {
            $status = 'succeeded';
            $paystatus = '1';
        } else {
            $status = 'pending';
            $paystatus = '0';
        }
        
        $emailid = $event->data->object->customer_email;
        
        $data = ['user_id' => $uid,
            'customer_id' => $event->data->object->customer,
            'email' => $event->data->object->customer_email,
            'paid_amount' => $event->data->object->amount_paid / 100,
            'item_price_currency' => $event->data->object->currency,
            'item_number' => $packid,
            'item_name' => $packnm,
            'payment_status' => $status,
            'receipt' => $event->data->object->hosted_invoice_url,
            'payment_type' => 'recurring',
            'payment_data' => $payload,
            'created_at' => date("Y-m-d H:i:s")];

        DB::table('payment_response')->insert($data);
        
        $user_details = $udata;
        
        if (date('Y-m-d') > $user_details->trial_end) { //trial expire
            $today = date("d-m-Y");
            $sub['sub_start_date'] = date('Y-m-d');
            $sub['sub_end_date'] = date('Y-m-d', strtotime($today . '+ 30 days'));
        } else { #trial running
            $trial_end = date("d-m-Y", strtotime($user_details->trial_end));
            $sub['sub_start_date'] = date('Y-m-d', strtotime($trial_end . '+ 1 days'));
            $sub['sub_end_date'] = date('Y-m-d', strtotime($trial_end . '+ 30 days'));
        }
        $sub['subscription_id'] = $packid;
        $sub['payment'] = $paystatus;
        $q = DB::table('subscribers')->where('user_id', $uid)->update($sub);
        
        
        $amountpaid = $event->data->object->amount_paid / 100;
        
        
        $start = $sub['sub_start_date'];
        $end = $sub['sub_end_date'];
        $to = $emailid;
        $subject = 'TrashScan: Your Subscription Payment Successfully Done!';
        $message_content = 'Greetings  <b>' . $name . '</b>,<br/><br/>
		 We are delighted to inform you that your subscription payment successfully done.<br/><br/>
		 <a href=' . $event->data->object->hosted_invoice_url . '>View Invoice</a><br/><br/>
		 Package Name: <b>' . $packnm . '</b><br/>
			Amount Paid: <b>$' . $amountpaid . '</b><br/>
		  Start Date: <b>' . $start . '</b><br/>
		  End Date: <b>' . $end . '</b><br/><br/>
		  
		  <b>Your payment will be deduct automatically on due date. </b><br/><br/>
		  
		  Thanks & Regards<br/>
		TrashScan Support Team.<br/>
		<img src="' . url('assets/images/logo.png') . '" height="78px" width="85px"><br/>
		Please feel free to email us at support@trashscanapp.com<br/>
		Toll Free Number: +1(800) 770-6963';
        
        $message = '

<table style="font-family:Avenir,Helvetica,sans-serif;box-sizing:border-box;background-color:#f5f8fa;padding:0;width:100%;max-width:500px;border:1px solid #aeaeae;margin:0px auto" width="100%" cellspacing="0" cellpadding="0">
   <tbody>
      <tr>
         <td style="font-family:Avenir,Helvetica,sans-serif;box-sizing:border-box" align="center">
            <table style="font-family:Avenir,Helvetica,sans-serif;box-sizing:border-box;margin:0;padding:0;width:100%" width="100%" cellspacing="0" cellpadding="0">
               <tbody>
                  <tr>
                     <td style="font-family:Avenir,Helvetica,sans-serif;box-sizing:border-box;padding:25px 0;text-align:center">
                        <a href="http://subscriber.trashscanapp.com" style="font-family:Avenir,Helvetica,sans-serif;box-sizing:border-box;color:#bbbfc3;font-size:19px;font-weight:bold;text-decoration:none" target="_blank" data-saferedirecturl="https://www.google.com/url?q=http://subscriber.trashscanapp.com&amp;source=gmail&amp;ust=1591194427226000&amp;usg=AFQjCNH4IYfzl2MTXhs4s4gXhiO1Mvdfpg">
                           <img src="https://ci4.googleusercontent.com/proxy/6BW816oZGSgsOpIXrQTclwCxH5wbza8Cw-momomju6m_a7uhZ_uAFmpJuCpMj7-j8-cmbp0uZzMF2DIJR5a7knVEQHIizN2cav5jIiaAAyoHMQ=s0-d-e1-ft#http://subscriber.trashscanapp.com/assets/images/email_logo.png" style="font-family:Avenir,Helvetica,sans-serif;box-sizing:border-box;max-width:100%;border:none" class="CToWUd" width="90" height="90">
                           <div style="font-family:Avenir,Helvetica,sans-serif;box-sizing:border-box">Trash Scan</div>
                        </a>
                     </td>
                  </tr>
                  <tr>
                     <td cellpadding="0" cellspacing="0" style="font-family:Avenir,Helvetica,sans-serif;box-sizing:border-box;background-color:#ffffff;border-bottom:1px solid #edeff2;border-top:1px solid #edeff2;margin:0;padding:0;width:100%" width="100%">
                        <table style="font-family:Avenir,Helvetica,sans-serif;box-sizing:border-box;background-color:#ffffff;margin:0 auto;padding:0;width:570px" width="570" cellspacing="0" cellpadding="0" align="center">
                           <tbody>
                              <tr>
                                 <td style="font-family:Avenir,Helvetica,sans-serif;box-sizing:border-box;padding:35px">
                                    <p style="font-family:Avenir,Helvetica,sans-serif;box-sizing:border-box;color:#74787e;font-size:16px;line-height:1.5em;margin-top:0;text-align:left">
									' . $message_content . '
									
									</p>
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                     </td>
                  </tr>
                  <tr>
                     <td style="font-family:Avenir,Helvetica,sans-serif;box-sizing:border-box">
                        <table style="font-family:Avenir,Helvetica,sans-serif;box-sizing:border-box;margin:0 auto;padding:0;text-align:center;width:570px" width="570" cellspacing="0" cellpadding="0" align="center">
                           <tbody>
                              <tr>
                                 <td style="font-family:Avenir,Helvetica,sans-serif;box-sizing:border-box;padding:35px" align="center">
                                    <p style="font-family:Avenir,Helvetica,sans-serif;box-sizing:border-box;line-height:1.5em;margin-top:0;color:#aeaeae;font-size:12px;text-align:center">© 2020 TrashScan.</p>
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                     </td>
                  </tr>
               </tbody>
            </table>
         </td>
      </tr>
   </tbody>
</table>

';
        

        $headers = 'From: TrashScan <Donotreply@trashscanapp.com> ' . "\r\n" .
            'Reply-To: Donotreply@trashscanapp.com' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();
        $headers .= "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $sentmail = mail($to, $subject, $message, $headers);
        
        
        
        //return view('auto_billing_response');
    }
}
