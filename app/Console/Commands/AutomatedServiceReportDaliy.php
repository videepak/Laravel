<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AutomatedServiceReportDaliy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:AutomatedServiceReportDaliy';

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

        #1314: Automated Violation Report for Bin Tag List: Start
        $users = \App\Subscriber::select('id', 'user_id')
            ->with(
                [
                    'user' => function ($query) {
                        $query->select('id', 'email', 'subscriber_id', 'is_admin', 'timezone');
                    },
                    'employees' => function ($query) {
                        $query->select('id', 'email', 'subscriber_id', 'is_admin', 'role_id', 'timezone', 'deleted_at')
                            ->withTrashed();
                    },
                    'getProperties' => function ($query) {
                        $query->select('id', 'subscriber_id')
                            ->withTrashed();
                    },
                ]
            )
            ->whereHas(
                'getNotification',
                function ($query) {
                    $query->where('type', 10)
                        ->where('day_frequency', 1);
                }
            )
        ->get();
        
        if ($users->isNotEmpty()) {
            automatedViolationReport($users, 'Daily');
        }
        #1314: Automated Violation  Report for Bin Tag List: End
        
        #1352: Automated Service report: Start
        $users = \App\Subscriber::select('id', 'user_id')
            ->with(
                [
                    'user' => function ($query) {
                        $query->select('id', 'email', 'subscriber_id', 'is_admin', 'timezone');
                    },
                    'employees' => function ($query) {
                        $query->select('id', 'email', 'subscriber_id', 'is_admin', 'role_id', 'timezone', 'deleted_at')
                            ->withTrashed();
                    },
                    'getProperties' => function ($query) {
                        $query->select('id', 'subscriber_id')
                            ->withTrashed();
                    },
                ]
            )
            ->whereHas(
                'getNotification',
                function ($query) {
                    $query->where('type', 7)
                        ->where('day_frequency', 1);
                }
            )
        ->get();

        if ($users->isNotEmpty()) {
            automatedServiceReport($users, 'Daily');
        }
        #1352: Automated Service report: Start
        
        #1352: Automated Clock in/out report: Start
        $clock = \App\Subscriber::select('id', 'user_id')
            ->with(
                [
                    'user' => function ($query) {
                        $query->select('id', 'email', 'subscriber_id', 'is_admin', 'timezone');
                    },
                    'employees' => function ($query) {
                        $query->select('id', 'email', 'subscriber_id', 'is_admin', 'role_id', 'timezone', 'deleted_at');
                    },
                    // 'getNotification' => function ($query) {
                    //     $query->select('day_frequency', 'subscriber_id', 'type')
                    //         ->where('type', 9);
                    // }
                ]
            )
            ->whereHas(
                'getNotification',
                function ($query) {
                    $query->where('type', 9)
                        ->where('day_frequency', 1);
                }
            )
        ->get();

        if ($clock->isNotEmpty()) {
            automatedClockInOutReport($clock, 'Daily');
        }
        #1352: Automated Clock in/out report: End
        
        #1314: Automated Report for Bin Tag List: Start
        $use = \App\Subscriber::select('id', 'user_id')
            ->with(
                [
                    'user' => function ($query) {
                        $query->select('id', 'email', 'subscriber_id', 'is_admin', 'timezone');
                    },
                    'employees' => function ($query) {
                        $query->select('id', 'email', 'subscriber_id', 'is_admin', 'role_id', 'timezone', 'deleted_at');
                    },
                    'getProperties' => function ($query) {
                        $query->select('id', 'subscriber_id', 'name')
                            ->with(
                                [
                                    'getUnit' => function ($query) {
                                        $query->select('id', 'address1', 'address2', 'unit_number', 'activation_date', 'property_id', 'building_id', 'latitude', 'longitude', 'building', 'barcode_id', 'created_at', 'updated_at', 'floor', 'is_active', 'is_route')
                                            ->where(
                                                function ($query) {
                                                    $query->where(
                                                        function ($query) {
                                                            $query->where('is_route', 0)
                                                                ->where('is_active', 1);
                                                        }
                                                    )
                                                    ->orWhere('is_route', 1);
                                                }
                                            );
                                    }
                                ]
                            )
                            ->whereHas(
                                'getUnit',
                                function ($query) {
                                    $query->where(
                                        function ($query) {
                                            $query->where(
                                                function ($query) {
                                                    $query->where('is_route', 0)
                                                        ->where('is_active', 1);
                                                }
                                            )
                                            ->orWhere('is_route', 1);
                                        }
                                    );
                                }
                            );
                    },
                    // 'getNotification' => function ($query) {
                    //     $query->select('day_frequency', 'subscriber_id', 'type')
                    //         ->where('type', 8);
                    // }
                ]
            )
            ->whereHas(
                'getNotification',
                function ($query) {
                    $query->where('type', 8)
                        ->where('day_frequency', 1);
                }
            )
        ->get();
                
        if ($use->isNotEmpty()) {
            automatedUnitReport($use, 'Daily');
        }
        #1314: Automated Report for Bin Tag List: End
    }
}
