<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class NewViolationNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:NewViolationNotification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send new violation count to property manager';

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

        $start = \Carbon\Carbon::parse(getStartEndTime()->startTime)->subDays('1');
        $end = \Carbon\Carbon::parse(getStartEndTime()->endTime)->subDays('1');

        $users = \App\User::select('id', 'email')
            ->where('role_id', \config('constants.propertyManager'))
            ->has('assignedproperties')
            ->with(
                [
                    'assignedproperties' => function ($query) {
                        $query->select('user_id', 'property_id');
                    },
                ]
            )
        ->get();
        
        foreach ($users as $user) {

                $mobile = '+1'.$user->mobile.'';

                //1078: Unsubscribe from email notification: Start
                $checkUser = \App\UserNotification::where(
                    [
                        'type' => 1,
                        'user_id' => $user->id,
                    ]
                )
                ->first();
                    
                $emailCheck = !is_null($checkUser) && empty($checkUser->email) ? false : true;
                $smsCheck = !is_null($checkUser) && empty($checkUser->sms) ? false : true;
                //1078: Unsubscribe from email notification: End
            
            $property = $user->assignedproperties;

            $barcodeId = \App\Units::select('barcode_id')
                ->whereIn('property_id', $property->pluck('property_id'))
                ->where('is_route', 0)
                ->get();

            $note = \App\Violation::select('id')
                ->where('manager_status', 0)
                ->whereBetween(
                    \DB::raw("convert_tz(updated_at,'UTC','america/new_york')"),
                    [
                        $start, 
                        $end
                    ]
                )
                ->whereIn('barcode_id', $barcodeId->pluck('barcode_id')->toArray())
            ->get();

            if ($note->isNotEmpty()) {
            
                $content = $note->count().' new violation created.';

                try {
                    if($emailCheck) {
                        \Notification::send($user, new \App\Notifications\NewViolation($content));
                    }

                    if ($smsCheck) {
                       sms($mobile, $content);
                    }
                } catch (\Exception $e) {
                    //echo 'Message: '.$e->getMessage();
                }
            }
        }


        // $start = \Carbon\Carbon::parse(getStartEndTime()->startTime)->subDays('1');
        // $end = \Carbon\Carbon::parse(getStartEndTime()->endTime)->subDays('1');

        // $count = \App\Property::withCount(
        //     [
        //         'getViolationByProperties' => function ($query) use ($start, $end) {
        //             $query->whereBetween(\DB::raw("convert_tz(violations.updated_at,'UTC','america/new_york')"), [$start, $end])
        //                     ->where('status', 2);
        //         },
        //     ]
        // )
        //         // ->withCount(['getNotesByProperties' => function($query) use($start, $end) {
        //         //         $query->whereBetween(\DB::raw("convert_tz(barcode_notes.updated_at,'UTC','america/new_york')"), [$start, $end])
        //         //         ->where('status', 2);
        //         //     }])
        // ->whereHas('getPropertyManger')
        // ->with('getPropertyManger')
        // ->get();

        // //Sent submitted violation count to property manager: Start
        // foreach ($count as $counts) {
        //     if (!empty($counts->get_violation_by_properties_count)) {
        //         $coun = $counts->get_violation_by_properties_count;
        //     } else {
        //         $coun = 'No';
        //     }

        //     $content = ''.$coun.' violations submitted.';
        //     $user = $counts->getPropertyManger;

        //     try {
        //         $a = \Notification::send($user, new \App\Notifications\NewViolation($content));
        //     } catch (\Exception $e) {
        //         //echo 'Message: ' .$e->getMessage();
        //     }
        // }
        //Sent submitted violation count to property manager: End

        //Sent submitted notes count to property manager: Start
        // foreach ($count as $counts) {

        //     if (!empty($counts->get_notes_by_properties_count)) {
        //         $co = $counts->get_notes_by_properties_count;
        //     } else {
        //         $co = "No";
        //     }

        //     $content = "" . $co . " notes submitted.";
        //     $user = $counts->getPropertyManger;
        //     $ifNoteViolation = 1;

        //     $a = \Notification::send($user, new \App\Notifications\NewViolation($content, $ifNoteViolation));

        // }
        //Sent submitted notes count to property manager: End
    }
}
