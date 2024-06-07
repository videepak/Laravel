<?php

use Edujugon\PushNotification\PushNotification;
use Pushok\AuthProvider;
use Pushok\Client as PClient;
use Pushok\Notification;
use Pushok\Payload;
use Pushok\Payload\Alert;
use Twilio\Rest\Client;

if (!function_exists('createExtension')) {
    function createExtension()
    {
        // The length we want the unique reference number to be
        $unique_ref_length = 4;

        // A true/false variable that lets us know if we've
        // found a unique reference number or not
        $unique_ref_found = false;

        // Define possible characters.
        // Notice how characters that may be confused such
        // as the letter 'O' and the number zero don't exist
        $possible_chars = '1234567890';

        // Until we find a unique reference, keep generating new ones
        while (!$unique_ref_found) {
            // Start with a blank reference number
            $unique_ref = '';

            // Set up a counter to keep track of how many characters have
            // currently been added
            $i = 0;

            // Add random characters from $possible_chars to $unique_ref
            // until $unique_ref_length is reached
            while ($i < $unique_ref_length) {
                // Pick a random character from the $possible_chars list
                $char = substr($possible_chars, mt_rand(0, strlen($possible_chars) - 1), 1);

                $unique_ref .= $char;

                ++$i;
            }

            // Our new unique reference number is generated.
            // Lets check if it exists or not
            //$query = "SELECT `order_ref_no` FROM `orders` WHERE `order_ref_no`='".$unique_ref."'";
            $query = DB::table('users')->select('extension')
                    ->where('extension', $unique_ref)->get();

            if (count($query) == 0) {
                // We've found a unique number. Lets set the $unique_ref_found
                // variable to true and exit the while loop
                $unique_ref_found = true;
            }
        }

        return $unique_ref;
    }
}

function days()
{
    $days = [
        '0' => 'Monday',
        '1' => 'Tuesday',
        '2' => 'Wednesday',
        '3' => 'Thursday',
        '4' => 'Friday',
        '5' => 'Saturday',
        '6' => 'Sunday',
    ];

    return $days;
}

    //Sms function
function sms($mobile, $text = '')
{
    try {
        $sid = 'ACd3e62ed1a09b11e1f2b7aabc2e5d6cf1';
        $token = '2919f8b986b3d0ede4710cf596444684';
        $client = new Client($sid, $token);

        // $account = $client->api->accounts($sid)->fetch();
        // dd($account);

        if ($text == '') {
            $text = 'Welcome To Trashscan';
        }

        // Use the client to do fun stuff like send text messages!
        $client->messages->create(
            $mobile, // the number you'd like to send the message to
            [
                'from' => '+19727162972', //A Twilio phone number you purchased at twilio.com/console
                'body' => $text, // the body of the text message you'd like to send
            ]
        );

        return true;
    } catch (Exception $e) {
        //echo 'Message: ' . $e->getMessage();
    }
}

//Timezone paramenter (this is show on user profile, add/edit employee etc.)
function selectTimezone()
{
    $timezone = [
        'America/New_York' => 'EST (UTC-5:00 Hrs)',
        'America/Chicago' => 'CST (UTC-6:00 Hrs)',
        'America/Denver' => 'MST (UTC-7:00 Hrs)',
        'America/Los_Angeles' => 'PST (UTC-8:00 Hrs)',
        ];

    return $timezone;
}

    //Property Manger Permission (this is show on add/edit property manager.)
function managerPremission()
{
    $permission = ['6' => 'Manage Violation', '5' => 'Service Report', '7' => 'Detail Report'];

    return $permission;
}

function getUserTimezone()
{
    if (!empty(\Auth::user()->timezone)) {
        $tz = \Auth::user()->timezone;
    } else {
        $tz = 'America/New_York';
    }

    return $tz;
}

function getStartEndTime()
{
    $timezone = \Carbon\Carbon::now()->timezone(getUserTimezone())->subHours(6);
    $startDate = $timezone->format('Y-m-d') . ' 06:00:00';
    $endDate = $timezone->addDays(1)->format('Y-m-d') . ' 05:59:59';

    $time = (object) ['startTime' => $startDate, 'endTime' => $endDate];

    return $time;
}

function getPropertyManageId()
{
    //Get Property Manager Id from role model

    $role = \App\Role::where('name', 'property_manager')->first();

    return $role->id;
}

function getAdminId()
{
    //Get Admin Id from role model

    $role = \App\Role::where('name', 'admin')->first();

    return $role->id;
}

function paginateOffset($page, $perpage)
{
    $offset = ($page - 1) * $perpage + 1;

    return $offset;
}

function getLogo($subscriberId = '')
{
    //Dynamic logo and copy right according to subscriber:Start
    $subscriberId = isset(\Auth::user()->subscriber_id) ? \Auth::user()->subscriber_id : $subscriberId;

    $subscriber = \App\Subscriber::find($subscriberId);

    if (isset($subscriber->company_logo) && !empty($subscriber->company_logo)) {
        $logo = url('uploads/user/' . $subscriber->company_logo . '');
        $companyName = $subscriber->company_name;
    } else {
        $logo = url('assets/images/logo.png');
        $companyName = config('app.name');
    }

    $data = ['logo' => $logo, 'companyName' => $companyName];

    return $data;

    //Dynamic logo and copy right according to subscriber:End
}

function logoPublicPath($subscriberId = '')
{
    //Dynamic logo and copy right according to subscriber:Start
    $subscriberId = isset(\Auth::user()->subscriber_id) ? \Auth::user()->subscriber_id : $subscriberId;

    $subscriber = \App\Subscriber::find($subscriberId);

    if (isset($subscriber->company_logo) && !empty($subscriber->company_logo)) {
        $logo = url('uploads/user/' . $subscriber->company_logo . '');
        $companyName = $subscriber->company_name;
    } else {
        $logo = public_path('assets/images/logo.png');
        $companyName = config('app.name');
    }

    $data = ['logo' => $logo, 'companyName' => $companyName];

    return $data;

    //Dynamic logo and copy right according to subscriber:End
}

