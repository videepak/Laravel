<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Subscription;

class ManageSubscription extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->data['subscription'] = Subscription::latest()->get();
        return view('admin.subscriptions.subscription_list', $this->data);
    }

    public function add_subscription()
    {
        $this->data['subscription'] = '';
        return view('admin.subscriptions.add_subscription', $this->data);
    }

    public function subscription_add(Request $request)
    {

        $this->validate(
            $request, [
            'package_name' => 'required',
            'admin' => 'required|digits_between:0,8',
            'field_controller' => 'required|digits_between:0,8',
            'code_package' => 'required|digits_between:0,8',
            'price' => 'required|digits_between:0,8',
            ]
        );

        $addsub = new Subscription();
        $addsub->package_offering = $request->package_name;
        $addsub->package_admin = $request->admin;
        $addsub->package_field_collector = $request->field_controller;
        $addsub->package_qr_code = $request->code_package;
        $addsub->subscription_type = $request->type;
        $addsub->price = $request->price;


        $status = $addsub->save();

        $class = ($status) ? 'success' : 'error';
        $message = ($status) ? 'Subscription created successfully.' 
                : 'Subscription creation failed.';
        $data = array(
           'title' => 'Subscription',
            'text' => $message,
            'class' => $class
        );

        return redirect('admin/subscriptions')
                ->with('status', $data);
    }

    public function subscription_delete($id)
    {

        $checkExisting = \App\Subscriber::where('subscription_id', $id)
                ->first();
        if ($checkExisting) {
            
            $class = 'error';
            $message = 'You can not delete the subscriptions because '
                    . 'subscriptions already assgin to subscribers.';
            $data = array(
              'title' => 'Subscription',
              'text' => $message,
              'class' => $class
            );

            return redirect('admin/subscriptions')
                    ->with('status', $data);
        } else {
            $subscription = Subscription::find($id);
            $subscription->delete();

            $class = 'success';
            $message = 'Subscription deleted successfully.';
            $data = array(
               'title' => 'Subscription',
               'text' => $message,
               'class' => $class
            );

            return redirect('admin/subscriptions')
                    ->with('status', $data);
        }
    }

    public function view_subscription($id)
    {
        $this->data['subscription'] = Subscription::find($id);
        return view('admin.subscriptions.add_subscription', $this->data);
    }

    public function update_subscription(Request $request, $id)
    {

        $this->validate(
            $request, [
            'package_name' => 'required',
            'admin' => 'required|digits_between:0,8',
            'field_controller' => 'required|digits_between:0,8',
            'code_package' => 'required|digits_between:0,8',
            'price' => 'required|digits_between:0,8',
			'features' => 'required|string',
            ]
        );

        $updatesubscription = Subscription::find($id);
        $updatesubscription->package_offering = $request->package_name;
        $updatesubscription->package_admin = $request->admin;
        $updatesubscription->package_field_collector = $request->field_controller;
        $updatesubscription->package_qr_code = $request->code_package;
        $updatesubscription->subscription_type = $request->type;
        $updatesubscription->price = $request->price;
		$updatesubscription->features = $request->features;
		$updatesubscription->star_features = $request->star_features;
		$updatesubscription->plan_content = $request->plan_content;
		$updatesubscription->number_of_property = $request->number_of_property;
		
        $status = $updatesubscription->update();

        $class = ($status) ? 'success' : 'error';
        $message = ($status) ? 'Subscription updated successfully.' 
                : 'Subscription updation failed.';
        $data = array(
          'title' => 'Subscription',
          'text' => $message,
          'class' => $class
        );

        return redirect('admin/subscriptions')
                        ->with('status', $data);
    }
}
