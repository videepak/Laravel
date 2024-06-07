<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Resident;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Mail;
use App\Mail\ResidentSendEmail;
use Excel;
use Response;
use App\ResidentsUnit;
use Illuminate\Validation\Rule;
use App\ResidentEmailHistory;

class ResidentController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

    }

    public function index()
    {
        if (!$this->user->hasRole(['property_manager'])) {
            return redirect('unauthorized');
        }
        $getPropertyTemplate = \App\TemplateContent::where('is_user', 2)->get();
        $this->data['residentTemplate'] = $getPropertyTemplate;

        return view('residents.list', $this->data);
    }

    public function getResidentList(Request $request)
    {
        $i = $request->start + 1;
        $residentArray = [];
        $search = $request->search['value'];

        //Get total result:Start
        $resident = \App\Resident::whereIn('property_id',$this->propertyList()->pluck('id'))
                ->orWhere('user_id', '=' ,  $this->user->id)
                ->when(
                !empty($search),
                function ($query) use ($search) {
                    $query->where(
                        function ($query) use ($search) {
                            $query->where('mobile', 'like', "%$search%")
                                ->orWhere('email', 'like', "%$search%")
                               // ->orWhere('move_in_date', 'like', "%$search%")
                               // ->orWhere('move_out_date', 'like', "%$search%")
                                ->orWhere(\DB::raw("CONCAT_WS(' ', `firstname`, `lastname`)"), 'like', "%$search%");
                        }
                    );
                }
            )
        ->get();
        //Get total result:End
        //Get result with limit:Start (Todo: merge the both queries)
        $residentsList = \App\Resident::select('id','firstname','lastname','mobile','email','unit_id','is_alert')
                ->whereIn('property_id',$this->propertyList()->pluck('id'))
                ->orWhere('user_id', '=' ,  $this->user->id)
                ->when(
                !empty($search),
                function ($query) use ($search) {
                    $query->where(
                        function ($query) use ($search) {
                            $query->where('mobile', 'like', "%$search%")
                                ->orWhere('email', 'like', "%$search%")
                                // ->orWhere('move_in_date', 'like', "%$search%")
                                // ->orWhere('move_out_date', 'like', "%$search%")
                                ->orWhere(\DB::raw("CONCAT_WS(' ', `firstname`, `lastname`)"), 'like', "%$search%");
                        }
                    );
                }
            )
            ->with(
                [
                    'getUnit' => function ($query) {
                        $query->select('id','unit_number','property_id')
                            ->where('is_active', 1)
                            ->where('is_route', 0);
                        }
                ]
            )
            ->latest()
            ->limit($request->length)->offset($request->start)
            ->get();
        //Get result with limit:End
        
        if (count($residentsList) > 0) {
            foreach ($residentsList as $residentt) {
                //Prepare the action link :Start
                $unitName = '-';
                $property = '';
                $unit_data = '';
                $concatName = $this->user->firstname.' '.$this->user->lastname;
                $cotactdetail = $this->user->mobile;
                $action = "<a href='" . url('property-manager/resident/' . $residentt->id) . "' onclick='return deleteResident(this, event);' title='Delete' ><li class='fa fa-trash-o'></li></a>";

                $action .= " | <a href='" . url('property-manager/resident/' . $residentt->id . '/edit/') . "' title='Edit'><li class='fa fa-edit'></li></a>";

                $action .= " | <a href='javascript:void(0);' title='Send Mail' class='resident-mail' data-name='Hello ".$residentt->firstname."' data-resident='".$concatName."' data-contact='".$cotactdetail."' ><li class='fa fa-mail-forward'></li></a> ";
                
                $action .= "| <a href='".url('property-manager/email-history/'.$residentt->id)."' id='resiHistory' class='resiHistory' title='Email History' ><i class='fa fa-history'></i></a> ";

                $getResidentCount = \App\ResidentsUnit::where('residents_id',$residentt->id)
                                    ->where('unit_id',$residentt->unit_id)
                                    ->where('violation_id','!=',0)
                                    ->withTrashed()
                                    ->count();
                // $count = 'green';
                // $action .= "| <a href='#'><span class='badge bg-$count'>" . $getResidentCount . '</span></a>';
                
                // $dateObject = \Carbon\Carbon::parse($residentt->move_in_date)->format('m-d-Y');
                // $dateObjmove = \Carbon\Carbon::parse($residentt->move_out_date)->format('m-d-Y');
                if ($residentt->is_alert != 0)
                {
                    $serviceResident = "<a href='javascript:void(0);' class='service_alert' title='subscribed'><i class='fa-solid fa-circle-check' style='color: #008000;'></i></a>";
                }
                else
                {
                    $serviceResident = "<a href='javascript:void(0);' class='service_alert' title='not subscribed yet'><i class='fa-sharp fa-solid fa-circle-xmark' style='color: #ec0000;'></i></a>";
                }
                if (isset($residentt->getUnit) && !empty($residentt->getUnit->unit_number))
                {
                    $unitName = $residentt->getUnit->unit_number;
                    $unit_data = $residentt->getUnit->id;
                    $property = $residentt->getUnit->property_id;
                }
                $residentArray[] = [
                    'id' => $i++,
                    'resident_id' => $residentt->id,
                    'property_id' => $property,
                    'name' => ucwords($residentt->firstname . ' ' . $residentt->lastname),
                    'mobile' => $residentt->mobile,
                    'email' => $residentt->email,
                    'unit_id' => $unitName,
                    'unit_data' => $unit_data,
                    'violation' => $getResidentCount,
                    'service' => $serviceResident,
                    //'move_in_date' => $dateObject,
                    //'move_out_date' => $dateObjmove,
                    'action' => $action,
                ];
            }
        }

        return json_encode(
            [
                'draw' => intval($request->draw),
                'recordsTotal' => !empty($resident) ? $resident->count() : 0,
                'recordsFiltered' => !empty($resident) ? $resident->count() : 0,
                'data' => $residentArray,
            ]
        );
    }

    public function destroy($id)
    {
        if (!$this->user->hasRole(['property_manager'])) 
        {
            return redirect('unauthorized');
        }
            $resi = \App\Resident::find($id);
            $resi->delete();

            $class = 'success';
            $message = 'Resident deleted successfully.';
            $data = [
                'title' => 'Manage Resident',
                'text' => $message,
                'class' => $class,
            ];

            return redirect('property-manager/resident')->with('status', $data);
    }

    public function show($id)
    {
    }

    public function create()
    {
        $unitEdit = [];
        $unitDetails = $this->propertyList()
                ->with(
                [
                    'getUnit' => function ($query) {
                        $query->select('id as value','property_id','unit_number as text')
                            ->where('is_active', 1)
                            ->where('is_route', 0)
                            ->whereNotIn(
                                'id', 
                                function($query){
                                    $query->select('unit_id')
                                        ->from('residents')
                                        ->whereNull('deleted_at');
                                }
                            );
                    }
                ]
            )
        ->get();
        foreach($unitDetails as $property){
            foreach($property->getUnit as $unit){
                $unitEdit[] = [
                    'value' => $unit->value,
                    'text' => $property->name ." - ". $unit->text
                ];
            }
        }
       
        $this->data['unitDetails'] = $unitEdit;
  
        return view('residents/create', $this->data);
    }
    
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'first_name' => 'required',
                'last_name' => 'required',
                'mobile' => 'required|min:10|max:10',
                'email' => [
                            'required',
                            'email',
                            Rule::unique('residents')
                                ->where(function ($query) 
                                {
                                    return $query->where('subscriber_id', $this->user->subscriber_id);
                                }
                            )
                    ],
                //'move_in_date' => 'required',
            ]
        );
        
        //$dateObject = \Carbon\Carbon::createFromFormat('d-m-Y',$request->move_in_date)->format('Y-m-d');

        $resident = new Resident();

        $resident->firstname = $request->first_name;
        $resident->lastname = $request->last_name;
        $resident->mobile = $request->mobile;
        $resident->email = $request->email;
        $resident->unit_id = 0;
        $resident->property_id = 0;
       // $resident->move_in_date = !empty($dateObject) ? $dateObject : '';
        $resident->user_id = $this->user->id;
        $resident->subscriber_id = $this->user->subscriber_id;
        
        $status = $resident->save();
        
        // $storeResidentUnit = new ResidentsUnit();

        // $storeResidentUnit->residents_id = $resident->id;
        // $storeResidentUnit->unit_id = $resident->unit_id;
        // $storeResidentUnit->violation_id = 0;

        // $storeResidentUnit->save();

        $class = ($status) ? 'success' : 'error';
        $message = ($status) ? 'Resident created successfully.' : 'Resident creation failed.';

        $data = [
            'title' => 'Resident',
            'text' => $message,
            'class' => $class,
        ];

        return redirect('property-manager/resident')->with('status', $data);
    }

    public function edit($id)
    {
        if (!$this->user->hasRole(['property_manager'])) {
            return redirect('unauthorized');
        }
        $resident = \App\Resident::select('id','firstname','lastname','mobile','email','unit_id')
                    ->where('id',$id)
                    ->with(
                        [
                            'getUnit' => function ($query) {
                                $query->select('id','unit_number','property_id')
                                ->with(
                                    [
                                        'getPropertyDetail' => function ($query) {
                                            $query->select('id','name');
                                        }
                                    ]);
                                }
                        ])
                    ->first();
        
        $unitDetails = $this->propertyList()
            ->with(
                [
                    'getUnit' => function ($query) {
                        $query->select('id as value','property_id','unit_number as text')
                            ->where('is_active', 1)
                            ->where('is_route', 0);
                        }
                ]
            )
        ->get();
     
        foreach($unitDetails as $property){
            foreach($property->getUnit as $unit){
                $unitEdit[] = [
                    'value' => $unit->value,
                    'text' => $property->name ." - ". $unit->text
                ];
            }
        }


        $this->data['resident'] = $resident;
        
        $this->data['unitDetails'] = $unitEdit;

        return view('residents/create', $this->data);
    }

    public function update(Request $request, $id)
    {
        if (!$this->user->hasRole(['property_manager'])) {
            return redirect('unauthorized');
        }
        $this->validate(
            $request,
            [
                'first_name' => 'required',
                'last_name' => 'required',
                'mobile' => 'required|min:10|max:10',
                'email' => 'required',
                //'move_in_date' => 'required',
                //'move_out_date' => 'required',

            ]
        );

        // $dateMoveIn = \Carbon\Carbon::createFromFormat('d-m-Y',$request->move_in_date)->format('Y-m-d');
        // $dateMoveOut = \Carbon\Carbon::createFromFormat('d-m-Y',$request->move_out_date)->format('Y-m-d');
        
        $resident = \App\Resident::find($id);

        $resident->firstname = $request->first_name;
        $resident->lastname = $request->last_name;
        $resident->mobile = $request->mobile;
        $resident->email = $request->email;
        //$resident->unit_id = $request->unit_id;
        // $resident->move_in_date = $dateMoveIn;
        // $resident->move_out_date = $dateMoveOut;
       // $resident->user_id = $this->user->id;
        $resident->subscriber_id = $this->user->subscriber_id;
        
        
        $status = $resident->save();
        
        $class = ($status) ? 'success' : 'error';
        $message = ($status) ? 'Resident updated successfully.' : 'Resident updation failed.';

        $data = 
            [
                'title' => 'Resident',
                'text' => $message,
                'class' => $class,
            ];

        return redirect('property-manager/resident')->with('status', $data);
    }

    public function getResidentTemplate(Request $request)
    {
        $getSubject = \App\TemplateContent::select('subject','content','name')
                    ->where('id', $request->id)
                    ->where('is_user', 2)
                    ->get();
            
            foreach($getSubject as $subjectvalue){
                $subjectvalue;
            }
            
        return response()
                    ->json(
                        [
                            'name' => $subjectvalue->name,
                            'subject' => $subjectvalue->subject,
                            'content' => $subjectvalue->content,
                        ]
                        );
        
    }

    public function residentSendMail(Request $request)
    {
        $data = [
            'subject' => $request->subject, 
            'body' => $request->body,
            'logo' => getLogo()['logo'],
            'companyName' => getLogo()['companyName'],
        ];
        //$ccEmails = $request->cc;
        $cc = [];
        if (!empty($request->cc)) {
            $cc = explode(',', $request->cc);
        }
        $toEmail = explode(',', $request->email);

        // Ability to see history of outgoing reminders/waste communications start 
        $emailHistory = new ResidentEmailHistory();

        $emailHistory->name = $request->name;
        $emailHistory->property_manager_id = $this->user->id;
        $emailHistory->resident_id = $request->resident_id;
        $emailHistory->subject = $request->subject;
        $emailHistory->cc = $request->cc;
        $emailHistory->body = $request->body;
        $emailHistory->property_id = $request->property_id;
        $emailHistory->unit_id = $request->unit_id;
        
        $emailHistory->save();
        // Ability to see history of outgoing reminders/waste communications end
        
        try {
            Mail::to($toEmail)->cc($cc)->send(new ResidentSendEmail($data));
        }catch (\Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
        return response()->json(['result' => true]);
    }

    public function residentExport($type) 
    {
        $residentData = \App\Resident::select('firstname','lastname','email','subscriber_id','user_id','unit_id')->get()->toArray();
        return \Excel::create('Residents', function($excel) use ($residentData) 
            {
                $excel->sheet('Residents Details', function($sheet) use ($residentData)
                {
                    $sheet->fromArray($residentData);
                });
            })
            ->download($type);
    }

    public function residentsImport(Request $request)
    {
        if($request->hasFile('residents'))
        {
            $path = $request->file('residents')->getRealPath();
            $data = \Excel::load($path)->get();
                if($data->count())
                {
                    foreach ($data as $key => $value) 
                    {
                        $resident_list[] = 
                        [
                            'Firstname' => $value->firstname, 
                            'Lastname' => $value->lastname,
                            'Mobile' => $value->mobile, 
                            'Email' => $value->email,
                            'subscriber_id' => $this->user->subscriber_id,
                            'user_id' => $this->user->id,
                            //'unit_id' => $value->unit_id,    
                            //'move_in_date' => $value->moveindate
                        ];
                    }  
                    foreach($resident_list as $resid)
                    {
                        $residentemail = \App\Resident::where('email',$resid['Email'])
                                        ->where('subscriber_id',$this->user->subscriber_id)
                                        ->count();

                        if ($residentemail > 0)
                        {
                            continue;
                            return redirect('property-manager/resident');
                        }
                        else
                        {
                            $resident = new Resident();
                    
                            $resident->firstname = $resid['Firstname'];
                            $resident->lastname = $resid['Lastname'];
                            $resident->mobile = $resid['Mobile'];
                            $resident->email = $resid['Email'];
                            $resident->subscriber_id = $resid['subscriber_id'];
                            $resident->user_id = $resid['user_id'];
                           // $resident->move_in_date = $resid['move_in_date'];
                            
                            $status = $resident->save();
                        }
                    }
                        $class = ($status) ? 'success' : 'error';
                        $message = ($status) ? 'File improted successfully.' : 'File improted failed.';
                        
                        $data = 
                        [
                            'title' => 'Resident',
                            'text' => $message,
                            'class' => $class,
                        ];

                    return redirect('property-manager/resident')->with('status', $data);
                    
                }
        }
    }

    public function getDownload()
    {
        $file = public_path()."/downloads/resident.xlsx";
        $headers = array('Content-Type: application/vnd.ms-excel',);
        return Response::download($file, 'resident.xlsx',$headers);
    }

    public function emailHistoryIndex(Request $request)
    {
        $resident_name = \App\Resident::select(\DB::raw("CONCAT_WS(' ', `firstname`, `lastname`) AS full_name"))
                                ->where('id', $request->id)
                                ->first();
        
        $this->data['resName'] = $resident_name;

        $this->data['emailHistory'] = $request->id;
    
        return view('residents.email_history', $this->data);
    }

    public function showEmailHistory(Request $request)
    {
        if (!$this->user->hasRole(['property_manager'])) {
            return redirect('unauthorized');
        }
        $unitName = '-';
        // dd($request->email_id);
        $i = $request->start + 1;
        $emhistoryArray = [];
        $search = $request->search['value'];

       
        //Get total result:Start
        $history =  \App\ResidentEmailHistory::where('resident_id', $request->emailId)
                ->when(
                !empty($search),
                function ($query) use ($search) {
                    $query->where(
                        function ($query) use ($search) {
                            $query->where('subject', 'like', "%$search%")
                                ->orWhere('cc', 'like', "%$search%")
                                ->orWhere('body', 'like', "%$search%");
                        }
                    );
                }
            )
        ->get();

        //Get total result:End
        //Get result with limit:Start (Todo: merge the both queries)
        $emailHistoryList = \App\ResidentEmailHistory::select('id','name','subject','cc','body','unit_id')
                ->where('resident_id', $request->emailId)
                ->when(
                !empty($search),
                function ($query) use ($search) {
                    $query->where(
                        function ($query) use ($search) {
                            $query->where('subject', 'like', "%$search%")
                                ->orWhere('cc', 'like', "%$search%")
                                ->orWhere('body', 'like', "%$search%");
                        }
                    );
                }
            )
            ->with(
                [
                    'getUnitNumber' => function ($query) {
                        $query->select('id','unit_number')
                            ->where('is_active', 1)
                            ->where('is_route', 0);
                        }
                ]
            )
            ->latest()
            ->limit($request->length)->offset($request->start)
            ->get();
        //Get result with limit:End
        if (count($emailHistoryList) > 0) {
            foreach ($emailHistoryList as $emhistory) {
                //Prepare the action link :Start
                if (isset($emhistory->getUnitNumber) && !empty($emhistory->getUnitNumber->unit_number))
                {
                    $unitName = $emhistory->getUnitNumber->unit_number;
                }  
                $emhistoryArray[] = [
                    'id' => $i++,
                    'name' => $emhistory->name,
                    'subject' => $emhistory->subject,
                    'cc' => $emhistory->cc,
                    'body' => $emhistory->body,
                    'unit_id' => $unitName,
                ];
            }
        }

        return json_encode(
            [
                'draw' => intval($request->draw),
                'recordsTotal' => !empty($history) ? $history->count() : 0,
                'recordsFiltered' => !empty($history) ? $history->count() : 0,
                'data' => $emhistoryArray,
            ]
        );
    }

    public function getResidentLogs(Request $request)
    {
        $unitName = '-';
        $i = $request->start + 1;
        $residentLogsArray = [];
        $search = $request->search['value'];

        $residentLogs =  \App\ResidentsUnit::query()
            ->where('residents_id', $request->res_id)
            ->where('violation_id', 0)
            ->when(
                !empty($search),
                function ($query) use ($search) {
                    $query->where(
                        function ($query) use ($search) {
                            $query->where('unit_id', 'like', "%$search%");
                        }
                    );
                }
            )
        ->withTrashed()
        ->get();

        //Get total result:End
        //Get result with limit:Start (Todo: merge the both queries)
        $reslogsList = \App\ResidentsUnit::select('id', 'residents_id', 'unit_id', 'move_in_date', 'move_out_date', 'deleted_at')
            ->where('residents_id', $request->res_id)
            ->where('violation_id', 0)
            ->when(
                !empty($search),
                function ($query) use ($search) {
                    $query->where(
                        function ($query) use ($search) {
                            $query->where('unit_id', 'like', "%$search%");
                        }
                    );
                }
            )
            ->with(
                [
                    'getUnitNumber' => function ($query) {
                        $query->select('id', 'unit_number', 'property_id')
                        ->with(
                            [
                                'getPropertyDetail' => function ($query) {
                                    $query->select('id', 'name');
                                }
                            ]
                        );
                    },
                    'getResident' => function ($query) {
                        $query->select('id', 'unit_id');
                    }
                ]
            )
            ->latest()
            ->limit($request->length)->offset($request->start)
            ->withTrashed()
            ->get();

        if (count($reslogsList) > 0) {
            foreach ($reslogsList as $reslogs) {
                $action = "";
           
                if (isset($reslogs->getUnitNumber) && !empty($reslogs->getUnitNumber->unit_number))
                {
                    $unitName = $reslogs->getUnitNumber->unit_number;
                }

                if (is_null($reslogs->deleted_at))
                {
                    $action = "<a href='javascript:void(0)' class='move_out_date' data-resi='" . $reslogs->getResident->id . "' data-id='" . $reslogs->getResident->unit_id . "'>Move out</a>";
                }

                if (isset($reslogs->move_in_date) && $reslogs->move_in_date === '0000-00-00')
                {
                        $moveIn = '-';
                } else {
                    $moveIn = \Carbon\Carbon::parse($reslogs->move_in_date)
                      ->timezone(getUserTimezone())->format('m-d-Y h:i A');
                }
                
                if (isset($reslogs->move_out_date) && $reslogs->move_out_date === '0000-00-00') 
                {
                    $moveOut = '-';
                } else {
                    $moveOut = \Carbon\Carbon::parse($reslogs->move_out_date)
                        ->timezone(getUserTimezone())->format('m-d-Y h:i A');
                }
                
                $residentLogsArray[] = [
                    'id' => $i++,
                    'property' => $reslogs->getUnitNumber->getPropertyDetail->name,
                    'unit_id' => $unitName,
                    'move_in_date' => $moveIn,
                    'move_out_date' => $moveOut,
                    'action' => $action,
                ];
            }
        }
            
        return json_encode(
            [
                'draw' => intval($request->draw),
                'recordsTotal' => !empty($residentLogs) ? $residentLogs->count() : 0,
                'recordsFiltered' => !empty($residentLogs) ? $residentLogs->count() : 0,
                'data' => $residentLogsArray,
            ]
        );
    }

    public function changeMoveOutDate(Request $request)
    {
        $status = false;
        $changeResidentUnit = \App\Resident::where('id', $request->resi_id)->first();
        $changeResidentUnit->unit_id = 0;
        $changeResidentUnit->save();

        $updateMoveOutDate = \App\ResidentsUnit::where('unit_id', $request->unit_id)
            ->where('residents_id', $request->resi_id)
            ->first();

        if (!is_null($updateMoveOutDate)) {
            $updateMoveOutDate->move_out_date = \Carbon\Carbon::now()->format('Y-m-d');
            $status = $updateMoveOutDate->save();
            $updateMoveOutDate->delete();
        }

        $class = ($status) ? 'success' : 'error';
        $message = ($status) ? 'Resident move out successfully.' : 'Resident move out successfully.';
        
        return response()
            ->json(
                [
                'status' => $status,
                'message' => $message,
                ]
            );
    }
    
    public function updateUnitEdit(Request $request)
    {
        $resident = \App\Resident::where('id', $request->pk)->first();
        $resident->unit_id = $request->value;
        $resident->save();

        $residentU = \App\ResidentsUnit::query()
            ->where('residents_id', $request->pk)
            ->where('violation_id', '0')
            ->first();
            
        if (!is_null($residentU)) {
            $residentU->move_out_date = \Carbon\Carbon::now()->format('Y-m-d');
            $residentU->save();
            $residentU->delete();
        }
        
        \App\ResidentsUnit::query()
            ->where('residents_id', $request->pk)
            ->where('violation_id', '!=', '0')
            ->delete();
        
        \App\ResidentsUnit::create(
            [
                'residents_id' => $request->pk,
                'unit_id'      => $request->value,
                'violation_id' => 0,
                'move_in_date' => \Carbon\Carbon::now()->format('Y-m-d')
            ]
        );
            
        return response()
            ->json(
                [
                    'success' => true,
                ]
            );
    }
}