function isNotRollbackViolation($id)
{
    //Remove rollback violation:Start.
    $vio = \App\Violation::where('id', $id)
            ->whereNotIn('status', [1])->get();

    return $vio->isNotEmpty() ? true : false;
    //Remove rollback violation:End.
}

function routeCheckPoint()
{
    return [
        'XE7W4YGDN5','4BDLDRG7Z6','P9EWJQGY8N','V4PLNXWJO8','K2VW94GXJ3','POAGKQGYD8','8VJG2KLYEQ','A5NW3KWKQE','KVRWVQG47B','PYEGPVG947','P29L8EW568','86NLR6GEMY','ONEG5RW8KQ','4QBWZBWYJO','35AWY4LVEP','PQYLE2G6NR','RJ2G7NWA5Q','YJXG6MW54O','KZYGM8GRA5','XK2LAOG37N','RDELO9GBV7','7DJLQKL5OE','Y98GXYLJNV','OA3GBXALP8','XE7W4YYGDN','4BDLD3RW7Z','P9EWJ6QGY8','V4PLNEXLJO','K2VW934WXJ','POAGKYQWYD','8VJG2RKWYE','A5NW3DKLKQ','KVRWVNQG47','PYEGP3VL94','P29L8ZEW56','86NLRB6GEM','ONEG5NRL8K','4QBWZ8BLYJ','35AWYA4WVE','PQYLEZ2L6N','RJ2G78NLA5','YJXG65MW54','KZYGMX8WRA','XK2LA9OW37','RDELOO9LBV','7DJLQMKG5O','Y98GX8YLJN','OA3GBZAGP8','XE7W4ZYLDN','4BDLDRRG7Z','P9EWJYQWY8','V4PLNOXWJO','K2VW924LXJ','POAGKMQWYD','8VJG24KWYE','A5NW3QKWKQ','KVRWVEQG47','PYEGPOVW94','P29L86EW56','86NLRO6LEM','ONEG5JRG8K','4QBWZ3BWYJ','35AWY94GVE','PQYLEK2L6N','RJ2G79NWA5','YJXG6NMG54','KZYGMO8WRA','XK2LA7OG37','RDELOY9WBV','7DJLQRKW5O','Y98GXNYWJN','OA3GB2AWP8','XE7W4DYGDN','4BDLD8RL7Z','P9EWJQQGY8','V4PLNVXGJO','K2VW9Y4LXJ','POAGKOQLYD','8VJG2BKLYE','A5NW34KGKQ','KVRWVYQW47','PYEGP5VG94','P29L8KEW56','86NLR56WEM','ONEG53RL8K','4QBWZ4BGYJ','35AWYB4GVE','PQYLEY2G6N','RJ2G7XNGA5','YJXG6YMW54','KZYGM38LRA','XK2LAXOG37','RDELOP9WBV','7DJLQ7KW5O','Y98GX6YWJN','OA3GBKAGP8','XE7W46YLDN','4BDLD5RL7Z','P9EWJ8QWY8','OA3GBKKGP8','XE7W465LDN','4BDLD59L7Z','P9EWJ8DWY8','V4PLNX4WJO','K2VW9DRLXJ','POAGKZNGYD','8VJG2OAWYE','A5NW3R8GKQ','KVRWV6KG47','PYEGP6NL94','P29L845G56','86NLRQNGEM','ONEG5XDW8K','4QBWZK2WYJ','35AWY6NGVE','PQYLE5OG6N','RJ2G7BQWA5','YJXG694G54','KZYGM5EGRA','XK2LA5AL37','RDELOMOWBV','7DJLQQRL5O','Y98GXE6WJN','OA3GBMKWP8'
    ];
}

function getCurrentDay()
{
    $time = \Carbon\Carbon::now();
    $converted_to_timezone = \Carbon\Carbon::now()->setTimezone(getUserTimezone());
    $currentDay = $converted_to_timezone->subHours(6)
            ->format('l');

    switch ($currentDay) {
        case 'Monday':
            return 0;
            break;
        case 'Tuesday':
            return 1;
            break;
        case 'Wednesday':
            return 2;
            break;
        case 'Thursday':
            return 3;
            break;
        case 'Friday':
            return 4;
            break;
        case 'Saturday':
            return 5;
            break;
        case 'Sunday':
            return 6;
            break;
    }
}

    /**
     * IOS Push.
     *
     * Tasks: #1094
     *
     * Notification Type (In Payload var): 1= Clock-in,2= Clock-out; 3= Still working, 4 = Reporting * Manager.
     *
     * @param
     *   key_id  (The Key ID obtained from Apple developer account.)
     *   team_id // The Team ID obtained from Apple developer account.)
     *   app_bundle_id // The bundle ID for app obtained from Apple developer account.)
     *   private_key_path //(Storage_path('AuthKey_V9GB55PQ7B.p8')), // Path to private key.)
     *   private_key_secrett //(Private key secret.)
     */
function iosPush($title, $message, array $deviceToken, array $load = [])
{
    $options = [
        'key_id' => env('APN_KEY_ID'),
        'team_id' => env('APN_TEAM_ID'),
        'app_bundle_id' => env('APN_APP_BUNDLE_ID'),
        'private_key_path' => env('APN_PRIVATE_KEY', storage_path('AuthKey_V9GB55PQ7B.p8')),
        'private_key_secret' => null,
    ];

    $load = empty($load) ? ['payload1' => 'value1'] : $load;

    $authProvider = AuthProvider\Token::create($options);

    $alert = Alert::create()->setTitle($title);

    $alert = $alert->setBody($message);

    $payload = Payload::create()->setAlert($alert);

    //Set notification sound to default
    $payload->setSound('default');

    //Add custom value to your notification, needs to be customized
    $payload->setCustomValue('data', $load);

    $deviceTokens = $deviceToken;

    $notifications = [];

    foreach ($deviceTokens as $deviceToken) {
        $notifications[] = new Notification($payload, $deviceToken);
    }

    $client = new PClient($authProvider, $production = env('IOS_PRODUCTION'));

    $client->addNotifications($notifications);

    $responses = $client->push();
}

    /**
     * IOS Push.
     *
     * Tasks: #1094
     *
     * Notification Type (In Payload var): 1= Clock-in,2= Clock-out; 3= Still working, 4 = Reporting * Manager.
     *
     * //$push->setDevicesToken(["dJDbtUzKxt8:APA91bEecVRqtQh-WJwgeqij7FigKEyPrb7rYs_e_Fi-WcQimDmkW3yjBbTUEQkRdj97WgaVLpkIcSeW0ySn074kpBTgAnmY5DoxrT6UdnfByykcuA-VOccEgamTmikmxuIC9cGmSEbg", "cLlyEvwiB2s:APA91bE0ww5ahjIF_6-V6nfPoScKjOYlS5HVHJF9CW93qNTBaDgFLlN3mUbXZ10PVialVjqJdvjToiUuKWrhshsJ3B_4LU0NdXYv3KJ1SosTsTGkG1YF-VkdGycuu7BInSKvTyTSqDYI"])->send();
     */
