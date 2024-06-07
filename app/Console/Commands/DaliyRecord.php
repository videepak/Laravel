<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;

class DaliyRecord extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:DaliyRecord';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $totalPickup = collect();
        //$arrPickup = [];
        $properties = \App\Property::query()
            ->with(
                [
                    'service' => function ($query) {
                        $query->select('pickup_finish', 'pickup_start', 'pickup_type', 'property_id');
                    }
                ]
            )
        ->get();
        
        foreach ($properties as $property) {
            $subscriberId = $property->subscriber_id;
            $pickupType = $property->service->pickup_type;
            $pickupStart = $property->service->pickup_start;
            $pickupFinish = $property->service->pickup_finish;

            $users = \App\User::select('timezone')
                ->where('subscriber_id', $subscriberId)
                ->first();
            
            if (!isset($users->timezone)) {
                continue;
            }

            $timezone = \Carbon\Carbon::now()->timezone($users->timezone)->subDays(1);
            $startDate = $timezone->format('Y-m-d') . ' 06:00:00';
            $endDate = \Carbon\Carbon::now()->timezone($users->timezone)->format('Y-m-d') . ' 05:59:59';
            
            if ($pickupStart <= $startDate && $pickupFinish >= $endDate) {
            } else {
                continue;
            }
            
            $pickup = \App\Activitylogs::query()
                ->where('property_id', $property->id)
                // ->when(
                //     $pickupType == 1,
                //     function ($query) {
                //         $query->where('wast', 1);
                //     }
                // )
                // ->when(
                //     $pickupType == 2,
                //     function ($query) {
                //         $query->where('recycle', 1);
                //     }
                // )
                // ->when(
                //     $pickupType == 3,
                //     function ($query) {
                //         $query->where('wast', 1)
                //             ->where('recycle', 1);
                //     }
                // )
                ->where(
                    function ($query) {
                        $query->where('wast', 1)
                            ->orWhere('recycle', 1);
                    }
                )
                ->where('type', 2)
                ->whereBetween(
                    \DB::raw("convert_tz(updated_at,'UTC','" . $users->timezone . "')"),
                    [
                        $startDate,
                        $endDate,
                    ]
                )
            ->get();

            $checkpointScanned = \App\Activitylogs::query()
                ->where('property_id', $property->id)
                ->where('type', 11)
                ->whereBetween(
                    \DB::raw("convert_tz(updated_at,'UTC','" . $users->timezone . "')"),
                    [
                        $startDate,
                        $endDate,
                    ]
                )
            ->get();

            $totalWalkThroughs = \App\Activitylogs::query()
                ->where('property_id', $property->id)
                ->where('type', 8)
                ->whereBetween(
                    \DB::raw("convert_tz(updated_at,'UTC','" . $users->timezone . "')"),
                    [
                        $startDate,
                        $endDate,
                    ]
                )
            ->get();

            // Total Active Unit : Start
            $totalUnit = \App\Units::query()
                //->select('property_id', \DB::raw('count(*) as unitActives'))
                ->select('property_id')
                //->groupBy('property_id')
                ->where('property_id', $property->id)
                ->where('is_active', 1)
                ->where('is_route', 0)
            ->get();
            // Total Active Unit : End

            //Total Checkpoints By Property: Start
            $totalCheckpoint = \App\Units::query()
                //->select('property_id', \DB::raw('count(*) as totalCheckpoint'))
                ->select('property_id')
                ->where('property_id', $property->id)
                //->groupBy('property_id')
                ->where('is_route', 1)
            ->get();
            //Total Checkpoints By Property: End

            // Total Active Building : Start
            $buildingActive = \App\Building::query()
                ->select('id')
                ->whereIn(
                    'id',
                    function ($query) use ($property) {
                        $query->select('building_id')
                            ->from('units')
                            ->where('property_id', $property->id)
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
                            )
                            ->groupBy('building_id');
                    }
                )
            ->get();
            // Total Active Building : 
            
            $totalTask = \App\Tasks::query()
            ->whereIn(
                'id',
                function ($query) use ($property) {
                    $query->select('task_id')
                        ->from('task_assigns')
                        ->where('property_id', $property->id);
                }
            )
            ->where('start_date', '<=', \Carbon\Carbon::now()->setTimezone($users->timezone))
            ->where('end_date', '>=', \Carbon\Carbon::now()->setTimezone($users->timezone))
            ->get();
            
            $taskComplete = \App\Activitylogs::query()
                ->where('property_id', $property->id)
                ->where('type', 13)
                ->whereBetween(
                    \DB::raw("convert_tz(updated_at,'UTC','" . $users->timezone . "')"),
                    [
                        $startDate,
                        $endDate,
                    ]
                )
            ->get();
            
            $missedCheckout = \App\PropertiesCheckIn::query()
                ->select('id')
                ->where('property_id', $property->id)
                ->whereNotNull('reason')
                ->whereBetween(
                    \DB::raw("convert_tz(created_at,'UTC','" . $users->timezone . "')"),
                    [
                        $startDate,
                        $endDate,
                    ]
                )
            ->get();
            // Capture Total time on property (first check in and last check out) : Start
            $checkIn = \App\PropertiesCheckIn::query()
                ->select('id', 'created_at', 'updated_at')
                ->where('property_id', $property->id)
                ->where('check_in', 1)
                ->whereBetween(
                    \DB::raw("convert_tz(created_at,'UTC','" . $users->timezone . "')"),
                    [
                        $startDate,
                        $endDate,
                    ]
                )
            ->first();

            $checkOut = \App\PropertiesCheckIn::query()
                ->select('id', 'created_at', 'updated_at')
                ->where('property_id', $property->id)
                ->where('check_in_complete', 1)
                ->whereBetween(
                    \DB::raw("convert_tz(created_at,'UTC','" . $users->timezone . "')"),
                    [
                        $startDate,
                        $endDate,
                    ]
                )
                ->latest()
            ->first();
                
            if (!is_null($checkIn) && !is_null($checkOut)) {
                $checkInOutTime = $checkIn->created_at->diff($checkOut->updated_at)
                    ->format('%H:%I:%S');
            } else {
                $checkInOutTime = null;
            }
            // Capture Total time on property (first check in and last check out) : End

            $arrPickup [] = [
                'property_id' => $property->id,
                'pickup_completed' => $pickup->count(),
                'active_units' => $totalUnit->count(),
                'route_checkpoints_scanned' => $checkpointScanned->count(),
                'checkpoints_by_property' => $totalCheckpoint->count(),
                'building_walk_throughs' => $totalWalkThroughs->count(),
                'active_building' => $buildingActive->count(),
                'checkinout_duration' => $checkInOutTime,
                'total_tasks_completed' => $taskComplete->count(),
                'total_task' => $totalTask->count(),
                'missed_property_checkouts' => $missedCheckout->count(),
                'record_date' => Carbon::now()->subDays(1)->format('Y-m-d') . ' 06:00:00',
                'created_at' => Carbon::now()->toDateTimeString(),
                'updated_at' => Carbon::now()->toDateTimeString()
                //'start' => $startDate,
                //'end' => $endDate,
                //'timezone' => $users->timezone
            ];
        }
        
        \App\DaliyRecords::insert($arrPickup);
    }
}
