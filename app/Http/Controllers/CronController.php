<?php

namespace App\Http\Controllers;

use App\Notifications\EmailTemplate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class CronController extends BaseController
{
    public function newViolationListForPropertyManager()
    {
        $user = \App\User::where('role_id', 10)->get();
        $count = [];
        foreach ($user as $udetail) {
            $count = \App\Violation::whereIn(
                'barcode_id',
                function ($query) use ($udetail) {
                    $query->select('barcode_id')
                        ->from('units')
                        ->whereIn(
                            'property_id',
                            function ($query) use ($udetail) {
                                $query->select('property_id')
                                    ->from('user_properties')
                                    ->where('user_id', $udetail->id)
                                    ->whereNull('deleted_at')
                                    ->groupBy('property_id');
                            }
                        )
                        ->where('is_active', 1)
                        ->orderBy('barcode_id')
                        ->whereNull('deleted_at');
                }
            )
            ->where('created_at', '<=', Carbon::now())
            ->where('status', 0)
            //->with('getReason', 'getUser', 'getUnitNumber')
            ->with(
                [
                    'getReason',
                    'getUser',
                    'getUnitNumber' => function ($query) {
                        $query->where('is_route', 0);
                    }
                ]
            )
            ->latest()
            ->get();

            $rcount = 1;

            if ($count->isNotEmpty()) {
                $content = '<p>Check voilation list</p>';
                $content .= '<div style="overflow-x:scroll">'
                        . '<table width="100%"><tr>'
                        . '<th>S.No</th>'
                        . '<th>Username</th>'
                        . '<th>Violation Reason</th>'
                        . '<th>Violation Action</th>'
                        . '<th>Unit Number</th>'
                        . '<th>Updated At</th>'
                        . '<th>Status</th>'
                        . '</tr>';
                foreach ($count as $voi) {
                    $content .= '<tr>'
                        . '<td style="text-align:center">' . $rcount . '</td>'
                        . '<td style="text-align:center">' . $udetail->firstname
                        . ' ' . $udetail->lastname . '</td>'
                        . '<td style="text-align:center">' . $voi->getReason->reason . '</td>'
                        . '<td style="text-align:center">' . $voi->violation_action . '</td>'
                        . '<td style="text-align:center">' . $voi->getUnitNumber->unit_number . ' </td>'
                        . '<td style="text-align:center">' . $voi->updated_at->format('d-m-Y H:i:s') . '</td>'
                        . '<td style="text-align:center">New</td>'
                        . '</tr>';
                }
                $content .= '</table> '
                        . '</div>';

                try {
                    $udetail->notify(new EmailTemplate($content, 'Welcome to Trash Scan'));
                } catch (\Exception $e) {
                    //echo 'Message: ' .$e->getMessage();
                }
            }
        }
    }

    public function getCountOlderday()
    {
        $start = \Carbon\Carbon::parse($this->usertime->startTime)
                ->subDays('1');
        $end = \Carbon\Carbon::parse($this->usertime->endTime)
                ->subDays('1');

        $count = $this->propertyList()
            ->withCount(
                [
                    'getViolationByProperties' => function ($query) use ($start, $end) {
                        $query->whereBetween(
                            \DB::raw("convert_tz(violations.updated_at,'UTC','" . getUserTimezone() . "')"),
                            [
                                $start,
                                $end,
                            ]
                        )
                        ->where('status', 2);
                    },
                ]
            )
        ->with('getPropertyManger')
        ->get();

        foreach ($count as $counts) {
            if (!empty($counts->get_violation_by_properties_count)) {
                $content = '' . $counts->get_violation_by_properties_count . ' violations are open.';
                $user = $counts->getPropertyManger;

                try {
                    \Notification::send($user, new \App\Notifications\NewViolation($content));
                } catch (\Exception $e) {
                    //echo 'Message: ' .$e->getMessage();
                }
            }
        }
    }

    public function insertUserTable()
    {
        $arr = [];
        $customer = \App\Customer::whereNotIn(
            'id',
            function ($query) {
                $query->select('customer_id')
                    ->from('users')
                    ->whereNull('deleted_at')
                    ->whereNotNull('customer_id')
                    ->groupBy('customer_id');
            }
        )
        ->get();

        foreach ($customer as $customers) {
            $count = \App\User::where('email', $customers->email)->get();

            if ($count->isEmpty()) {
                $user = new \App\User();

                //$mobile = "+1" . $request->mobile . "";
                $rawPassword = $this->generatePassword();
                $password = Hash::make($rawPassword);

                $user->firstname = $customers->name;
                $user->lastname = $customers->lastname;
                $user->email = $customers->email;
                $user->mobile = $customers->phone;
                $user->customer_id = $customers->id;
                $user->device_token = '';
                $user->platform = '';
                $user->is_admin = 0;

                $user->subscriber_id = $customers->subscriber_id;
                $user->password = $password;

                $user->role_id = 10;
                $status = $user->save();
                $user->attachRole(10);

                $customerPropertyId = \App\Property::select('id', 'customer_id')
                    ->where('customer_id', $customers->id)
                    ->get();

                if ($customerPropertyId->isNOtEmpty()) {
                    foreach ($customerPropertyId as $customerPropertyIds) {
                        $userId = \App\User::select('id')
                                ->where('customer_id', $customerPropertyIds->customer_id)
                                ->first();

                        $property = \App\UserProperties::where(
                            [
                                'property_id' => $customerPropertyIds->id,
                                'user_id' => $userId->id,
                            ]
                        )
                        ->get();

                        if ($property->isEmpty()) {
                            $user = new \App\UserProperties();
                            $user->property_id = $customerPropertyIds->id;
                            $user->user_id = $userId->id;
                            $user->save();
                        }
                    }
                }

                $a = $userId->roles()
                        ->where('role_id', 10)->get();

                if ($a->isEmpty()) {
                    $userId->attachRole(10);
                }
            } else {
                $arr[] = $customers->email;
            }
        }
        dd($arr);
    }

    public function generatePassword()
    {
        $seed = str_split(
            'abcdefghijklmnopqrstuvwxyz'
            . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
            . '0123456789'
        );
        // and any other characters
        shuffle($seed);
        // probably optional since array_is randomized; this may be redundant
        $rand = '';
        foreach (array_rand($seed, 6) as $k) {
            $rand .= $seed[$k];
        }

        return $rand;
    }
}