function androidPush($title, $message, array $deviceToken, array $payload = [])
{
    $push = new PushNotification('fcm');
    $payload = empty($payload) ? ['payload1' => 'value1'] : $payload;

    $push->setMessage(
        [
           // 'notification' => [
           //      'title' => $title,
           //      'body' => $message,
           //      'sound' => 'default'
           //  ],
           'data' => $payload,
        ]
    );

    $push->setDevicesToken($deviceToken)->send();

    //dd($push->getFeedback());
}

function automatedServiceReport($users, $frequency)
{
    foreach ($users as $user) {
        $i = 0;
        $logsArray = [];
        $getProperties = $user->getProperties;
        $getEmployees = $user->employees;

        if ($getProperties->isEmpty() || $getEmployees->isEmpty()) {
            continue;
        }

        $admins = $getEmployees->where('role_id', 1);
        $adminRole = $getEmployees
            ->filter(
                function ($value, $key) {
                    return $value->role_id == 1 &&  is_null($value->deleted_at);
                }
            );
        
        foreach ($adminRole as $admin) {
            $hashSubscriberId = \Hashids::encode($admin->id);
            $checkUserNotify = \App\UserNotification::select('id')
                ->where('subscriber_id', $admin->subscriber_id)
                ->where('user_id', $admin->id)
                ->where('type', 7)
                ->first();
            
            $userNotify = \App\UserNotification::select('user_id', 'day_frequency', 'type')
                ->where('subscriber_id', $admin->subscriber_id)
                ->where('user_id', $admin->id)
                ->when(
                    $frequency == 'Daily',
                    function ($query) {
                        $query->where('day_frequency', 1);
                    }
                )
                ->when(
                    $frequency == 'Monthly',
                    function ($query) {
                        $query->where('day_frequency', 3);
                    }
                )
                ->when(
                    $frequency == 'Weekly',
                    function ($query) {
                        $query->where('day_frequency', 2);
                    }
                )
                ->where('type', '=', 7)
                ->where('email', 1)
                ->first();
            
            if (!is_null($userNotify) || is_null($checkUserNotify)) {
                $timezone = !empty($admin->timezone) ? $admin->timezone : 'America/New_York';

                switch ($timezone) {
                    case "America/Los_Angeles":
                        $reportTime = 'PST (UTC-8:00 Hrs)';
                        break;
                    case "America/Chicago":
                        $reportTime =  "CST (UTC-6:00 Hrs)";
                        break;
                    case "America/Denver":
                        $reportTime =  "MST (UTC-7:00 Hrs)";
                        break;
                    default:
                        $reportTime = "EST (UTC-5:00 Hrs)";
                }
                
                $now = \Carbon\Carbon::now()->timezone($timezone)->format('Y-m-d');

                $logsArray[] = [
                    'S.No', 'Property', 'Building Name', 'Unit',
                    'Scan Date', 'Volume','Activity', 'Scan By',
                    'Property Timezone'
                ];
                
                if (is_null($checkUserNotify) || $userNotify->day_frequency == 1) {
                    $endTime = $now . ' 05:59:59';
                    
                    $startTime = \Carbon\Carbon::parse($endTime)->subDays(1)->copy()->format('Y-m-d') . ' 06:00:00';
                    
                    $date = \Carbon\Carbon::parse($endTime)->subDays(1)->copy()->format('F, d Y');
                    
                    $reportName = "DailyServiceReport-$hashSubscriberId";
                } elseif ($userNotify->day_frequency == 2) {
                    $startTime = \Carbon\Carbon::now()->timezone($timezone)->startOfWeek()->subWeek()->addHours(6);
                    
                    $endTime = \Carbon\Carbon::now()->timezone($timezone)->startOfWeek()->addHours(5)->addMinutes(59)->addSeconds(59);
                    
                    $date = \Carbon\Carbon::parse($startTime)->copy()->format('F, d Y') . ' - ' .    \Carbon\Carbon::parse($endTime)->copy()->format('F, d Y');
                
                    $reportName = "WeeklyServiceReport-$hashSubscriberId";
                } elseif ($userNotify->day_frequency == 3) {
                    $startPreviousMonth = new \Carbon\Carbon('first day of last month', $timezone);
                
                    $startCurrentMonth = new \Carbon\Carbon('first day of this month', $timezone);
                
                    $startTime =  \Carbon\Carbon::parse($startPreviousMonth)->copy()->format('Y-m-d') . ' 06:00:00';
                
                    $endTime = \Carbon\Carbon::parse($startCurrentMonth)->copy()->format('Y-m-d') . ' 05:59:59';
        
                    $date = \Carbon\Carbon::now()->timezone($timezone)->subMonth()->format('F, Y');
                    
                    $reportName = "MonthlyServiceReport-$hashSubscriberId";
                }
                
                $barcode = \App\Units::select('barcode_id')
                    ->where(
                        function ($query) {
                            $query->where(
                                function ($query) {
                                    $query->where('is_route', 0)
                                        ->where('is_active', 1);
                                }
                            )
                            ->orWhere(
                                function ($query) {
                                    $query->where('is_route', 1);
                                }
                            );
                        }
                    )
                    ->whereIn('property_id', $getProperties->pluck('id'))
                    ->withTrashed()
                    ->get()->map(
                        function ($val, $key) {
                            return $val->barcode_id;
                        }
                    );
              
                $totalLog = \App\Activitylogs::where(
                    function ($query) use ($getEmployees) {
                        $query->whereIn('user_id', $getEmployees->pluck('id'))
                                ->orWhereIn('updated_by', $getEmployees->pluck('id'));
                    }
                )
                    ->where(
                        function ($query) use ($barcode, $getProperties) {
                            //$query->whereIn('barcode_id2', $barcode)
                            $query->whereRaw("barcode_id in (select `barcode_id` from `units` where `property_id` in (" . collect($getProperties->pluck('id'))->implode(', ') . ") and `is_active` = 1)")
                            ->orWhereIn('property_id', $getProperties->pluck('id'));
                        }
                    )
                    ->whereIn('type', [2, 3, 6, 8, 5, 11])
                    ->whereBetween(
                        \DB::raw("convert_tz(updated_at, 'UTC','" . $timezone . "')"),
                        [
                            $startTime,
                            $endTime,
                        ]
                    )
                    ->with(
                        [
                            'getUserDetail' => function ($query) {
                                $query->select('id', 'title', 'firstname', 'lastname', 'mobile', 'role_id', 'subscriber_id', 'user_id')
                                    ->withTrashed();
                            },
                            'unit' => function ($query) {
                                $query->select('id', 'unit_number', 'property_id', 'building_id', 'barcode_id', 'created_at', 'updated_at')
                                   // ->where('is_active', '1')
                                    ->with(
                                        [
                                            'getBuildingDetail' => function ($query) {
                                                $query->select('id', 'building_name', 'property_id')
                                                    ->withTrashed();
                                            },
                                        ]
                                    )
                                    ->withTrashed();
                            },
                            'getProperty' => function ($query) {
                                $query->select('id', 'units', 'name', 'type')
                                    ->with(
                                        [
                                            'service' => function ($query) {
                                                $query->select('id', 'recycle_weight', 'waste_weight', 'waste_reduction_target', 'recycling', 'property_id')
                                                    ->withTrashed();
                                            },
                                        ]
                                    )
                                    ->withTrashed();
                            }
                        ]
                    )
                    ->withTrashed()
                    ->latest()
                    ->get();
                        
                foreach ($totalLog as $log) {
                    $propertyName = $propertyId = $buildingName = $type = '';
                    $userInfoByUserId = $log->getUserDetail;
                    $property = $log->getProperty;
                    $building = !empty($log->unit->getBuildingDetail) ? $log->unit->getBuildingDetail : '';
                
                    if ($log->type == 11) {
                        $units = \App\Units::where('barcode_id', $log->barcode_id)
                            ->withTrashed()->first();

                        if (isset($property->name)) {
                            $propertyName = $property->name;
                        }
                
                        if (!is_null($units)) {
                            $buildingName = !empty($units->getBuilding->building_name) ? $units->getBuilding->building_name : '-';
                        }
                    } elseif ($log->type == 3) {
                        $vio = \App\Violation::where('barcode_id', $log->barcode_id)
                            ->withTrashed()
                            ->first();
                
                        if (is_null($vio)) {
                            continue;
                        }
                        
                        $units = $log->unit;
                    } else {
                        $units = $log->unit;
                    }
                
                    if (isset($units->property_id)) {
                        $services = $property->service;
                
                        if ($log->type == 2 && $log->wast == 1 && $log->recycle == null) {
                            $type = 'Waste Total: ' . $services->waste_weight;
                        }
                        
                        if ($log->type == 2 && $log->recycle == 1 && $log->wast == null) {
                            $type = 'Recycle Total: ' . $services->recycle_weight;
                        }
                        
                        if ($log->type == 2 && $log->recycle == 1 && $log->wast == 1) {
                            $type = 'Waste Total:' . $services->recycle_weight . '<br/> Recycle Total:' . $services->waste_weight;
                        }
                
                        if (isset($property->name)) {
                            $propertyName = $property->name;
                        }
                
                        if (isset($units->getBuildingDetail->building_name)) {
                            $buildingName = $units->getBuildingDetail->building_name;
                        }
                
                        $logsArray[] = [
                           'sNo' => ++$i,
                            'property_name' => $propertyName,
                            'building' => empty($buildingName) ? $propertyName : $buildingName,
                            'unit' => !empty($units->unit_number) ? $units->unit_number : $units->name,
                            'updated_at' => $log->updated_at->timezone($timezone)->format('m-d-Y h:i A'),
                            'type' => !empty($type) ? $type : '-',
                            'status' => $log->text,
                            'employee_name' => ucwords($userInfoByUserId->title . ' ' . $userInfoByUserId->firstname . ' ' . $userInfoByUserId->lastname),
                            'timezone' => $reportTime
                        ];
                    } elseif ($log->type == 8 || $log->type == 5 || $log->type == 12) {
                        if (isset($property->name)) {
                            $propertyName = $property->name;
                            $propertyId = $property->id;
                        }
                
                        if (isset($building->building_name)) {
                            $buildingName = $building->building_name;
                        }
                
                        $logsArray[] = [
                            'sNo' => ++$i,
                            'property_name' => $propertyName,
                            'building' => empty($buildingName) ? $propertyName : $buildingName,
                            'unit' => !empty($units->name) ? $units->name : '-',
                            'updated_at' => $log->updated_at->timezone($timezone)->format('m-d-Y h:i A'),
                            'type' => '-',
                            'status' => $log->text,
                            'employee_name' => ucwords($userInfoByUserId->title . ' ' . $userInfoByUserId->firstname . ' ' . $userInfoByUserId->lastname),
                            'timezone' => $reportTime
                        ];
                    }
                }

                if (count($logsArray) > 1) {
                    \Excel::create(
                        $reportName,
                        function ($excel) use ($logsArray, $date, $frequency) {
                            $excel->setTitle('Trash Scan ' . $frequency . ' Service Report - [' . $date . ']');
                            $excel->setDescription('Trash Scan ' . $frequency . ' Service Report - [' . $date . ']');
                            $excel->sheet(
                                'sheet1',
                                function ($sheet) use ($logsArray) {
                                    $sheet->fromArray($logsArray, null, 'A1', false, false);
                                }
                            );
                        }
                    )
                    //->download('xls');
                    ->store(
                        'xls',
                        public_path() . '/uploads/pdf/'
                    );
                                        
                    $link = url("guest/automated-service-report/$hashSubscriberId");
                    
                    $data = [
                        "message" => 'Hello, Click the below button to download ' . strtolower($frequency) . ' service report.',
                        "subject" => 'Trash Scan ' . $frequency . ' Service Report - [' . $date . ']',
                        "link" => $link,
                        "button" => "Download Service Report"
                    ];
                
                    \Notification::send($adminRole, new \App\Notifications\SendDeliveryReportExcel($data));
                }

                unset($logsArray);
            }
        }
    }
}

