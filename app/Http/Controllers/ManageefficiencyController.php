<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Units;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Role;
use App\Subscriber;
use App\Subscription;
use App\UserProperties;
use App\Property;
use App\Service;
use App\Customer;
use DB;
use App\Activitylogs;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Pagination\LengthAwarePaginator;

class ManageefficiencyController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->middleware('RoleAndPermission:report');
    }

    public function index(Request $request)
    {
        $bulidingCount = $sumWalkThroughDone = $sumWalkThroughNotDone = 0;
        $arr = $totalBuilding = $walkThroughDone = $excPropertyDate = $excBuildingDate = $excBuildingid = $buildId = [];

        $properties = $this->propertyList()
            ->whereHas(
                'getUnit',
                function ($query) {
                    $query->select('id')
                        ->where('is_active', 1)
                        ->where('is_route', 0);
                }
            )
            ->withCount('getBuildingIsActiveUnit')
            ->with(
                [
                    'todayHasProperty' => function ($query) {
                        $query->select('id', 'property_id', 'day');
                    },
                    'getBuildingIsActiveUnit' => function ($query) {
                        $query->select('id', 'property_id');
                    },
                    'service' => function ($query) {
                        $query->select('id', 'pickup_start', 'pickup_finish', 'pickup_type', 'property_id', 'waste_reduction_target');
                    }
                ]
            )
        ->get();

        $startDate = \App\Service::select('pickup_start')
            //->whereIn('property_id1', $getProperty->pluck('id'))
            ->whereRaw("property_id in (" . $properties->implode('id', ', ') . ")")
            ->orderBy('pickup_start')->first();   

        if (isset($properties) && $properties->isNotEmpty()) {
            $startOfWeek = \Carbon\Carbon::parse($startDate->pickup_start)
                ->format('Y-m-d H:i:s');

            $endOfWeek = \Carbon\Carbon::now()->addDays(1)
                ->format('Y-m-d') . " 05:59:59";

            $userStartTime = \Carbon\Carbon::now()
                ->timezone(getUserTimezone())->format('Y-m-d H:i:s');

            foreach ($properties as $property) { //dd($property);
                //Get property days: Start
                $todayHasProperty = $property->todayHasProperty;

                $day = $todayHasProperty->pluck('day');
                //Get property days: End

                $service = $property->service;

                $serviceStartDate = \Carbon\Carbon::parse($service->pickup_start)
                    ->format('Y-m-d H:i:s');

                $serviceEndDate = \Carbon\Carbon::parse($service->pickup_finish)
                    ->format('Y-m-d H:i:s');

                while ($startOfWeek <= $endOfWeek) {
                    $dayNumber = $this->getCurrentDay($startOfWeek);
                    $startEnd = \Carbon\Carbon::parse($startOfWeek)
                        ->addDays(1)->format('Y-m-d') . " 05:59:59";
                    
                    if (isset($service) && in_array($dayNumber, $day->toArray()) && ($serviceStartDate <= $startOfWeek && $serviceEndDate >= $startEnd) && ($startOfWeek <= $userStartTime && $startEnd >= $startOfWeek)) {
                        /* $excludedProperties = $property->exculdeProperty
                            ->contains(
                                function ($value, $key) use ($startOfWeek, $startEnd) {
                                    if ($value->updated_at > $startOfWeek && $value->updated_at < $startEnd) {
                                        return $value;
                                    }
                                }
                            ); */

                        $excludedProperties = \App\ExcludedProperty::select('id')
                            ->where('property_id', $property->id)
                            ->whereBetween(
                                DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                                [$startOfWeek, $startEnd]
                            )
                        ->get();
                                //dd($excludedProperties, $excludedProperties1);
                        if ($excludedProperties->isEmpty()) {
                        //if ($excludedProperties) {
                            if ($property->type == 1 || $property->type == 3 || $property->type == 4) {
                                $walkThrough = \App\walkThroughRecord::select('id')
                                    ->where('property_id', $property->id)
                                    ->whereBetween(
                                        DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                                        [$startOfWeek, $startEnd]
                                )
                                ->get();

                                $totalBuilding[] = 1;
                                $walkThroughDone[] = $walkThrough->count();
                            } elseif ($property->type == 2) {
                                $getBuildings = $property->getBuildingIsActiveUnit;

                                foreach ($getBuildings as $getBuilding) {
                                    $excbuild = \App\ExcludedProperty::select('id')
                                        ->where('property_id', $property->id)
                                        ->where('building_id', $getBuilding->id)
                                        ->whereBetween(
                                            DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                                            [$startOfWeek, $startEnd]
                                        )
                                    ->get();

                                    if ($excbuild->isEmpty()) {
                                        $walkThrough = \App\walkThroughRecord::select('id')
                                            ->where('property_id', $property->id)
                                            ->where('building_id', $getBuilding->id)
                                            ->whereBetween(
                                                DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                                                [$startOfWeek, $startEnd]
                                            )
                                        ->get();

                                        $totalBuilding[] = 1;
                                        $walkThroughDone[] = $walkThrough->count();
                                    }
                                }
                            }
                        }

                        $excludedProperties = \App\ExcludedProperty::select('id')
                            ->where('property_id', $property->id)
                            ->where(
                                function ($query) {
                                    $query->where('building_id', '!=', '0')
                                        ->whereNotNull('building_id');
                                }
                            )
                            ->whereBetween(
                                DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                                [$startOfWeek, $startEnd]
                            )
                        ->get();

                        if ($excludedProperties->isNotEmpty()) {
                            $getBuildings = $property->getBuildingIsActiveUnit;

                            foreach ($getBuildings as $getBuilding) {
                                $excbuild = \App\ExcludedProperty::select('id')
                                    ->where('property_id', $property->id)
                                    ->where('building_id', $getBuilding->id)
                                    ->whereBetween(
                                        DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                                        [$startOfWeek, $startEnd]
                                    )
                                ->get();

                                if ($excbuild->isEmpty()) {
                                    $walkThrough = \App\walkThroughRecord::select('id')
                                        ->where('property_id', $property->id)
                                        ->where('building_id', $getBuilding->id)
                                        ->whereBetween(
                                            DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                                            [$startOfWeek, $startEnd]
                                        )
                                    ->get();

                                    $totalBuilding[] = 1;
                                    $walkThroughDone[] = $walkThrough->count();
                                }
                            }
                        }
                    }
                    
                    $startOfWeek = \Carbon\Carbon::parse($startOfWeek)->addDays(1);
                }

                $startOfWeek = \Carbon\Carbon::parse($startDate->pickup_start);
            }
            
            $sumWalkThroughDone = array_sum($walkThroughDone);
            $sumWalkThroughNotDone = array_sum($totalBuilding);
        }

        $this->data['completed'] = $sumWalkThroughDone;
        $this->data['notCompleted'] = $sumWalkThroughNotDone - $sumWalkThroughDone;

        return view("efficiency/efficiency", $this->data);
    }

    public function indexBackup(Request $request)
    {   
        $bulidingCount = $sumWalkThroughDone = $sumWalkThroughNotDone = 0;
        $arr = $totalBuilding = $walkThroughDone = $excPropertyDate = $excBuildingDate = $excBuildingid = $buildId = [];

        //Get service start date: Start
        $startDate = \App\Service::when(
            $this->user->hasRole('admin'),
            function ($query) {
                $query->whereIn(
                    'property_id',
                    function ($query) {
                        $query->select('id')
                            ->from('properties')
                            ->where('subscriber_id', $this->user->subscriber_id)
                            ->whereNull('deleted_at');
                    }
                );
            }
        )//Only for employee: Start        
        ->when(
            !$this->user->hasRole(['admin', 'property_manager']),
            function ($query) {
                $query->whereIn(
                    'property_id',
                    function ($query) {
                        $query->select('property_id')
                            ->from('user_properties')
                            ->where('user_id', $this->user->id)
                            ->whereNull('deleted_at');
                    }
                )
                ->orWhereIn(
                    'property_id',
                    function ($query) {
                        $query->select('id')
                            ->from('properties')
                            ->where('user_id', $this->user->id)
                            ->whereNull('deleted_at');
                    }
                );
            }
        )
        ->when(
            $this->user->hasRole('property_manager'),
            function ($query) {
                $query->where(
                    function ($query) {
                        $query->whereIn(
                            'property_id',
                            function ($query) {
                                $query->select('property_id')
                                    ->from('user_properties')
                                    ->where("user_id", $this->user->id)
                                    ->whereNull("deleted_at");
                            }
                        );
                    }
                );
            }
        )
        ->orderBy('pickup_start')->first();
        //Get service start date: End
        //Get property detail: Start
        $properties = \App\Property::when(
            !$this->user->hasRole(['admin', 'property_manager']),
            function ($query) {
                $query->whereIn(
                    'id',
                    function ($query) {
                        $query->select('property_id')
                            ->from('user_properties')
                            ->where('user_id', $this->user->id)
                            ->whereNull('deleted_at');
                    }
                )
                ->orWhereIn(
                    'id',
                    function ($query) {
                        $query->select('id')
                            ->from('properties')
                            ->where('user_id', $this->user->id)
                            ->whereNull('deleted_at');
                        }
                    );
            }
        )
        ->when(
            $this->user->hasRole('admin'),
            function ($query) {
                $query->where('subscriber_id', $this->user->subscriber_id);
            }
        )
        ->when(
            $this->user->hasRole('property_manager'),
            function ($query) {
                $query->where(
                    function ($query) {
                        $query->whereIn(
                            'id',
                            function ($query) {
                                $query->select('property_id')
                                    ->from('user_properties')
                                    ->where("user_id", $this->user->id)
                                    ->whereNull("deleted_at");
                            }
                        );
                    }
                );
            }
        )
        ->whereHas(
            'getUnit',
            function ($query) {
                $query->where('is_active', 1)
                ->where('is_route', 0);
            }
        )
        ->withCount('getBuildingIsActiveUnit')
        ->get();
        //Get property detail: End

        if (isset($properties) && $properties->isNotEmpty()) {
            $startOfWeek = \Carbon\Carbon::parse($startDate->pickup_start)
                ->format('Y-m-d H:i:s');
            $endOfWeek = \Carbon\Carbon::now()->addDays(1)
                ->format('Y-m-d') . " 05:59:59";
            $userStartTime = \Carbon\Carbon::now()
                ->timezone(getUserTimezone())->format('Y-m-d H:i:s');

            foreach ($properties as $property) {
                //Get property days: Start
                $todayHasProperty = $property->todayHasProperty;
                $day = $todayHasProperty->map(
                    function ($val, $key) {
                        return $val->day;
                    }
                );
                //Get property days: End

                $service = $property->service;
                $serviceStartDate = \Carbon\Carbon::parse($service->pickup_start)
                    ->format('Y-m-d H:i:s');
                $serviceEndDate = \Carbon\Carbon::parse($service->pickup_finish)
                    ->format('Y-m-d H:i:s');

                while ($startOfWeek <= $endOfWeek) {
                    $dayNumber = $this->getCurrentDay($startOfWeek);
                    $startEnd = \Carbon\Carbon::parse($startOfWeek)
                        ->addDays(1)->format('Y-m-d') . " 05:59:59";
                    
                    if (isset($service) && in_array($dayNumber, $day->toArray()) && ($serviceStartDate <= $startOfWeek && $serviceEndDate >= $startEnd) && ($startOfWeek <= $userStartTime && $startEnd >= $startOfWeek)) {
                        $excludedProperties = \App\ExcludedProperty::where('property_id', $property->id)
                            ->whereBetween(
                                DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                                [$startOfWeek, $startEnd]
                            )
                        ->get();

                        if ($excludedProperties->isEmpty()) {
                            if ($property->type == 1
                                    || $property->type == 3
                                    || $property->type == 4) {
                                $walkThrough = \App\walkThroughRecord::where('property_id', $property->id)
                                    ->whereBetween(
                                        DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                                            [$startOfWeek, $startEnd]
                                    )
                                ->get();

                                $totalBuilding[] = 1;
                                $walkThroughDone[] = $walkThrough->count();
                            } elseif ($property->type == 2) {
                                $getBuildings = $property->getBuildingIsActiveUnit()->get();

                                foreach ($getBuildings as $getBuilding) {
                                    $excbuild = \App\ExcludedProperty::where('property_id', $property->id)
                                        ->where('building_id', $getBuilding->id)
                                        ->whereBetween(
                                            DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                                            [$startOfWeek, $startEnd]
                                        )
                                    ->get();

                                    if ($excbuild->isEmpty()) {
                                        $walkThrough = \App\walkThroughRecord::where('property_id', $property->id)
                                            ->where('building_id', $getBuilding->id)
                                            ->whereBetween(
                                                DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                                                [$startOfWeek, $startEnd]
                                            )
                                        ->get();

                                        $totalBuilding[] = 1;
                                        $walkThroughDone[] = $walkThrough->count();
                                    }
                                }
                            }
                        }

                        $excludedProperties = \App\ExcludedProperty::where('property_id', $property->id)
                                ->where(
                                    function ($query) {
                                        $query->where('building_id', '!=', '0')
                                        ->whereNotNull('building_id');
                                    }
                                )
                                ->whereBetween(
                                    DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                                    [$startOfWeek, $startEnd]
                                )
                                ->get();

                        if ($excludedProperties->isNotEmpty()) {
                            $getBuildings = $property->getBuildingIsActiveUnit()->get();

                            foreach ($getBuildings as $getBuilding) {
                                $excbuild = \App\ExcludedProperty::where('property_id', $property->id)
                                        ->where('building_id', $getBuilding->id)
                                        ->whereBetween(
                                            DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                                            [$startOfWeek, $startEnd]
                                        )
                                        ->get();

                                if ($excbuild->isEmpty()) {
                                    $walkThrough = \App\walkThroughRecord::where('property_id', $property->id)
                                            ->where('building_id', $getBuilding->id)
                                            ->whereBetween(
                                                DB::raw("convert_tz(updated_at,'UTC','" . getUserTimezone() . "')"),
                                                [$startOfWeek, $startEnd]
                                            )
                                            ->get();

                                    $totalBuilding[] = 1;
                                    $walkThroughDone[] = $walkThrough->count();
                                }
                            }
                        }
                    }
                    $startOfWeek = \Carbon\Carbon::parse($startOfWeek)->addDays(1);
                }
                $startOfWeek = \Carbon\Carbon::parse($startDate->pickup_start);
            }
            // die('...');
            $sumWalkThroughDone = array_sum($walkThroughDone);
            $sumWalkThroughNotDone = array_sum($totalBuilding);
        }

        $this->data['completed'] = $sumWalkThroughDone;
        $this->data['notCompleted'] = $sumWalkThroughNotDone - $sumWalkThroughDone;

        //dump($arr);
        return view("efficiency/efficiency", $this->data);
    }

    protected function checkBuildExcludeDate($start, $end, $exclude)
    {

        $a = [];

        foreach ($exclude as $key => $date) {
            $result = $this->checkInRange($start, $end, $date);

            if ($result) {
                $a[] = $key;
            }
        }
        return $a;
    }

    protected function checkExcludeDate($start, $end, $exclude)
    {
        foreach ($exclude as $date) {
            $result = $this->checkInRange($start, $end, $date);

            if ($result) {
                return false;
                break;
            }
        }
        return true;
    }

    private function checkInRange($startDate, $endDate, $dateFromUser)
    {
        // Convert to timestamp
        $startTs = strtotime($startDate);
        $endTs = strtotime($endDate);
        $userTs = strtotime($dateFromUser);

        // Check that user date is between start & end
        return (($userTs >= $startTs) && ($userTs <= $endTs));
    }

    private function getCurrentDay($date)
    {

        $currentDay = \Carbon\Carbon::parse($date)->subHours(6)->format('l');

        switch ($currentDay) {
            case "Monday":
                return 0;
                break;
            case "Tuesday":
                return 1;
                break;
            case "Wednesday":
                return 2;
                break;
            case "Thursday":
                return 3;
                break;
            case "Friday":
                return 4;
                break;
            case "Saturday":
                return 5;
                break;
            case "Sunday":
                return 6;
                break;
        }
    }
}
