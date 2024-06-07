<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class SendDeliveryReportDataByExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Task : #1263: Automated Service Report Syndication
     *
     * @var string
     */
    protected $signature = 'command:SendDeliveryReportDataByExcel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create excel for delivery report and send to users via email.';

    /**
     * Create a new command instance.
     *
     * @return void
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
        $logs = collect();

        $users = \App\Subscriber::select('id', 'user_id')
            ->with(
                [
                    'user' => function ($query) {
                        $query->select('id', 'email', 'subscriber_id', 'is_admin', 'timezone');
                    },
                    'employees' => function ($query) {
                        $query->select('id', 'email', 'subscriber_id', 'is_admin', 'role_id')
                            ->withTrashed();
                    },
                    'getProperties' => function ($query) {
                        $query->select('id', 'subscriber_id')
                            ->withTrashed();
                    },
                    'getNotification' => function ($query) {
                        $query->select('day_frequency', 'subscriber_id', 'type')
                            ->where('type', 7);
                    }
                ]
            )
            ->whereHas(
                'getNotification',
                function ($query) {
                    $query->where('type', 7);
                }
            )
            ->get();

        foreach ($users as $user) {
            $i = 0;
            $getProperties = $user->getProperties;
            $getEmployees = $user->employees;
            $getNotification = $user->getNotification;
            //dd($getNotification, $getNotification[0]->subscriber_id);
            $timezone = !empty($user->user[0]->timezone) ? $user->user[0]->timezone : 'America/New_York';
            $now = \Carbon\Carbon::now()->timezone($timezone)->format('Y-m-d');
            $daysInMonth = \Carbon\Carbon::now()->timezone($timezone)->daysInMonth;
            //dd($daysInMonth);
            $logsArray = [];
            $logsArray[] = ['S.No', 'Property Name', 'Building', 'Unit', 'Updated At', 'Type','Status', 'Employee Name',
            ];

            if ($getProperties->isEmpty() || $getEmployees->isEmpty() || $getNotification->isEmpty()) {
                continue;
            }
  
            if ($getNotification[0]->day_frequency == 1) {
                $endTime = $now . ' 05:59:59';
                $startTime = \Carbon\Carbon::parse($endTime)->subDays(1)->copy()->format('Y-m-d') . ' 06:00:00';
            } elseif ($getNotification[0]->day_frequency == 2) {
                $endTime = $now . ' 05:59:59';
                $startTime = \Carbon\Carbon::parse($endTime)->subDays(7)->copy()->format('Y-m-d') . ' 06:00:00';
            } elseif ($getNotification[0]->day_frequency == 3) {
                $endTime = $now . ' 05:59:59';
                $startTime = \Carbon\Carbon::parse($endTime)->subDays($daysInMonth)
                    ->copy()->format('Y-m-d') . ' 06:00:00';
            }


            //dd($now, $startTime, $endTime);

            $barcode = \App\Units::select('barcode_id')
            ->where('is_active', 1)
            ->where('is_route', 0)
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
                    $query->whereIn('barcode_id', $barcode)
                        ->orWhereIn('property_id', $getProperties->pluck('id'));
                }
            )
            ->whereIn('type', [2, 3, 6, 8, 5])
            ->whereBetween(
                \DB::raw("convert_tz(updated_at, 'UTC','" . $timezone . "')"),
                [
                    $startTime,
                    $endTime,
                ]
            )
            ->withTrashed()
            ->latest()
            ->get();

            // $totalLog = \App\Activitylogs::whereIn('type', [2, 3, 6, 8, 5])
            // ->where(
            //     function ($query) use ($getEmployees) {
            //         $query->whereIn('user_id', $getEmployees->pluck('id'))
            //             ->orWhereIn('updated_by', $getEmployees->pluck('id'));
            //     }
            // )
            // ->where('property_id', $getProperties->pluck('id'))
            // ->withTrashed()
            // ->latest()
            // ->get();

            foreach ($totalLog as $log) {
                $propertyName = $propertyId = $buildingName = $type = '';
    
                $userInfoByUserId = $log->getUserDetail;
    
                $property = $log->getPropertyDetailByPropertyIdWithTrashed;
    
                $building = $log->getBuildingDetailWithTrashed;
    
                if ($log->type == 3) {
                    $vio = \App\Violation::where('barcode_id', $log->barcode_id)->withTrashed()->first();
                    
                    if (is_null($vio)) {
                        continue;
                    }
                    
                    if ($vio->type) {
                        $units = \App\RouteCheckIn::where('barcode_id', $log->barcode_id)
                            ->withTrashed()->first();
                    } else {
                        $units = \App\Units::where('barcode_id', $log->barcode_id)
                            ->withTrashed()->first();
                    }
                } else {
                    $units = \App\Units::where('barcode_id', $log->barcode_id)
                    ->withTrashed()->first();
                }
    
                if (isset($units->property_id)) {
                    $services = \App\Service::where('property_id', $units->property_id)
                        ->withTrashed()->first();
    
                    if ($log->type == 2 && $log->wast == 1 && $log->recycle == null) {
                        $type = 'Waste Total: ' . $services->waste_weight;
                    }
                    if ($log->type == 2 && $log->recycle == 1 && $log->wast == null) {
                        $type = 'Recycle Total: ' . $services->recycle_weight;
                    }
                    if ($log->type == 2 && $log->recycle == 1 && $log->wast == 1) {
                        $type = 'Waste Total:'
                                . $services->recycle_weight
                                . ', Recycle Total:'
                                . $services->waste_weight;
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
                        'employee_name' => ucwords($userInfoByUserId->title . ' '
                        . $userInfoByUserId->firstname . ' ' . $userInfoByUserId->lastname),
                    ];
                } elseif ($log->type == 8 || $log->type == 5) {
                    if (!is_null($log->barcode_id) && str_contains($log->barcode_id, '-RCP')) {
                        $units = \App\RouteCheckIn::where('barcode_id', $log->barcode_id)
                            ->withTrashed()->first();
                    }

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
                        'employee_name' => ucwords($userInfoByUserId->title
                            . ' ' . $userInfoByUserId->firstname
                            . ' ' . $userInfoByUserId->lastname),
                    ];
                }
            }

            $date = \Carbon\Carbon::today()->timezone($timezone)->format('m-d-Y');
            $time = time() . "(" . $date . ")";

            Excel::create(
                $time,
                function ($excel) use ($logsArray, $date) {
                    
                    // Set the spreadsheet title, creator, and description
                    $excel->setTitle('Delivery Report (' . $date . ')');
                    $excel->setDescription('Delivery Report');
                    // Build the spreadsheet, passing in the payments array
                    $excel->sheet(
                        'sheet1',
                        function ($sheet) use ($logsArray) {
                            $sheet->fromArray($logsArray, null, 'A1', false, false);
                        }
                    );
                }
            )
            ->store(
                'xls',
                public_path() . '/uploads/pdf/'
            );

            $link = url('/uploads/pdf') . '/' . $time . '.xls';
            $admins = $getEmployees->where('role_id', 1);

            \Notification::send($admins, new \App\Notifications\SendDeliveryReportExcel($link));
        }
    }
}