function automatedUnitReport($use, $frequency)
{
    #1314: Automated Report for Bin Tag List: Start
    foreach ($use as $user) {
        $getProperties = $user->getProperties;
        $getEmployees = $user->employees;
        $admins = $getEmployees->where('role_id', 1);
        $adminRole = $getEmployees
            ->filter(
                function ($value, $key) {
                    return $value->role_id == 1 &&  is_null($value->deleted_at);
                }
            );
        $related[] = ['S.No', 'Address1', 'Address2', 'Unit Number', 'Activation Date', 'Property', 'Building', 'Floor', 'Latitude', 'Longitude', 'Barcode', 'Last Scan Date', 'Units', 'Created At', 'Updated At', 'Status', 'Property Timezone'];
        
        if ($getProperties->isEmpty()) {
            continue;
        }

        foreach ($adminRole as $admin) {
            $hashSubscriberId = \Hashids::encode($admin->id);
            $userNotify = \App\UserNotification::select('user_id', 'day_frequency', 'type')
            ->where('subscriber_id', $admin->subscriber_id)
            ->where('user_id', $admin->id)
            ->when(
                $frequency == 'Daily',
                function ($query) {
                    $query->where('day_frequency', 1);
                }
            )
            ->when(
                $frequency == 'Monthly',
                function ($query) {
                    $query->where('day_frequency', 3);
                }
            )
            ->when(
                $frequency == 'Weekly',
                function ($query) {
                    $query->where('day_frequency', 2);
                }
            )
            ->where('type', '=', 8)
            ->first();

            $timezone = !empty($admin->timezone) ? $admin->timezone : 'America/New_York';
            
            switch ($timezone) {
                case "America/Los_Angeles":
                    $reportTime = 'PST (UTC-8:00 Hrs)';
                    break;
                case "America/Chicago":
                    $reportTime =  "CST (UTC-6:00 Hrs)";
                    break;
                case "America/Denver":
                    $reportTime =  "MST (UTC-7:00 Hrs)";
                    break;
                default:
                    $reportTime = "EST (UTC-5:00 Hrs)";
            }

            if (!is_null($userNotify)) {
                $i = 1;
                foreach ($getProperties as $property) {
                    foreach ($property->getUnit as $getUnit) {
                        $lastScanDate = \App\Activitylogs::query()
                            ->select('created_at')
                            ->where('barcode_id', $getUnit->barcode_id)
                            ->latest()
                            ->first();

                        $related[] = [
                            'S.No' => $i++,
                            'Address1' => $getUnit->address1,
                            'Address2' => $getUnit->address2,
                            'Unit Number' => $getUnit->unit_number,
                            'Activation Date'  => !empty($getUnit->activation_date) ? \Carbon\Carbon::parse($getUnit->activation_date)->timezone($timezone)->format('m-d-Y h:i A') : '',
                            'Property'  => $property->name,
                            'Building'  => $getUnit->building,
                            'Floor' => $getUnit->floor,
                            'Latitude'  => $getUnit->latitude,
                            'Longitude' => $getUnit->longitude,
                            'Barcode'  => $getUnit->barcode_id,
                            'Last Scan Date'  => !empty($lastScanDate->created_at) ? $lastScanDate->created_at->timezone($timezone)->format('m-d-Y h:i A') : '',
                            'Units'  => empty($getUnit->is_route) ? 'Unit':'Route Checkpoint',
                            'Created At'  => $getUnit->created_at->timezone($timezone)->format('m-d-Y h:i A'),
                            'Updated At' => $getUnit->updated_at->timezone($timezone)->format('m-d-Y h:i A'),
                            'Status' => !empty($getUnit->is_active) ? 'Active' : 'In-active',
                            'timezone' => $reportTime
                        ];
                    }
                }
            
                $now = \Carbon\Carbon::now()->timezone($timezone)->format('Y-m-d');

                if ($userNotify->day_frequency == 1) {
                    $endTime = $now . ' 05:59:59';
                    
                    $startTime = \Carbon\Carbon::parse($endTime)->subDays(1)->copy()->format('Y-m-d') . ' 06:00:00';
                    
                    $date = \Carbon\Carbon::parse($endTime)->subDays(1)->copy()->format('F, d Y');
        
                    $reportName = "DailyUnitReport-$hashSubscriberId";
                } elseif ($userNotify->day_frequency == 2) {
                    $startTime = \Carbon\Carbon::now()->timezone($timezone)->startOfWeek()->subWeek()->addHours(6);
                    
                    $endTime = \Carbon\Carbon::now()->timezone($timezone)->startOfWeek()->addHours(5)->addMinutes(59)->addSeconds(59);
                    
                    $date = \Carbon\Carbon::parse($startTime)->copy()->format('F, d Y') . ' - ' .    \Carbon\Carbon::parse($endTime)->copy()->format('F, d Y');
                
                    $reportName = "WeeklyUnitReport-$hashSubscriberId";
                } elseif ($userNotify->day_frequency == 3) {
                    $startPreviousMonth = new \Carbon\Carbon('first day of last month', $timezone);
                
                    $startCurrentMonth = new \Carbon\Carbon('first day of this month', $timezone);
                
                    $startTime =  \Carbon\Carbon::parse($startPreviousMonth)->copy()->format('Y-m-d') . ' 06:00:00';
                
                    $endTime = \Carbon\Carbon::parse($startCurrentMonth)->copy()->format('Y-m-d') . ' 05:59:59';
        
                    $date = \Carbon\Carbon::now()->timezone($timezone)->subMonth()->format('F, Y');
                    
                    $reportName = "MonthlyUnitReport-$hashSubscriberId";
                }

                if (count($related) > 1) {
                    \Excel::create(
                        $reportName,
                        function ($excel) use ($related, $date, $frequency) {
                            $excel->setTitle('Trash Scan ' . $frequency . ' unit Report - [' . $date . ']');
                    
                            $excel->setDescription('Trash Scan ' . $frequency . ' unit Report - [' . $date . ']');
                    
                            $excel->sheet(
                                'sheet1',
                                function ($sheet) use ($related) {
                                    $sheet->fromArray($related, null, 'A1', false, false);
                                }
                            );
                        }
                    )
                    ->store(
                        'xls',
                        public_path() . '/uploads/pdf/'
                    );
                    unset($related);
                    $link = url("guest/automated-unit-report/$hashSubscriberId");
                    
                    $data = [
                        "message" => 'Hello, Click the below button to download ' . strtolower($frequency) . ' unit report.',
                        "subject" => 'Trash Scan ' . $frequency . ' Unit Report - [' . $date . ']',
                        "link" => $link,
                        "button" => "Download Unit Report"
                    ];
                    
                    \Notification::send($adminRole, new \App\Notifications\SendDeliveryReportExcel($data));
                }
            }
        }
    }
    #1314: Automated Report for Bin Tag List: End
}

