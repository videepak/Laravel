<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClockInOutController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function clockInOutDetail(Request $request)
    {
        $users = $reporting = collect();

        $reporting = \App\User::select('id', 'timezone', 'subscriber_id', 'is_admin', \DB::raw("CONCAT_WS(' ', `firstname`, `lastname`) as name"))
            ->where('id', $this->user->id)
            ->first();

        if (!is_null($reporting) && $reporting->is_admin == 1) {
            $reportingManager = \App\User::select('id', 'reporting_manager_id', \DB::raw("CONCAT_WS(' ', `firstname`, `lastname`) as name"))
            ->where(
                [
                    'subscriber_id' => $reporting->subscriber_id,
                    'role_id' => \config('constants.adminRoleId'),
                ]
            )
            ->orderBy('firstname')
            ->get();

            $users = \App\User::select('id', 'reporting_manager_id', \DB::raw("CONCAT_WS(' ', `firstname`, `lastname`) as name"))
                ->whereNotIn('role_id', [10]) //Remove property manager from the list.
                ->whereIn('reporting_manager_id', $reportingManager->pluck('id'))
                ->orderBy('firstname')
                ->get();
        } elseif (!is_null($reporting)) {
            $reportingManager = \App\User::select('id', 'timezone', 'subscriber_id', 'is_admin', \DB::raw("CONCAT_WS(' ', `firstname`, `lastname`) as name"))
                ->where('id', $this->user->id)
                ->get();

            $users = \App\User::select('id', 'reporting_manager_id', \DB::raw("CONCAT_WS(' ', `firstname`, `lastname`) as name"))
                ->where('role_id', '!=', 10) //Remove property manager from the list.
                ->whereIn(
                    'reporting_manager_id',
                    function ($query) use ($request) {
                        $query->from('users')
                            ->select('id')
                            ->where('id', $this->user->id);
                    }
                )
            ->orderBy('firstname')
            ->get();
        }

        $this->data['users'] = $users;
        $this->data['reporting'] = $reportingManager;

        return view('report.clockinout', $this->data);
    }

    public function getReport(Request $request)
    {
        $i = $request->start + 1;
        $reportArray = [];
        $reportingManager = $request->reporting;
        $name = $request->name;
        $users = $reporting = collect();
        $todayStart = \Carbon\Carbon::parse($request->startTime, getUserTimezone())->addHours(6)->copy();
        $todayEnd = \Carbon\Carbon::parse($request->endTime, getUserTimezone())->addDays(1)->addHours(5)->addMinutes(59)->addSeconds(59)->copy();

        //Get total result:Start (Todo: merge the both queries): Start
        $clo = \App\ClockInOut::when(
            $this->user->is_admin,
            function ($query) use ($reportingManager) {
                $query->whereIn(
                    'user_id',
                    function ($query) use ($reportingManager) {
                        $query->select('id')
                            ->from('users')
                            ->whereNotIn('role_id', [10])
                            ->when(
                                !empty($reportingManager),
                                function ($query) use ($reportingManager) {
                                    $query->where('reporting_manager_id', $reportingManager);
                                },
                                function ($query) {
                                    $query->where('subscriber_id', $this->user->subscriber_id);
                                }
                            )
                            ->whereNull('deleted_at');
                    }
                );
            },
            function ($query) use ($reportingManager) {
                $query->whereIn(
                    'user_id',
                    function ($query) use ($reportingManager) {
                        $query->select('id')
                            ->from('users')
                            ->when(
                                !empty($reportingManager),
                                function ($query) use ($reportingManager) {
                                    $query->where('reporting_manager_id', $reportingManager);
                                },
                                function ($query) {
                                    $query->where('reporting_manager_id', $this->user->id);
                                }
                            )
                            ->whereNotIn('role_id', [10])
                            ->whereNull('deleted_at');
                    }
                );
            }
        )
        ->when(
            !empty($name),
            function ($query) use ($name) {
                $query->where('user_id', $name);
            }
        )
        ->whereBetween(
            \DB::raw("convert_tz(created_at,'UTC','" . getUserTimezone() . "')"),
            [
                $todayStart,
                $todayEnd,
            ]
        )
        ->get();
        //Get total result (Todo: merge the both queries): End

        $clock = \App\ClockInOut::when(
            $this->user->is_admin,
            function ($query) use ($reportingManager) {
                $query->whereIn(
                    'user_id',
                    function ($query) use ($reportingManager) {
                        $query->select('id')
                            ->from('users')
                            ->whereNotIn('role_id', [10])
                            ->whereNull('deleted_at')
                            ->when(
                                !empty($reportingManager),
                                function ($query) use ($reportingManager) {
                                    $query->where('reporting_manager_id', $reportingManager);
                                },
                                function ($query) {
                                    $query->where('subscriber_id', $this->user->subscriber_id);
                                }
                            );
                    }
                );
            },
            function ($query) use ($reportingManager) {
                $query->whereIn(
                    'user_id',
                    function ($query) use ($reportingManager) {
                        $query->select('id')
                            ->from('users')
                            ->whereNotIn('role_id', [10])
                            ->whereNull('deleted_at')
                            ->when(
                                !empty($reportingManager),
                                function ($query) use ($reportingManager) {
                                    $query->where('reporting_manager_id', $reportingManager);
                                },
                                function ($query) {
                                    $query->where('reporting_manager_id', $this->user->id);
                                }
                            );
                    }
                );
            }
        )
        ->when(
            !empty($name),
            function ($query) use ($name) {
                $query->where('user_id', $name);
            }
        )
        ->whereBetween(
            \DB::raw("convert_tz(created_at,'UTC','" . getUserTimezone() . "')"),
            [
                $todayStart,
                $todayEnd,
            ]
        )
        ->with(
            [
                'getUser',
            ]
        )
        ->orderBy('clock_in', 'DESC')
        ->get();
 
        foreach ($clock as $clocks) {
            $name = $clockin = $clockout = $reason = '';

            $reporting = \App\User::select(\DB::raw("CONCAT_WS(' ', `firstname`, `lastname`) as name"))->where('id', $clocks->getUser->reporting_manager_id)->first();

            $name = !empty($clocks->getUser->firstname) ? ucwords($clocks->getUser->firstname) . ' ' . ucwords($clocks->getUser->lastname) : '-';

            $clockin = !empty($clocks->clock_in) ? \Carbon\Carbon::parse($clocks->clock_in)->timezone(getUserTimezone())->format('m-d-Y h:i A') : '-';            
            $clockin = "<a href='#' class='time' data-name='1' data-pk='" . $clocks->id . "' data-title='Clockin'>" . $clockin . '</a>';


            $clockout = !empty($clocks->clock_out) ? \Carbon\Carbon::parse($clocks->clock_out)->timezone(getUserTimezone())->format('m-d-Y h:i A') : '';
            $clockout = "<a href='#' class='time' data-name='0' data-pk='" . $clocks->id . "' data-title='Clockout'>" . $clockout . '</a>';


            $reason = !empty($clocks->reason) ? ucwords($clocks->reason) : '';
            $reason = "<a href='#' class='textare' data-name='reason' data-pk='" . $clocks->id . "' data-title='Reason'>" . $reason . '</a>';

            $reporting = !is_null($reporting) ? ucwords($reporting->name) : '-';

            $reportArray[] = [
                'user_id' => $i++,
                'name' => $name,
                'clockin' => $clockin,
                'clockout' => $clockout,
                'reason' => $reason,
                'reportingname' => $reporting,
            ];
        }

        return json_encode(
            [
                'draw' => intval($request->draw),
                'recordsTotal' => $clo->isNotEmpty() ? $clo->count() : 0,
                'recordsFiltered' => $clo->isNotEmpty() ? $clo->count() : 0,
                'data' => $reportArray,
            ]
        );
    }

    /*
    *
    *   #1317: Edit Employee Timesheet
    *
    */
    public function resetDateTime(Request $request)
    {
        if ($request->name != 'reason') {
            $dateti = \Carbon\Carbon::parse($request->value, getUserTimezone())
                ->timezone('UTC')->format('Y-m-d H:i:s');
                
            $data = !empty($request->name) ? ['clock_in' => $dateti] : ['clock_out' => $dateti];
            
            $message = !empty($request->name) ? 'Clock In datetime' : 'Clock Out datetime';
        } else {
            $data = ['reason' => $request->value];
            $message = 'Reason';
        }
     
        $status = \App\ClockInOut::where('id', $request->pk)->update($data);

        if ($status) {
            return response()->json(
                [
                    'message' => "$message update successfully.",
                    'alert' => 'success'
                ],
                200
            );
        } else {
            return response()->json(
                [
                    'message' => 'Some thing went try after sometime.',
                    'alert' => 'danger'
                ],
                200
            );
        }
    }
}
