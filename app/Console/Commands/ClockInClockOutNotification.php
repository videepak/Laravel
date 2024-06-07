<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClockInClockOutNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ClockInClockOutNotification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'When user delay clock-in or clock-out then we will send a notification to the user and its reporting manager.';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $users = \App\User::select('id', 'device_token', 'reporting_manager_id', 'service_in_time', 'service_out_time', 'platform', 'subscriber_id', 'timezone', \DB::raw("CONCAT_WS(' ', `title`, `firstname`, `lastname`) as name"))
            ->whereNotNull('device_token')
            ->whereNotNull('timezone')
            ->whereNotNull('platform')
            ->where('reporting_manager_id', '!=', 0)
            ->where('is_admin', '!=', \config('constants.adminRoleId'))
            ->where('role_id', '!=', \config('constants.propertyManager'))
            ->with(
                [
                    'getSubscriber' => function ($query) {
                        $query->select('id', 'service_in_time', 'service_out_time');
                    },
                ]
            )
            ->get();
                    
        if ($users->isNotEmpty()) {
            foreach ($users as $user) {
                $title = '';
                
                $platform = strtoupper($user->platform);

                $currentTime = \Carbon\Carbon::now()->setTimezone($user->timezone);

                $report = \App\User::select('id', 'device_token', 'platform')
                            ->where('id', $user->reporting_manager_id)->first();

                //1078: Unsubscribe from email notification: Start
                if (!is_null($report)) {
                    $checkUser = \App\UserNotification::where(
                        [
                            'type' => 6,
                            'user_id' => $report->id,
                        ]
                    )
                    ->first();

                    $check = !is_null($checkUser) && empty($checkUser->sms) ? false : true;
                }
                //1078: Unsubscribe from email notification: End

                // Get user clock-in and clock-out time: Start
                if (!empty($user->service_in_time) && !empty($user->service_out_time)) {
                    $inTime = $user->service_in_time;
                    $outTime = $user->service_out_time;
                    $start = str_replace(':', '.', substr($user->service_in_time, 0, -3));
                    $end = str_replace(':', '.', substr($user->service_out_time, 0, -3));
                } else {
                    $inTime = $user->getSubscriber->service_in_time;
                    $outTime = $user->getSubscriber->service_out_time;
                    $start = str_replace(':', '.', substr($user->getSubscriber->service_in_time, 0, -3));
                    $end = str_replace(':', '.', substr($user->getSubscriber->service_out_time, 0, -3));
                }

                $startDate = \Carbon\Carbon::now()
                    ->setTimezone($user->timezone)
                    ->format('Y-m-d') . ' ' . $inTime;

                $startDate = \Carbon\Carbon::parse($startDate, $user->timezone);

                if ($start > $end) {
                    $endDate = \Carbon\Carbon::now()
                        ->setTimezone($user->timezone)->addDay(1)
                        ->format('Y-m-d') . ' ' . $outTime;
                } else {
                    $endDate = \Carbon\Carbon::now()
                        ->setTimezone($user->timezone)
                        ->format('Y-m-d') . ' ' . $outTime;
                }

                $endDate = \Carbon\Carbon::parse($endDate, $user->timezone);

                // Get user clock-in and clock-out time: End

                $today = \Carbon\Carbon::now()->setTimezone($user->timezone)->subHours(6);
                $todayStart = $today->copy()->format('Y-m-d') . ' 06:00:00';
                $todayEnd = $today->copy()->addDay(1)->format('Y-m-d') . ' 05:59:59';

                //Check clock-in and clock-out with days:Start
                $days = explode(",", $user->clockinout_frequency_day);
                $currentDay = $today->copy()->format('w');

                if (!in_array($currentDay, $days)) {
                    continue;
                }
                //Check clock-in and clock-out with days:End

                $checkClock = \App\ClockInOut::where('user_id', $user->id)
                    ->whereBetween(
                        \DB::raw("convert_tz(clock_in, 'UTC', '" . $user->timezone . "')"),
                        [
                            $todayStart,
                            $todayEnd
                        ]
                    )
                    ->first();

                if (is_null($checkClock)) {
                    $startMins = $startDate->copy()->addMinute(30);

                    $endMins = $startDate->copy()->addMinute(60);

                    //dd($currentTime, $startMins, $endMins, $currentTime->gte($startMins), $currentTime->lte($endMins));

                    if ($currentTime->gte($startMins) && $currentTime->lte($endMins)) {
                        $title = 'Friendly reminder, clock in past due.';
                        $message = 'Friendly reminder, clock in past due.';
                        $deviceToken = [$user->device_token];

                        $payload = [
                            'notificationType' => 1,
                            'userId' => $user->id,
                            'message' => ' Friendly reminder, clock in past due.',
                        ];

                        //For Reporting Manager: Start

                        if (!is_null($report) && $check) {
                            $reporting[] = [
                                'userId' => $report->id,
                                'deviceToken' => $report->device_token,
                                'platform' => $report->platform,
                                'message' => 'View past due employee clock ins.',
                            ];
                        }

                        //For Reporting Manager: End
                    } else {
                        continue;
                    }
                } elseif (!is_null($checkClock) && empty($checkClock->clock_out)) {
                    $startMins = $endDate->copy()->addMinute(30);

                    $endMins = $endDate->copy()->addMinute(60);

                    if ($currentTime->gte($startMins) && $currentTime->lte($endMins)) {
                        $title = ' Friendly reminder, clock out past due.';
                        $message = 'Friendly reminder, clock out past due.';
                        $deviceToken = [$user->device_token];

                        $payload = [
                            'notificationType' => 2,
                            'userId' => $user->id,
                            'message' => 'Friendly reminder, clock out past due.',
                        ];

                        //For Reporting Manager: Start
                        if (!is_null($report) && $check) {
                            $reporting[] = [
                                'userId' => $report->id,
                                'deviceToken' => $report->device_token,
                                'platform' => $report->platform,
                                'message' => 'View past due employee clock outs.',
                            ];
                        }
                        //For Reporting Manager: End
                    } else {
                        continue;
                    }
                }

                if ((isset($title) && !empty($title)) && $platform == 'ANDROID') {
                    androidPush($title, $message, $deviceToken, $payload);
                } elseif ((isset($title) && !empty($title)) && $platform == 'IOS') {
                    iosPush($title, $message, $deviceToken, $payload);
                }
            }
        }

        //For Reporting Manager Only: Start
        if (isset($reporting) && !empty($reporting)) {
            $input = array_map('unserialize', array_unique(array_map('serialize', $reporting)));

            foreach ($input as $value) {
                $payload = [
                    'notificationType' => 4,
                    'message' => $value['message'],
                ];

                if (strtoupper($value['platform']) == 'ANDROID') {
                    androidPush($value['message'], $value['message'], [$value['deviceToken']], $payload);
                } elseif (strtoupper($value['platform']) == 'IOS') {
                    iosPush($value['message'], $value['message'], [$value['deviceToken']], $payload);
                }
            }
        }
        //For Reporting Manager Only: End
    }
}