function automatedClockInOutReport($use, $frequency)
{
    #1352: Automated Clock in/out report: Start
    foreach ($use as $user) {
        $getEmployees = $user->employees;
        $admins = $getEmployees->where('role_id', 1);
        $adminRole = $getEmployees
            ->filter(
                function ($value, $key) {
                    return $value->role_id == 1 &&  is_null($value->deleted_at);
                }
            );
            
        $related[] = ['S.No', 'Name', 'Reporting Manager', 'Clockin', 'Clockout', 'Reason', 'Property Timezone'];

        foreach ($adminRole as $admin) {
            $i = 1;
            $hashSubscriberId = \Hashids::encode($admin->id);
            $userNotify = \App\UserNotification::select('user_id', 'day_frequency', 'type')
            ->where('subscriber_id', $admin->subscriber_id)
            ->where('user_id', $admin->id)
            ->when(
                $frequency == 'Daily',
                function ($query) {
                    $query->where('day_frequency', 1);
                }
            )
            ->when(
                $frequency == 'Monthly',
                function ($query) {
                    $query->where('day_frequency', 3);
                }
            )
            ->when(
                $frequency == 'Weekly',
                function ($query) {
                    $query->where('day_frequency', 2);
                }
            )
            ->where('type', '=', 9)
            ->first();
                  
            if (!is_null($userNotify)) {
                $timezone = !empty($admin->timezone) ? $admin->timezone : 'America/New_York';
                
                switch ($timezone) {
                    case "America/Los_Angeles":
                        $reportTime = 'PST (UTC-8:00 Hrs)';
                        break;
                    case "America/Chicago":
                        $reportTime =  "CST (UTC-6:00 Hrs)";
                        break;
                    case "America/Denver":
                        $reportTime =  "MST (UTC-7:00 Hrs)";
                        break;
                    default:
                        $reportTime = "EST (UTC-5:00 Hrs)";
                }

                $now = \Carbon\Carbon::now()->timezone($timezone)->format('Y-m-d');
                
                if ($userNotify->day_frequency == 1) {
                    $endTime = $now . ' 05:59:59';
                        
                    $startTime = \Carbon\Carbon::parse($endTime)->subDays(1)->copy()->format('Y-m-d') . ' 06:00:00';
                        
                    $date = \Carbon\Carbon::parse($endTime)->subDays(1)->copy()->format('F, d Y');
            
                    $reportName = "DailyClockInOutReport-$hashSubscriberId";
                } elseif ($userNotify->day_frequency == 2) {
                    $startTime = \Carbon\Carbon::now()->timezone($timezone)->startOfWeek()->subWeek()->addHours(6);
                        
                    $endTime = \Carbon\Carbon::now()->timezone($timezone)->startOfWeek()->addHours(5)->addMinutes(59)->addSeconds(59);
                        
                    $date = \Carbon\Carbon::parse($startTime)->copy()->format('F, d Y') . ' - ' .    \Carbon\Carbon::parse($endTime)->copy()->format('F, d Y');
                    
                    $reportName = "WeeklyClockInOutReport-$hashSubscriberId";
                } elseif ($userNotify->day_frequency == 3) {
                    $startPreviousMonth = new \Carbon\Carbon('first day of last month', $timezone);
                    
                    $startCurrentMonth = new \Carbon\Carbon('first day of this month', $timezone);
                    
                    $startTime =  \Carbon\Carbon::parse($startPreviousMonth)->copy()->format('Y-m-d') . ' 06:00:00';
                    
                    $endTime = \Carbon\Carbon::parse($startCurrentMonth)->copy()->format('Y-m-d') . ' 05:59:59';
            
                    $date = \Carbon\Carbon::now()->timezone($timezone)->subMonth()->format('F, Y');
                        
                    $reportName = "MonthlyClockInOutReport-$hashSubscriberId";
                }

                $clock = \App\ClockInOut::where(
                    function ($query) use ($admin) {
                        $query->whereIn(
                            'user_id',
                            function ($query) use ($admin) {
                                $query->select('id')
                                    ->from('users')
                                    ->whereNotIn('role_id', [10])
                                    ->whereNull('deleted_at')
                                    ->where('subscriber_id', $admin->subscriber_id);
                            }
                        );
                    }
                )
                ->whereBetween(
                    \DB::raw("convert_tz(created_at,'UTC','" . $timezone . "')"),
                    [
                        $startTime,
                        $endTime,
                    ]
                )
                ->with(
                    [
                        'getUser',
                    ]
                )
                ->get();
                
                foreach ($clock as $clocks) {
                    $name = $clockin = $clockout = $reason = '';
        
                    $reporting = \App\User::select(\DB::raw("CONCAT_WS(' ', `firstname`, `lastname`) as name"))->where('id', $clocks->getUser->reporting_manager_id)->first();
        
                    $name = !empty($clocks->getUser->firstname) ? ucwords($clocks->getUser->firstname) . ' ' . ucwords($clocks->getUser->lastname) : '-';
        
                    $clockin = !empty($clocks->clock_in) ? \Carbon\Carbon::parse($clocks->clock_in)->timezone($admin->timezone)->format('m-d-Y h:i A') : '-';
        
                    $clockout = !empty($clocks->clock_out) ? \Carbon\Carbon::parse($clocks->clock_out)->timezone($admin->timezone)->format('m-d-Y h:i A') : '';
        
                    $reason = !empty($clocks->reason) ? ucwords($clocks->reason) : '';
                    
                    $reporting = !is_null($reporting) ? ucwords($reporting->name) : '-';
        
                    $related[] = [
                        'user_id' => $i++,
                        'name' => $name,
                        'reportingname' => $reporting,
                        'clockin' => $clockin,
                        'clockout' => $clockout,
                        'reason' => $reason,
                        'timezone' => $reportTime
                    ];
                }
                
                if (isset($related) && count($related) > 1) {
                    \Excel::create(
                        $reportName,
                        function ($excel) use ($related, $date, $frequency) {
                            $excel->setTitle('Trash Scan ' . $frequency . ' clock in/out Report - [' . $date . ']');
                    
                            $excel->setDescription('Trash Scan ' . $frequency . ' clock in/out Report Report - [' . $date . ']');
                    
                            $excel->sheet(
                                'sheet1',
                                function ($sheet) use ($related) {
                                    $sheet->fromArray($related, null, 'A1', false, false);
                                }
                            );
                        }
                    )
                    ->store(
                        'xls',
                        public_path() . '/uploads/pdf/'
                    );

                    $link = url("guest/automated-clockinout-report/$hashSubscriberId");
                    unset($related);
                
                    $data = [
                        "message" => 'Hello, Click the below button to download ' . strtolower($frequency) . ' clockin/out report.',
                        "subject" => 'Trash Scan ' . $frequency . ' Clock In/Out Report - [' . $date . ']',
                        "link" => $link,
                        "button" => "Download Clock In/Out Report"
                    ];
                    
                    \Notification::send($adminRole, new \App\Notifications\SendDeliveryReportExcel($data));
                }
            }
        }
    }
    #1352: Automated Clock in/out report: End
}

