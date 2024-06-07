<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\User;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\LogsActivity;
use App\Subscriber;
use App\Subscription;
use App\PaymentResponse;
use Session;
use App\PaymentSubscription;

class StripeController extends Controller
{
    use LogsActivity;

    public $data;
    public $user;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(
            function ($request, $next) {
                $this->data['user'] = $this->user = Auth::user();

                return $next($request);
            }
        );
    }

    public function paysubscribe(Request $request)
    {
        $uid = $request->uid;
        $email = $request->email;
        $amt = $request->amt;
        $pack_name = $request->pack_name;
        $subscription_id = $request->subscription_id;
        $token = $request->stripeToken;

        //include Stripe PHP library
        //set api key
        $stripe = array(
            'secret_key' => 'sk_live_ShbdEcTLcBCvIxKraKC0fLWn00gb9FqWxE',
            'publishable_key' => 'pk_live_xGlO6TP82vQcnwPf1zngA4XO00PN0Unt1a',
        );

        \Stripe\Stripe::setApiKey($stripe['secret_key']);

        $user = Auth::user();
        if ($user->stripe_id != '') { //get if already created stripe customer
            $custid = $user->stripe_id;
        } else {
            //create customer to stripe
            $customer = \Stripe\Customer::create(
                array(
                    'email' => $email,
                    'source' => $token,
                )
            );
            $custid = $customer->id;
            $q = DB::table('users')->where('id', $user->id)->update(['stripe_id' => $custid]);
        }

        //check plan already created
        $chk_plan = DB::table('stripe_plan')->where('subscription_id', $subscription_id)->count();

        if ($chk_plan > 0) {
            $get_plan = DB::table('stripe_plan')->where('subscription_id', $subscription_id)->first();
            $planid = $get_plan->stripe_plan_id;
        } else {
            $cr_plan = \Stripe\Plan::create(array(
                'amount' => $amt,
                'interval' => 'month',
                'product' => array(
                    'name' => $request->pack_name,
                    'id' => 'Trashcanliveapp-' . $subscription_id,
                ),
                'currency' => 'usd',
              ));
            $planid = $cr_plan->id;
            DB::table('stripe_plan')->insert(['subscription_id' => $subscription_id, 'stripe_plan_id' => $planid, 'stripe_plan_name' => $pack_name]);
        }

        //item information
        $itemName = $pack_name;
        $itemNumber = $subscription_id;
        $itemPrice = $amt;
        $currency = 'usd';
        if ($user->stripe_subscriber_id != '') { //subscriber payment manually
             // $stripe_subscriber = \Stripe\Subscription::update([
                  // $user->stripe_subscriber_id,
                  // 'items' => [['plan' => $planid]],
                  // 'expand' => ['latest_invoice.payment_intent'],
            // ]);

            // $pay = new PaymentResponse();
            // $pay->user_id = $uid;

            // $pay->customer_id = $custid;
            // $pay->email = $stripe_subscriber->latest_invoice->customer_email;
            // $pay->paid_amount = $stripe_subscriber->latest_invoice->payment_intent->amount_received/100;

            // $pay->item_price_currency = $stripe_subscriber->latest_invoice->currency;
            // $pay->item_name = $itemName;
            // $pay->item_number = $itemNumber;
            // $pay->payment_status = $stripe_subscriber->latest_invoice->payment_intent->charges->data[0]->status;
            // $pay->receipt = $stripe_subscriber->latest_invoice->hosted_invoice_url;
            // $pay->payment_type = 'subscriber_updated';
            // $pay->payment_data = $stripe_subscriber;
            // $pay->save();

            // #subscribe detail update
            // $user_details = Auth::user();
            // $subscriber_details = Subscriber::where('user_id', $user_details->id)->first();
            // $subscriber_details->updated_at = date("Y-m-d H:i:s");
            // $subscriber_details->subscription_id = $subscription_id;
            // $subscriber_details->payment = '1';

            // $today = date('Y-m-d');

            // $subscriber_details->sub_start_date = $today;
            // $subscriber_details->sub_end_date = date('Y-m-d', strtotime($today .'+ 30 days'));
            // $subscriber_details->save();
        } else {  //create subscriber to recurring payment
            $stripe_subscriber = \Stripe\Subscription::create([
                  'customer' => $custid,
                  'items' => [['plan' => $planid]],
                  'expand' => ['latest_invoice.payment_intent'],
            ]);

            $xq2 = DB::table('users')->where('id', $user->id)->update(['stripe_subscriber_id' => $stripe_subscriber->id]);

            $pay = new PaymentResponse();
            $pay->user_id = $uid;

            $pay->customer_id = $custid;
            $pay->email = $stripe_subscriber->latest_invoice->customer_email;
            $pay->paid_amount = $stripe_subscriber->latest_invoice->payment_intent->amount_received / 100;

            $pay->item_price_currency = $stripe_subscriber->latest_invoice->currency;
            $pay->item_name = $itemName;
            $pay->item_number = $itemNumber;
            $pay->payment_status = $stripe_subscriber->latest_invoice->payment_intent->charges->data[0]->status;
            $pay->receipt = $stripe_subscriber->latest_invoice->hosted_invoice_url;
            $pay->payment_type = 'subscriber_created';
            $pay->payment_data = $stripe_subscriber;
            $pay->save();

            //subscribe detail update
            $user_details = Auth::user();
            $subscriber_details = Subscriber::where('user_id', $user_details->id)->first();
            $subscriber_details->updated_at = date('Y-m-d H:i:s');
            $subscriber_details->subscription_id = $subscription_id;

            if ($stripe_subscriber->latest_invoice->payment_intent->charges->data[0]->status == 'succeeded' && $stripe_subscriber->latest_invoice->payment_intent->charges->data[0]->captured == 'true') {
                $subscriber_details->payment = '1';
            } else {
                $subscriber_details->payment = '0';
            }

            $today = date('Y-m-d');

            if ($today > $user_details->trial_end) { //trial expire
                $trial_end = date('d-m-Y');

                $subscriber_details->sub_start_date = date('Y-m-d');
                $subscriber_details->sub_end_date = date('Y-m-d', strtotime($trial_end . '+ 30 days'));
            } else { //trial running
                $trial_end = date('d-m-Y', strtotime($user_details->trial_end));

                $subscriber_details->sub_start_date = date('Y-m-d', strtotime($trial_end . '+ 1 days'));
                $subscriber_details->sub_end_date = date('Y-m-d', strtotime($trial_end . '+ 30 days'));
            }
            $subscriber_details->save();
        }

        $q2 = DB::table('users')->where('id', $user->id)->update(['plan_id' => $planid]);

        //$subscriber_id = $subscriber_details->id;

        return redirect('/thanks-for-payment/' . base64_encode($pay->id));
    }

    public function paynow(Request $request)
    {
        /* creating a plan
          \Stripe\Stripe::setApiKey("sk_test_3PICx6hPwQo5b00Ve8bzALqp");

          \Stripe\Plan::create(array(
          "amount" => 5000,
          "interval" => "month",
          "product" => array(
          "name" => "Ruby full"
          ),
          "currency" => "usd",
          "id" => "ruby-full"
          )); */

        //dd($_REQUEST); die;

        $uid = $request->uid;
        $email = $request->email;
        $amt = $request->amt;
        $pack_name = $request->pack_name;
        $subscription_id = $request->subscription_id;
        $token = $request->stripeToken;
        /* $card_num =     $request->card_num;
          $card_cvc =     $request->cvc;
          $card_exp_month=$request->exp_month;
          $card_exp_year =$request->exp_year; */

        //include Stripe PHP library
        //set api key
        $stripe = array(
            'secret_key' => 'sk_test_3PICx6hPwQo5b00Ve8bzALqp',
            'publishable_key' => 'pk_test_wlR8UBPOmQo5qfO4NzNUIPVo',
        );

        \Stripe\Stripe::setApiKey($stripe['secret_key']);

        //add customer to stripe
        $customer = \Stripe\Customer::create(
            array(
                'email' => $email,
                'source' => $token,
            )
        );

        /* To retrive the details of card  *
          $refund = \Stripe\Charge::retrieve(
          "ch_1CDSSdLzn6tidxMkKpr8NSox",
          array('api_key' => "sk_test_3PICx6hPwQo5b00Ve8bzALqp")
          );
          print_r($refund);die;

         */

        //item information
        $itemName = $pack_name;
        $itemNumber = $subscription_id;
        $itemPrice = $amt;
        $currency = 'usd';

        //charge a credit or a debit card
        $charge = \Stripe\Charge::create(
            array(
                'customer' => $customer->id,
                'amount' => $itemPrice,
                'currency' => $currency,
                'description' => $itemName,
                'metadata' => array(),
            )
        );

        //retrieve charge details
        $chargeJson = $charge->jsonSerialize();
        $amount_refunded = $chargeJson['amount_refunded'];
        $failure_code = $chargeJson['failure_code'];
        $paid = $chargeJson['paid'];
        $captured = $chargeJson['captured'];

        //check whether the charge is successful
        if ($amount_refunded == 0 && empty($failure_code) && $paid == 1 && $captured == 1) {
            //order details
            $amount = $chargeJson['amount'];
            $balance_transaction = $chargeJson['balance_transaction'];
            $currency = $chargeJson['currency'];
            $status = $chargeJson['status'];
            $date = date('Y-m-d H:i:s');

            $user_details = Auth::user();
            $subscriber_details = Subscriber::where('user_id', $user_details->id)
                    ->first();

            $subscriber_details->payment = '1';
            $subscriber_details->sub_start_date = date('Y-m-d');
            $subscriber_details->sub_end_date = date('Y-m-d', strtotime('+30 days'));
            $subscriber_details->save();
            $subscriber_id = $subscriber_details->id;

            if (!empty($subscriber_id)) {
                //insert tansaction data into the database
                $pay = new PaymentResponse();
                $pay->user_id = $uid;
                $pay->order_no = time();
                $pay->customer_id = $customer->id;
                $pay->email = $email;
                $pay->paid_amount = $amt;
                $pay->txn_id = $token;
                $pay->item_price_currency = $charge['currency'];
                $pay->item_name = $charge['description'];
                $pay->item_number = $itemNumber;
                $pay->payment_status = $charge['status'];
                $pay->save();

                Session::flash('message', 'Payment Successfull!');
                $user_details = Auth::user();
                $subscriber_details = Subscriber::where('user_id', $user_details->id)
                        ->first();
                $subscribtion_details = Subscription::find($subscriber_details->subscription_id);

                return redirect('/home')
                        ->with(compact('subscriber_details', 'subscribtion_details', 'user_details'));
            }
        }
    }

    public function renew(Request $request)
    {
        \Stripe\Stripe::setApiKey('sk_test_3PICx6hPwQo5b00Ve8bzALqp');
        $ch = \Stripe\Charge::retrieve('ch_1CNIfgLzn6tidxMk0ljAHI7K');
        $ch->description = 'Charge for ava.harris@example.com';
        $ch->save();
    }

    /* public function renewSubs(Request $request) {



      try {

      \Stripe\Stripe::setApiKey('sk_test_3PICx6hPwQo5b00Ve8bzALqp');
      $user_details = Auth::user();
      //dd($user_details);
      $user_details->newSubscription('ruby-full', 'ruby-full')->create($request->stripeToken);







      /*$pay = new PaymentSubscription();
      $pay->stripe_id = 'ruby-full';
      $pay->save(); */

    /*     return 'Subscription successful, you get the course!';
      } catch (\Exception $ex) {
      return $ex->getMessage();
      }
      } */
}
