<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class BaseController extends Controller
{
    public $data;
    public $user;
    public $permissions;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(
            function ($request, $next) {
                $this->data['user'] = $this->user = \Auth::user();

                $this->data['u_image'] = \App\User::where('id', $this->user->id)
                    ->first();

                // $subscriberDetails = \App\Subscriber::when(
                //     $this->user->is_admin == 1,
                //     function ($query) {
                //         $query->where('user_id', $this->user->id);
                //     }
                // )
                // ->when(
                //     $this->user->is_admin != 1,
                //     function ($query) {
                //         $query->where('id', $this->user->subscriber_id);
                //     }
                // )->first();

                $subscriberDetails = \App\Subscriber::where('id', $this->user->subscriber_id)->first();

                $subscribtion = \App\Subscription::find($subscriberDetails->subscription_id);

                $this->data['subscriber_details'] = $this->subscriber = $subscriberDetails;

                $this->data['subscribtion_details'] = $subscribtion;

                $this->subscriber_id = $this->user->subscriber_id;

                //Get start and end date according to user timezone: Start
                $this->usertime = getStartEndTime();
                //Get start and end date according to user timezone: End
                return $next($request);
            }
        );
    }

    public function setUserTimeZone($data = '')
    {
        $tz = \Auth::user()->timezone;

        if (isset($tz) && !empty($tz)) {
            $timezone = $tz;
        } else {
            $timezone = 'America/New_York';
        }

        if (isset($data) && !empty($data)) {
            $dataTimeZone = $data->setTimezone($timezone)
                    ->format('m-d-Y h:i A');
        } else {
            $dataTimeZone = '';
        }

        return $dataTimeZone;
    }

    public function setUserTimeZoneForApi($data = '')
    {
        $tz = \Auth::user()->timezone;

        if (isset($tz) && !empty($tz)) {
            $timezone = $tz;
        } else {
            $timezone = 'America/New_York';
        }

        if (isset($data) && !empty($data)) {
            $dataTimeZone = $data->setTimezone($timezone);
        } else {
            $dataTimeZone = '';
        }

        return $dataTimeZone;
    }

    public function propertyList()
    {
        $properties = \App\Property::when(
            !$this->user->hasRole(['admin', 'property_manager']),
            function ($query) {
                $query->where(
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
                );
            }
        )
        ->when(
            $this->user->hasRole('admin'),
            function ($query) {
                $query->where('subscriber_id', $this->user->subscriber_id);
            }
        )
        // ->when(
        //     $this->user->hasRole('admin'),
        //     function ($query) {
        //         $query->whereIn(
        //             'id',
        //             function ($query) {
        //                 $query->select('id')
        //                 ->from('properties')
        //                 ->where('subscriber_id', $this->user->subscriber_id)
        //                 ->whereNull('deleted_at');
        //             }
        //         );
        //     }
        // )
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
                                ->where('user_id', $this->user->id)
                                ->whereNull('deleted_at');
                            }
                        );
                    }
                );
            }
        );

        return $properties;
    }

    public function createExcel(...$value)
    {
        $array = $value[0];
        $sheetname = $value[1];

        $date = \Carbon\Carbon::today()->toDateString();
        // Generate and return the spreadsheet
        Excel::create(
            $sheetname . $date,
            function ($excel) use ($array) {
                // Set the spreadsheet title, creator, and description
                $excel->setTitle('Payments');
                $excel->setCreator('Laravel')->setCompany('WJ Gilmore, LLC');
                $excel->setDescription('payments file');

                // Build the spreadsheet, passing in the payments array
                $excel->sheet(
                    'sheet1',
                    function ($sheet) use ($array) {
                        $sheet->getColumnDimension('A')->setVisible(false);
                        $sheet->getColumnDimension('B')->setVisible(false);
                        $sheet->getColumnDimension('C')->setVisible(false);
                        $sheet->fromArray($array, null, 'A1', false, false);
                    }
                );
            }
        )
        ->download('xlsx');
    }

    public function managerAccess()
    {
        $permissionName = [];
        $user = $this->user;
        $permission = $user->canAccess;

        if ($permission->isNotEmpty()) {
            $permissionName = $permission[0]->permssion_name;
        } else {
            $permissionName = [];
        }
    }

    public function generatePassword()
    {
        $seed = str_split(
            'abcdefghijklmnopqrstuvwxyz'
            . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
            . '0123456789'
        );

        shuffle($seed);
        $rand = '';

        foreach (array_rand($seed, 6) as $k) {
            $rand .= $seed[$k];
        }

        return $rand;
    }

    public function validateMobile(Request $request)
    {
        $mobile = $request->mobile;
        $id = $request->id;

        $userExists = \App\User::where('mobile', $mobile)
            ->where('id', '!=', $id)
            ->first();

        if (!empty($id)) {
            $user = \App\User::find($id);

            if (!empty($user->id) && $mobile == $user->mobile) {
                return '';
            } elseif (empty($userExists->id)) {
                return '';
            } else {
                abort(404, 'Mobile exists.');
            }
        } elseif (!empty($userExists->id)) {
            abort(404, 'Mobile exists.');
        }
    }

    public function validateEmail(Request $request)
    {
        $email = $request->email;
        $id = $request->id;

        $userExists = \App\User::where('email', $email)
            ->where('id', '!=', $id)->first();

        if (!empty($userExists)) {
            abort(404, 'Email exists.');
        } else {
            return '';
        }
    }

    public function getViolationDetail($request)
    {
        $ids = is_array($request->id) ? $request->id
            : explode(',', $request->id);

        $violations = \App\Violation::whereIn('id', $ids)->latest()->get();
        $voilationDetails = [];

        if (isset($violations) && $violations->isNotEmpty()) {
            foreach ($violations as $violation) {
                //Remove rollback violation:Start
                if (!isNotRollbackViolation($violation->id)) {
                    continue;
                }
                //Remove rollback violation:End
                $status = '';

                if ($violation->status == 0) {
                    $status = 'New';
                } elseif ($violation->status == 2) {
                    $status = 'Submitted';
                } elseif ($violation->status == 3) {
                    $status = 'Discarded';
                } elseif ($violation->status == 4) {
                    $status = 'Pending';
                } elseif ($violation->status == 5) {
                    $status = 'Closed';
                } elseif ($violation->status == 6) {
                    $status = 'Archived';
                }

                $user = \App\User::withTrashed()
                        ->find($violation->user_id);
                $unit = \App\Units::where('barcode_id', $violation->barcode_id)
                        ->withTrashed()->first();
                $property = \App\Property::withTrashed()
                        ->find($unit->property_id);
                $building = \App\Building::where('id', $unit->building_id)
                        ->withTrashed()->first();
                $reason = \App\Reason::withTrashed()
                        ->find($violation->violation_reason);
                $action = \App\Action::withTrashed()
                        ->find($violation->violation_action);

                $voilationDetails[] = [
                    'status' => $status,
                    'user_name' => ucwords($user->firstname . ' ' . $user->lastname),
                    'property_name' => ucwords($property->name),
                    'address' => ucwords($property->address),
                    'reason' => $reason->reason,
                    'action' => $action->action,
                    'images' => $violation->images,
                    'type' => $property->type,
                    'unit' => $unit->unit_number,
                    'unit_address' => ucwords($unit->address1),
                    'building_address' => !empty($building->address) ? ucwords($building->address) : 'Not Mention',
                    'building' => !empty($building->building_name) ? ucwords($building->building_name) : 'Not Mention',
                    'created_at' => $violation->created_at,
                    'special_note' => $violation->special_note,
                ];
            }
        }

        return $voilationDetails;
    }

    protected function getViolationEmailContent()
    {
        $template = \App\TemplateContent::select(
            'id',
            'status',
            'content',
            'name',
            'subject'
        )
        ->where(
            [
                'subscriber_id' => $this->user->subscriber_id,
                'template_id' => \APP\TemplateContent::TEMPLATEID,
                'is_user' => \APP\TemplateContent::ISUSERSUBSCRIBER
            ]
        );

        if ($template->get()->isNotempty()) {
            return [
                'allTemplate' => $template->get(),
                'defaultTemplate' => $template->where('status', 1)->get(),
            ];
        } else {
            return [
                'allTemplate' => collect(),
                'defaultTemplate' => collect(),
            ];
        }
    }

    protected function checkNoteReasonPermission($id)
    {
        $subjectNote = \App\NoteSubject::where(
            [
                'user_id' => $this->user->subscriber_id,
                'id' => $id,
            ]
        )->first();

        return !$subjectNote ? true : false;
    }

    protected function checkExceptionPermission($id)
    {
        $exception = \App\IssueReason::where(
            [
                'id' => $id,
                'user_id' => $this->user->subscriber_id,
            ]
        )->first();

        return !$exception ? true : false;
    }

    protected function checkPropertyPermission($id)
    {
        $property = $this->propertyList()->get();

        return !in_array($id, $property->pluck('id')->toArray()) ? true : false;
    }

    protected function checkRolePermission($id)
    {
        $roles = \App\Role::where('id', $id)
            ->whereIn(
                'user_id',
                function ($query) {
                    $query->select('id')
                        ->from('users')
                        ->where(
                            [
                                'subscriber_id' => $this->user->subscriber_id,
                            ]
                        );
                }
            )->first();

        return !$roles ? true : false;
    }

    protected function checkCustomerPermission($id)
    {
        $customer = \App\CustomerSubscriber::query()
            ->where(
                [
                    'customer_id' => $id,
                    'subscriber_id' => $this->user->subscriber_id,
                ]
            )
        ->first();
        
        return is_null($customer) ? true : false;
    }

    protected function checkManagerPermission($id)
    {
        $manage = \App\User::where(
            [
                'id' => $id,
                'subscriber_id' => $this->user->subscriber_id,
            ]
        )
        ->where('role_id', \config('constants.propertyManager'))
        ->first();

        return !$manage ? true : false;
    }

    protected function checkEmployeePermission($id)
    {
        $employee = \App\User::whereNotIn('role_id', [\config('constants.propertyManager')])
            ->where('is_admin', '!=', \config('constants.adminRoleId'))
            ->where('id', $id)
            ->where('subscriber_id', $this->user->subscriber_id)
            ->first();

        return !$employee ? true : false;
    }

    protected function checkBarcodeListPermission($id)
    {
        $barcodeList = $this->propertyList()
            ->where('id', $id)->first();

        return !$barcodeList ? true : false;
    }
}
