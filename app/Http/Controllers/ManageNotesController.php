<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ManageNotesController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        //Because note section in manage report panel
        //that's why report permission apply for this controller.
        //$this->middleware('RoleAndPermission:report');
    }

    public function index()
    {
        $employee = \App\User::select('id', 'title', 'firstname', 'lastname')
            ->when( #1281: Username filter in Property Manager Portal - Notes Report
                $this->user->hasRole('property_manager'),
                function ($query) {
                    $query->whereIn('id', function ($query) {
                        $query->select('user_id')
                            ->from('user_properties')
                            ->whereIn('property_id', $this->propertyList()->pluck('id'))
                            ->where('type', 1)
                            ->whereNull('deleted_at');
                    });
                },
                function ($query) {
                    $query->where('subscriber_id', $this->user->subscriber_id);
                }
            )
            ->orderBy('title')
            ->whereNotIn('role_id', [10])
            ->get();

        $reason = \App\NoteSubject::select('id', 'subject')
            ->orWhere(
                [
                    'user_id' => $this->subscriber_id,
                    'type' => 0,
                ]
            )
        ->orderBy('subject')
        ->withTrashed()
        ->get();

        $this->data['reasons'] = $reason;

        $this->data['empolyee'] = $employee;

        return view('note.notelist', $this->data);
    }

    public function getNoteList(Request $request)
    {
        $i = $request->start + 1;
        $search = $request->search['value'];
        $noteArray = $barcodeId = [];
        $state = $request->status;
        $usename = $request->username;
        $reasonSubject = $request->reasonSubject;
        $notesType = $request->notesType;
        $startTime = \Carbon\Carbon::parse($request->startTime, getUserTimezone())
            ->addHours(6)->copy();
        $endTime = \Carbon\Carbon::parse($request->endTime, getUserTimezone())
            ->addDays(1)->addHours(5)->addMinutes(59)->addSeconds(59)->copy();
        $barcodeId = collect();
        $name = $subject = $description = $image = '-';
        $created_at = $unit = $action = $status = '-';
        // Mantain two diffrent status first one for admin and
        //second one for property manager (Task: 640 comment: #17)): Start
        $col = $this->user->hasRole('property_manager')
            ? 'manager_status' : 'status';
        $statusVal = $this->user->hasRole('property_manager') ? [6, 11] : [6];
        // Mantain two diffrent status first one for admin and
        //second one for property manager (Task: 640 comment: #17)): End

        //Only for properties search: Start
        // if (!empty($search) || $this->user->hasRole('property_manager')) {
        //     $barcode = $this->propertyList()
        //         ->select('id')
        //         ->when(
        //             !empty($search),
        //             function ($query) use ($search) {
        //                 $query->where('name', 'like', "%$search%");
        //             }
        //         )
        //         ->with(
        //             [
        //                 'getUnit' => function ($query) {
        //                     $query->select('property_id', 'barcode_id')
        //                     ->where(
        //                         function ($query) {
        //                             $query->where(
        //                                 function ($query) {
        //                                     $query->where('is_route', 0)
        //                                         ->where('is_active', 1);
        //                                 }
        //                             )
        //                             ->orWhere(
        //                                 function ($query) {
        //                                     $query->where('is_route', 1);
        //                                 }
        //                             );
        //                         }
        //                     );
        //                 },
        //             ]
        //         )
        //     ->withTrashed()
        //     ->get();
                        
        //     $barcodeId = $barcode
        //         ->map(
        //             function ($item, $key) {
        //                 if (!empty($item->getUnit[0]->barcode_id)) {
        //                     return $item->getUnit->pluck('barcode_id');
        //                 }
        //             }
        //         )
        //     ->collapse();
        // }
        //Only for properties search: End
        //Get total record count: Start
        $note = \App\BarcodeNotes::query()
            ->whereBetween(
                \DB::raw("convert_tz(created_at, 'UTC','" . getUserTimezone() . "')"),
                [
                    $startTime,
                    $endTime
                ]
            )
            ->when( #1281: Username filter in Property Manager Portal - Notes Report
                $this->user->hasRole('property_manager') && empty($usename),
                function ($query) {
                    $query->whereIn(
                        'user_id',
                        function ($query) {
                            $query->select('user_id')
                                ->from('user_properties')
                                ->whereIn('property_id', $this->propertyList()->pluck('id'))
                                ->where('type', 1)
                                ->whereNull('deleted_at');
                        }
                    );
                }
            )
            ->when( #1281: Username filter in Property Manager Portal - Notes Report
                !$this->user->hasRole('property_manager') && empty($usename),
                function ($query) {
                    $query->where(
                        function ($query) {
                            $query->whereIn(
                                'user_id',
                                function ($query) {
                                    $query->from('users')
                                        ->select('id')
                                        ->where(
                                            [
                                                'subscriber_id' => $this->user->subscriber_id,
                                            ]
                                        );
                                }
                            );
                        }
                    );
                }
            )
            ->when(
                !empty($reasonSubject),
                function ($query) use ($reasonSubject) {
                    $query->where('reason', $reasonSubject);
                }
            )
            ->when(
                !empty($notesType),
                function ($query) use ($notesType) {
                    $query->where('notes_type', $notesType);
                }
            )
            // ->when(
            //     $barcodeId->isNotEmpty(),
            //     function ($query) use ($barcodeId) {
            //         $query->whereIn('barcode_id', $barcodeId);
            //     }
            // )
            // ->when(
            //     ($this->user->hasRole('property_manager') || !empty($search))
            //         && $barcodeId->isEmpty(),
            //     function ($query) use ($barcodeId) {
            //         //Todo: we will find proper solution
            //         //(property manager has no property when this condition excute).
            //         $query->whereIn('barcode_id', [1000]);
            //     }
            // )
            ->when(
                $state > -1, //Status contain zero value for "NEW" that's why
                //we have campared with -1.To Do: We will find any proper solution.
                function ($query) use ($state, $col) {
                    $query->where($col, $state);
                },
                function ($query) use ($statusVal, $col) {
                    $query->whereNotIn($col, $statusVal);
                }
            )
            ->withTrashed()
        ->get();
        //Get total record count: End
        //Get record according to limit: Start

        $not = \App\BarcodeNotes::query()
            ->whereBetween(
                \DB::raw("convert_tz(created_at, 'UTC','" . getUserTimezone() . "')"),
                [
                    $startTime,
                    $endTime
                ]
            )
            ->when(
                !empty($usename),
                function ($query) use ($usename) {
                    $query->where('user_id', $usename);
                }
            )
            ->when( #1281: Username filter in Property Manager Portal - Notes Report
                $this->user->hasRole('property_manager') && empty($usename),
                function ($query) {
                    $query->whereIn(
                        'user_id',
                        function ($query) {
                            $query->select('user_id')
                                ->from('user_properties')
                                ->whereIn('property_id', $this->propertyList()->pluck('id'))
                                ->where('type', 1)
                                ->whereNull('deleted_at');
                        }
                    );
                }
            )
            ->when( #1281: Username filter in Property Manager Portal - Notes Report
                !$this->user->hasRole('property_manager') && empty($usename),
                function ($query) {
                    $query->where(
                        function ($query) {
                            $query->whereIn(
                                'user_id',
                                function ($query) {
                                    $query->from('users')
                                        ->select('id')
                                        ->where(
                                            [
                                                'subscriber_id' => $this->user->subscriber_id,
                                            ]
                                        );
                                }
                            );
                        }
                    );
                }
            )
            ->when(
                !empty($reasonSubject),
                function ($query) use ($reasonSubject) {
                    $query->where('reason', $reasonSubject);
                }
            )
            ->when(
                !empty($notesType),
                function ($query) use ($notesType) {
                    $query->where('notes_type', $notesType);
                }
            )
            // ->when(
            //   $barcodeId->isNotEmpty(),
            //     function ($query) use ($barcodeId) {
            //         $query->whereIn('barcode_id', $barcodeId);
            //     }
            // )
            // ->when(
            //     ($this->user->hasRole('property_manager') || !empty($search))
            //         && $barcodeId->isEmpty(),
            //     function ($query) use ($barcodeId) {
            //         //Todo: we will find proper solution
            //         //(property manager has no property when this condition excute).
            //         $query->whereIn('barcode_id', [1000]);
            //     }
            // )
            ->when(
                $state > -1, //Status contain zero value for "NEW" that's why
                //we have campared with -1.To Do: We will find any proper solution.
                function ($query) use ($state, $col) {
                    $query->where($col, $state);
                },
                function ($query) use ($statusVal, $col) {
                    $query->whereNotIn($col, $statusVal);
                }
            )
            ->with(
                [
                    'getUser' => function ($query) {
                        $query->withTrashed();
                    },
                    'getNoteSubject' => function ($query) {
                        $query->select('id', 'subject')
                            ->withTrashed();
                    },
                ]
            )
            ->withTrashed()
            ->latest()
            ->limit($request->length)->offset($request->start)
        ->get();
        //Get record according to limit: End

        foreach ($not as $notes) {
            // Mantain two diffrent status first one for admin
            //and second one for property manager (Task: 640 comment: #17)): Start
            $noteStatus = $this->user->hasRole('property_manager')
                ? $notes->manager_status : $notes->status;
            // Mantain two diffrent status first one for admin
            // and second one for property manager (Task: 640 comment: #17)): End

            //Identify the notes type:Start
            if ($noteStatus == 6) {
                $type = 'Archived';
            } elseif ($noteStatus == 5) {
                $type = 'Closed';
            } elseif ($noteStatus == 2) {
                $type = 'Submitted';
            } elseif ($noteStatus == 0) {
                $type = 'New';
            } elseif ($noteStatus == 7) {
                $type = 'Read';
            } elseif ($noteStatus == 8) {
                $type = 'In Process';
            } elseif ($noteStatus == 9) {
                $type = 'On Hold';
            } elseif ($noteStatus == 10) {
                $type = 'Sent Notice';
            }
            //Identify the notes type:End

            //Prepare the action link according to user role:Start
            $action = "<li data-id='$notes->id' data-status='1' title='View' class='model_link fa fa-eye' style='cursor: pointer;'></li>";
            //Prepare the action link according to user role:End

            $editUrl = url('/note/change-status');
            $status = "<a href='#' class='change-status' data-type='select' data-pk='" . $notes->id . "' data-url='" . $editUrl . "' data-title='Select Status'>$type</a>";

            if (isset($notes->getUser->firstname)) {
                $name = ucwords($notes->getUser->title . ' ' . $notes->getUser->firstname . ' ' . $notes->getUser->lastname);
            }

            if (isset($notes->getNoteSubject->subject)) {
                $subject = ucwords($notes->getNoteSubject->subject);
            }

            if (isset($notes->description)) {
                $description = ucwords($notes->description);
            }

            //Making Image Tag : Start
            if (!empty($notes->image_name) && Storage::disk('s3')->exists('uploads/note/' . $notes->image_name)) {
                //$url = Storage::disk('s3')->url('uploads/note/' . $notes->image_name);
                $url = url('/uploads/note/' . $notes->image_name);
                $class = 'img-rounded';
            } else {
                $class = 'get-image img-rounded';
                $url = url('/uploads/note/no-image-available.png');
            }

            $image = "<a href='javascript:void(0);'><img src='" . $url . " 'width='50px' height='50px' class='" . $class . "'/></a>";
            //Making Image Tag: End

            $created_at = \Carbon\Carbon::parse($notes->created_at)
                ->timezone(getUserTimezone())
                ->format('m-d-Y h:i A');

            $updated_at = \Carbon\Carbon::parse($notes->updated_at)
                ->timezone(getUserTimezone())
                ->format('m-d-Y h:i A');

            if (!empty($notes->notes_type == 1)) {
                $notesType = 'Unit Specific Notes';
            } elseif ($notes->notes_type == 2) {
                $notesType = 'General Note';
            } elseif ($notes->notes_type == 3) {
                $notesType = 'Checkout Notes';
            } elseif (is_null($notes->notes_type)) {
                if (!empty($notes->barcode_id)) {
                    $notesType = 'Unit Specific Notes';
                } else {
                    $notesType = 'General Note';
                }
            }

            $detail = '<b>Created At:</b> ' . $created_at;
            $detail .= '<br/><b>Updated At:</b> ' . $updated_at;
            $detail .= '<br/><b>Description:</b> ' . $description;

            $noteArray[] = [
                'plusIcon' => '',
                'id' => $i++,
                'name' => $name,
                'subject' => $subject,
                'description' => $description,
                'image' => $image,
                'unit' => $notesType,
                'type' => $status,
                'action' => $action,
                'detail' => $detail,
            ];
        }

        return json_encode(
            [
                'draw' => intval($request->draw),
                'recordsTotal' => !empty($note) ? $note->count() : 0,
                'recordsFiltered' => !empty($note) ? $note->count() : 0,
                'data' => $noteArray,
            ]
        );
    }

    public function updateNotesStatus(Request $request)
    {
        // Mantain two diffrent status first one for admin and second one for property manager (Task: 640 comment: #17)): Start
        $col = $this->user->hasRole('property_manager') ? 'manager_status' : 'status';
        // Mantain two diffrent status first one for admin and second one for property manager (Task: 640 comment: #17)): End

        if ($this->user->hasRole('property_manager')) {
            $data = ['manager_status' => $request->value];
        } else {
            $data['status'] = $request->value;

            if ($request->value == 2) {
                $data['manager_status'] = 0; //"NEW" status for property manager.
            } elseif ($request->value == 0) {
                $data['manager_status'] = 11; //Hide for the property manager.
            }
        }

        $query = \App\BarcodeNotes::where('id', $request->pk)
            ->update($data);

        return response()
            ->json(
                [
                    'status' => $query ? true : false,
                ],
                200
            );
    }

    public function getNote(Request $request)
    {
        $enquiry = \App\BarcodeNotes::where('id', $request->id)
            ->with(
                [
                    'getReason' => function ($query) {
                        $query->select('id', 'reason', 'type')
                            ->withTrashed();
                    },
                    'getUser' => function ($query) {
                        $query->select('id', 'title', 'firstname', 'lastname')
                            ->withTrashed();
                    },
                    'activity'  => function ($query) {
                        $query->select('id', 'text', 'barcode_id', 'property_id')
                            ->with(
                                [
                                    'getPropertyDetailByPropertyId' => function ($query) {
                                        $query->select('id', 'name', 'address')
                                            ->withTrashed();
                                    }
                                ]
                            )
                        ->withTrashed();
                    },
                ]
            )
        ->withTrashed()
        ->first();
        
        if (isset($enquiry->activity->getPropertyDetailByPropertyId)) {
            //$propertyDetail = $enquiry->activity->getPropertyDetailByPropertyId;
            $propertyDetail = \App\Property::query()
                ->where(
                    'id',
                    function ($query) use ($enquiry) {
                        $query->select('property_id')
                            ->from('activity_log')
                            ->where('id', $enquiry->activityLogId);
                    }
                )
                ->withTrashed()
            ->first();
        }
        
        if (isset($enquiry->barcode_id) && !empty($enquiry->barcode_id)) {
            $barcodeId = $enquiry->barcode_id;
            $propertyDetail = \App\Property::query()
                ->where(
                    'id',
                    function ($query) use ($barcodeId) {
                        $query->select('property_id')
                            ->from('units')
                            ->where('barcode_id', $barcodeId);
                    }
                )
                ->withTrashed()
            ->first();
        }
        
        if (!empty($enquiry->image_name) && Storage::disk('s3')->exists('uploads/note/' . $enquiry->image_name)) {
            //$url = Storage::disk('s3')->url('uploads/note/' . $enquiry->image_name);
            $url = url('/uploads/note/' . $enquiry->image_name);
        } else {
            $url = url('/uploads/note/no-image-available.png');
        }

        if (isset($enquiry->getUser->firstname) && !empty($enquiry->getUser->firstname)) {
            $noteDetail['employee'] = ucwords($enquiry->getUser->firstname . ' ' . $enquiry->getUser->lastname);
        }
        
        if (isset($propertyDetail) && !empty($propertyDetail->name)) {
            $noteDetail['propertyName'] = ucwords($propertyDetail->name);
        }
        
        if (isset($propertyDetail) && !empty($propertyDetail->address)) {
            $noteDetail['propertyAddress'] = ucwords($propertyDetail->address);
        }

        if (isset($enquiry->getNoteSubject->subject) && !empty($enquiry->getNoteSubject->subject)) {
            $noteDetail['subject'] = ucwords($enquiry->getNoteSubject->subject);
        }

        $noteDetail['description'] = ucwords($enquiry->description);
        $noteDetail['lat'] = $enquiry->lat;
        $noteDetail['long'] = $enquiry->long;
        $noteDetail['date'] = $this->setUserTimeZone($enquiry->created_at);
        $noteDetail['url'] = $url;
        
        //Get Unit Detail:Start
        if (!empty($enquiry->barcode_id)) {
            //$type = $propertyDetail->type;

            if (!empty($enquiry->getUnitNumber->unit_number)) {
                $noteDetail['unitNumber'] = ucwords($enquiry->getUnitNumber->unit_number);
            }

            $noteDetail['address1'] = ucwords($enquiry->getUnitNumber->address1);
            $noteDetail['building'] = ucwords($enquiry->getUnitNumber->building);
            $noteDetail['buildingAddress'] = ucwords($enquiry->getUnitNumber->getBuildingDetail->address);
            
            // if (isset($type) && ($type == 1 || $type == 4)) {
            //     $noteDetail['address1'] = ucwords($enquiry->getUnitNumber->address1);
            // } elseif (isset($type) && ($type == 2 || $type == 3)) {
            //     $noteDetail['building'] = ucwords($enquiry->getUnitNumber->building);
            //     $noteDetail['buildingAddress'] = ucwords($enquiry
            //     ->getUnitNumber->getBuildingDetail->address);
            // }
        }
        //Get Unit Detail:End

        $this->data['noteDetail'] = $noteDetail;

        return view('note.notedetails', $this->data);
    }
}
