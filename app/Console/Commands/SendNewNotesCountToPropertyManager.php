<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendNewNotesCountToPropertyManager extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:SendNewNotesCountToPropertyManager';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notification to property manager for new notes with 24 hours ';

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

        $users = \App\User::select('id', 'email', 'mobile')
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
    
   
        //sms('+12027359961', 'testing'); dd('789');
   
        foreach ($users as $user) {
            $mobile = '+1' . $user->mobile . '';

            //1078: Unsubscribe from email notification: Start
            $checkUser = \App\UserNotification::where(
                [
                    'type' => 2,
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
                ->get();

            $note = \App\BarcodeNotes::select('id')
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
                $content = $note->count() . ' new notes created.';

                try {
                    if ($emailCheck) {
                        \Notification::send($user, new \App\Notifications\NewNotes($content));
                    }

                    if ($smsCheck) {
                        sms($mobile, $content);
                    }
                } catch (\Exception $e) {
                    //echo 'Message: '.$e->getMessage();
                }
            }
        }
    }
}