function automatedViolationReport($use, $frequency)
{
    foreach ($use as $user) {
        $getEmployees = $user->employees;
        $getProperties = $user->getProperties;
        $admins = $getEmployees->where('role_id', 1);
        $adminRole = $getEmployees
            ->filter(
                function ($value, $key) {
                    return $value->role_id == 1 &&  is_null($value->deleted_at);
                }
            );
        
        foreach ($adminRole as $admin) {
            $i = 1;
            $related[] = ['S.No', 'Username', 'Property', 'Rule', 'Action', 'Status', 'Details', 'Special Notes', 'Building', 'No.of Image', 'Created At', 'Property Timezone'];

            $hashSubscriberId = \Hashids::encode($admin->id);
            $checkUserNotify = \App\UserNotification::select('id')
                ->where('subscriber_id', $admin->subscriber_id)
                ->where('user_id', $admin->id)
                ->where('type', 10)
                ->first();

            $userNotify = \App\UserNotification::select('user_id', 'day_frequency', 'type')
            ->where('subscriber_id', $admin->subscriber_id)
            ->where('user_id', $admin->id)
            ->when(
                $frequency == 'Daily',
                function ($query) {
                    $query->where('day_frequency', 1);
                }
            )
            ->when(
                $frequency == 'Monthly',
                function ($query) {
                    $query->where('day_frequency', 3);
                }
            )
            ->when(
                $frequency == 'Weekly',
                function ($query) {
                    $query->where('day_frequency', 2);
                }
            )
            ->where('type', 10)
            ->first();
                
            if (!is_null($userNotify) || is_null($checkUserNotify)) {
                $timezone = !empty($admin->timezone) ? $admin->timezone : 'America/New_York';
                
                switch ($timezone) {
                    case "America/Los_Angeles":
                        $reportTime = 'PST (UTC-8:00 Hrs)';
                        break;
                    case "America/Chicago":
                        $reportTime =  "CST (UTC-6:00 Hrs)";
                        break;
                    case "America/Denver":
                        $reportTime =  "MST (UTC-7:00 Hrs)";
                        break;
                    default:
                        $reportTime = "EST (UTC-5:00 Hrs)";
                }
                
                $now = \Carbon\Carbon::now()->timezone($timezone)->format('Y-m-d');
                
                if (is_null($checkUserNotify) || $userNotify->day_frequency == 1) {
                    $endTime = $now . ' 05:59:59';
                        
                    $startTime = \Carbon\Carbon::parse($endTime)->subDays(1)->copy()->format('Y-m-d') . ' 06:00:00';
                        
                    $date = \Carbon\Carbon::parse($endTime)->subDays(1)->copy()->format('F, d Y');
            
                    $reportName = "DailyViolationReport-$hashSubscriberId";
                } elseif ($userNotify->day_frequency == 2) {
                    $startTime = \Carbon\Carbon::now()->timezone($timezone)->startOfWeek()->subWeek()->addHours(6);
                        
                    $endTime = \Carbon\Carbon::now()->timezone($timezone)->startOfWeek()->addHours(5)->addMinutes(59)->addSeconds(59);
                        
                    $date = \Carbon\Carbon::parse($startTime)->copy()->format('F, d Y') . ' - ' .    \Carbon\Carbon::parse($endTime)->copy()->format('F, d Y');
                    
                    $reportName = "WeeklyViolationReport-$hashSubscriberId";
                } elseif ($userNotify->day_frequency == 3) {
                    $startPreviousMonth = new \Carbon\Carbon('first day of last month', $timezone);
                    
                    $startCurrentMonth = new \Carbon\Carbon('first day of this month', $timezone);
                    
                    $startTime =  \Carbon\Carbon::parse($startPreviousMonth)->copy()->format('Y-m-d') . ' 06:00:00';
                    
                    $endTime = \Carbon\Carbon::parse($startCurrentMonth)->copy()->format('Y-m-d') . ' 05:59:59';
            
                    $date = \Carbon\Carbon::now()->timezone($timezone)->subMonth()->format('F, Y');
                        
                    $reportName = "MonthlyViolationReport-$hashSubscriberId";
                }
               
                $vio = \App\Violation::query()
                    ->whereBetween(
                        \DB::raw("convert_tz(created_at, 'UTC','" . $timezone . "')"),
                        [
                            $startTime,
                            $endTime,
                        ]
                    )
                    ->whereIn('property_id', $getProperties->pluck('id'))
                    ->withCount(
                        [
                            'images',
                        ]
                    )
                    ->with(
                        [
                            'getReason' => function ($query) {
                                $query->select('id', 'reason')
                                    ->withTrashed();
                            },
                            'getAction' => function ($query) {
                                $query->select('id', 'action')
                                    ->withTrashed();
                            },
                            'getUser' => function ($query) {
                                $query->select('id', \DB::raw("CONCAT_WS(' ', `title`, `firstname`, `lastname`) as name"))
                                    ->withTrashed();
                            },
                            'getUnitNumber' => function ($query) {
                                $query->select('id', 'unit_number', 'barcode_id', 'property_id')
                                    ->withTrashed();
                            },
                            'getBuilding' => function ($query) {
                                $query->select('id', 'building_name');
                            },
                            'getProperty' => function ($query) {
                                $query->select('id', 'name')
                                    ->withTrashed();
                            },
                        ]
                    )
                ->latest()
                ->withTrashed()
                ->get();

                foreach ($vio as $vios) {
                    $name = $property = $detail = $clockout = $reason = '';
        
                    if (isset($vios->getUser->name)) {
                        $name = ucwords($vios->getUser->name);
                    }

                    $property = isset($vios->getProperty->name) ? $vios->getProperty->name : '';

                    if (isset($vios->getReason->reason)) {
                        $rule = ucwords($vios->getReason->reason);
                    }
    
                    if (isset($vios->getAction->action)) {
                        $action = ucwords($vios->getAction->action);
                    }

                    $vioStatus = $admin->hasRole('property_manager')
                        ? $vios->manager_status : $vios->status;

                    if ($vioStatus == 6) {
                        $type = 'Archived';
                    } elseif ($vioStatus == 5) {
                        $type = 'Closed';
                    } elseif ($vioStatus == 2) {
                        $type = 'Submitted';
                    } elseif ($vioStatus == 0) {
                        $type = 'New';
                    } elseif ($vioStatus == 7) {
                        $type = 'Read';
                    } elseif ($vioStatus == 8) {
                        $type = 'In Process';
                    } elseif ($vioStatus == 9) {
                        $type = 'On Hold';
                    } elseif ($vioStatus == 10) {
                        $type = 'Sent Notice';
                    } else {
                        $type = 'Discarded';
                    }

                    if (isset($vios->created_at)) {
                        $createdAt = \Carbon\Carbon::parse($vios->created_at)
                        ->timezone(getUserTimezone())->format('m-d-Y h:i A');
                    }
        
                    if (isset($vios->getBuilding->building_name)) {
                        $building = ucwords($vios->getBuilding->building_name);
                    } else {
                        $building = "";
                    }

                    if (empty($vios->type) && isset($vios->getUnitNumber->unit_number)) {
                        $detail = 'Unit Number:' . $vios->getUnitNumber->unit_number;
                    }
        
                    if (!empty($vios->type) && isset($vios->getUnitNumber->unit_number)) {
                        $detail = 'Route Checkpoint: ' . $vios->getUnitNumber->unit_number;
                    }

                    if (isset($vios->special_note)) {
                        $specialNote = ucwords($vios->special_note);
                    }

                    $related[] = [
                        'user_id' => $i++,
                        'username' => $name,
                        'property' => $property,
                        'rule' => $rule,
                        'action' => $action,
                        'status' => $type,
                        'detail' => $detail,
                        'special' => $specialNote,
                        'building' => $building,
                        'detail' => $detail,
                        'imagecount' => $vios->images_count ? $vios->images_count : 0,
                        'created At' => $createdAt,
                        'timezone' => $reportTime
                    ];
                }
                
                if (isset($related) && count($related) > 1) {
                    \Excel::create(
                        $reportName,
                        function ($excel) use ($related, $date, $frequency) {
                            $excel->setTitle('Trash Scan ' . $frequency . ' Violation Report - [' . $date . ']');
                    
                            $excel->setDescription('Trash Scan ' . $frequency . ' Violation Report - [' . $date . ']');
                    
                            $excel->sheet(
                                'sheet1',
                                function ($sheet) use ($related) {
                                    $sheet->fromArray($related, null, 'A1', false, false);
                                }
                            );
                        }
                    )
                    //->download('xls');
                    ->store(
                        'xls',
                        public_path() . '/uploads/pdf/'
                    );
                
                    unset($related);
                    
                    $link = url("guest/automated-violation-report/$hashSubscriberId");
                    
                    $data = [
                        "message" => 'Hello, Click the below button to download ' . strtolower($frequency) . ' violation report.',
                        "subject" => 'Trash Scan ' . $frequency . ' Violation Report - [' . $date . ']',
                        "link" => $link,
                        "button" => "Download Violation Report"
                    ];
                    
                    \Notification::send($adminRole, new \App\Notifications\SendDeliveryReportExcel($data));
                }
            } else {
                unset($related);
            }
        }
    }

}