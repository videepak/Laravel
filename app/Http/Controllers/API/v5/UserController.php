<?php

namespace App\Http\Controllers\API\v5;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use App\User;
use App\Property;
use App\Units;
use App\violation;
use App\AppSetting;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\LoginRequest;  /* Use it to generate API parameters in Doc. */
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\DB;
use Hashids;
use Twilio\Rest\Client;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use App\Notifications\EmailTemplate;
use App\Activitylogs;

/**
 * @resource UserController: V5
 *
 * This resource handler user authentication.
 */
class UserController extends Controller {

    use AuthenticatesUsers;
    use SendsPasswordResetEmails;

    public static $validation_api_rules = [
        'login' => [
            'email' => 'required|string|email',
            'password' => 'required|string',
            'platform' => 'required|string',
            'device_token' => 'required|string',
            'appVersion' => 'required|string'
        ],
        'forgotPassword' => [
            'email' => 'required|email',
        ],
        'updateProfile' => [
            'first_name' => 'nullable',
            'last_name' => 'nullable',
            'mobile' => 'nullable',
            'image' => 'nullable',
            'image_type' => "required_with:image",
            'gender' => 'nullable',
            'colourBlindMode' => 'nullable'
        ],
        'getPropertyDetail' => ['property_id' => 'required|integer'],
        'scanQrcode' => [
            'barcode_id' => 'required|string',
        ],
        'scanQrcodeV2' => [
            'barcode_id' => 'required|string',
        ],
        'reportViolation' => [
            'long' => 'required',
            'lat' => 'required',
            'barcode_id' => 'required|string',
        ],
        'changePassword' => [
            'old_password' => 'required|string',
            'new_password' => 'required|string',
        ],
        'createViolation' => [
            'reasonid' => 'required|integer',
            'requiredactionid' => 'required|integer',
            'image' => 'nullable',
            'barcode_id' => 'required|string',
            'image_type' => "required_with:image"
        ],
        'createViolationV1' => [
            'reasonid' => 'required|integer',
            'requiredactionid' => 'required|integer',
            'special_note' => 'nullable',
            'barcode_id' => 'required|string'
        ],
        'walkThrough' => [
            'long' => 'required|string',
            'lat' => 'required|string',
            'buliding' => 'nullable',
            'propertyId' => 'required|integer|exists:properties,id,deleted_at,NULL',
            'buliding_id' => 'nullable',
        ],
        'activateCode' => [
            'address1' => 'required',
            'address2' => 'required',
            'barcode_id' => 'required|exists:units,barcode_id',
            'unit' => 'nullable|required_with:barcode_id',
            'long' => 'required|string',
            'lat' => 'required|string',
            'floor' => 'nullable',
            'building' => 'nullable',
            'building_id' => 'nullable',
            'type' => 'nullable|string',
        ],
        'activateCodeV2' => [
            'address1' => 'required',
            'address2' => 'required',
            'barcode_id' => 'required|exists:units,barcode_id',
            'unit' => 'nullable|required_with:barcode_id',
            'long' => 'required|string',
            'lat' => 'required|string',
            'floor' => 'nullable',
            'building' => 'nullable',
            'building_id' => 'nullable',
            'unitAddress' => 'nullable',
            'building_address' => 'nullable',
            'type' => 'nullable|string',
        ],
        'getActivityLog' => [
            'record_per_page' => 'required|integer',
            'page' => 'required|integer',
        ],
        'pickUp' => [
            'barcode_id' => 'required|string',
            'lat' => 'required',
            'long' => 'required',
        ],
        'pickUp_V2' => [
            'barcode_id' => 'required|string',
            'lat' => 'required',
            'long' => 'required',
        ],
        'manuallyPickUp' => [
            'property_id' => 'required|integer',
            'unit_number' => 'required',
            'buliding' => 'nullable',
            'lat' => 'required',
            'long' => 'required',
        ],
        'scanRevockBarcode' => [
            'activity_id' => 'required|string|exists:activity_log,id,deleted_at,NULL',
            'lat' => 'required',
            'long' => 'required',
        ],
        'note' => [
            'barcode_id' => 'nullable',
            'address1' => 'required',
            'address2' => 'required',
            'unit' => 'nullable',
            'long' => 'required|string',
            'lat' => 'required|string',
            'reason' => 'required|string',
            'activityLogId' => 'string',
            'description' => 'required|string',
            'image' => 'nullable',
            'image_type' => "required_with:image",
        ],
        'getEmployeschedule' => [
            'which_date' => 'nullable',
        ],
        'getEmployescheduleV2' => [
            'which_date' => 'nullable',
        ],
        'getEmployescheduleV3' => [
            'which_date' => 'nullable',
        ],
        'addNoteSchedule' => [
            'which_date' => 'nullable',
        ],
        'addNoteScheduleV2' => [
            'which_date' => 'nullable',
        ],
        'addNoteScheduleV3' => [
            'which_date' => 'nullable',
        ],
        'workPlanFillterApi' => [
            'which_date' => 'nullable',
            'search_text' => 'required',
        ],
        'workPlanFillterApiV2' => [
            'which_date' => 'nullable',
            'search_text' => 'required',
        ],
        'reportIssue' => [
            'title' => 'required|string',
            'description' => 'required|string',
            'issue_date' => 'required|date_format:"Y-m-d H:i:s"',
            'reason' => 'required|integer',
            'property_id' => 'required|integer|exists:properties,id,deleted_at,NULL',
            'building_id' => 'nullable|integer'
        ],
        'propertyCheckIn' => [
            'id' => 'required|integer',
            'lat' => 'required',
            'long' => 'required'
        ]
    ];

    public function __construct() {

        //die('v5/UserController');

        if (request()->segment(count(request()->segments())) != "login" || request()->segment(count(request()->segments())) != "forgotPassword") {
            $authToken = request()->header('authorization');
            //$appVersion = $this->getVersionByToken($authToken);
            $appVersion = request()->header('appVersion');
            $this->userId = $this->getUserIdByToken($authToken);
            $this->subscriberId = $this->getSubscriberIdByToken($authToken);
            $this->userDetail = $this->getUserDetailIdByToken($authToken);
            $this->timezone = $this->getTimezoneByToken($authToken);
            $this->platform = strtoupper($this->getPlatformByToken($authToken));
            $this->appVersion = 14; 

            if (!empty($this->getTimezoneByToken($authToken))) {
                $this->timezone = $this->getTimezoneByToken($authToken);
            } else {
                $this->timezone = "America/New_York";
            }

            $startDateTimezone = $this->getStartDate();
            $this->start = $startDateTimezone->format('Y-m-d') . " 06:00:00";
            $this->end = $startDateTimezone->addDay(1)->format('Y-m-d') . " 05:59:59";
        }
    }

    public function index() {
        return response()->json(['message' => "success", 'data' => ['responseCode' => 201, 'responseMsg' => 'Token Expired.'], 'status' => 405], 200);
    }

    /**
     * Login.
     *
     * @response {
     *      "message": "User successfully logged in",
     *      "data": {
     *              "authToken": "9c100d4e2ea88dd2f67456cf420090a3",
     *              "responseCode": 200,
     *              "responseMsg": "Login successfully"
     *          },
     *      "status": 200
     *  }
     *
     */
    public function login(Request $request) {

        $validator = Validator::make($request->all(), [
                    $this->username() => 'required|string|email',
                    'password' => 'required|string',
                    'platform' => 'required|string',
                    'device_token' => 'required|string',
                    'appVersion' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->errorFormat($validator->errors());
        }

        $device_token = $request->device_token;
        $platform = $request->platform;
        $app_version = $request->appVersion;
        $api_token = str_shuffle(bin2hex(openssl_random_pseudo_bytes(16)));

        if ($this->attemptLogin($request)) {
            $user = $this->guard()->user();

            //if ((isset($user->id) && !empty($user->id)) && $this->checkAppVersion($request->appVersion, $platform)) {

            $user->device_token = $device_token;
            $user->platform = $platform;
            $user->api_token = $api_token;
            $user->app_version = $app_version;
            //$user->timezone = $this->timezone;
            $user->save();
            $user = array(
                'authToken' => $api_token,
                'responseCode' => 200,
                'responseMsg' => 'Login successfully'
            );

            return response()->json([
                        'message' => 'User successfully logged in',
                        'data' => $user,
                        'status' => 200
            ]);
            //}
            // else {
            //     $user = array(
            //         'responseCode' => 202,
            //         'responseMsg' => 'Update required.'
            //     );
            //     return response()->json([
            //                 'message' => 'Update required.',
            //                 'data' => $user,
            //                 'status' => 200
            //     ]);
            // }
        } else {
            $user = array(
                'responseCode' => 201,
                'responseMsg' => 'Invalid email or Password.'
            );

            return response()->json([
                        'message' => 'Invalid email or Password.',
                        'data' => $user,
                        'status' => 200
            ]);
        }
    }

    /**
     * Forgot Password.
     *
     * @response {
     *          "message": "success",
     *          "data": {
     *                "responseCode": 200,
     *                "responseMsg": "Email sent successfully."
     *               },
     *          "status": 200
      }
     *
     */
    public function forgotPassword(Request $request) {

        $validator = Validator::make($request->all(), ['email' => 'required|email']);

        if ($validator->fails()) {
            return $this->errorFormat($validator->errors());
        }

        $response = $this->broker()->sendResetLink(
                $request->only('email')
        );

        return $response == Password::RESET_LINK_SENT ? response()->json(['message' => 'success', 'data' => ['responseCode' => 200, 'responseMsg' => 'Email sent successfully.'], 'status' => 200]) : response()->json(['message' => 'success', 'data' => ['responseCode' => 201, 'responseMsg' => 'Invalid Email.'], 'status' => 200], 200);
    }

    /**
     * User Profile.
     *
     * @response {
     *      "message": "success",
     *      "data": {
     *              "title": "Mr.",
     *              "first_name": "Employee 1",
     *              "last_name": "Employee 1",
     *              "email": "employee1@galaxyinfotech.co",
     *              "mobile": "1234567966",
     *              "gender": "",
     *              "image_name": "http://trashcan.galaxyweblinks.com/uploads/user/user_2.jpg",
     *              "colourBlindMode": "1",
     *              "role": "Paul Smith",
     *              "responseCode": 200,
     *              "responseMsg": "success"
     *              },
     *      "status": 200
     * }
     *
     */
    public function userProfile(Request $request) {

        $user = User::find($this->userId);

        if (isset($user->id) && !empty($user->id)) {
            $data = [
                'title' => $user->title,
                'first_name' => $user->firstname,
                'last_name' => $user->lastname,
                'email' => $user->email,
                'mobile' => $user->mobile,
                'gender' => $user->gender,
                'image_name' => $user->image_name != "" ? url('uploads/user/' . $user->image_name . '') : "",
                'colourBlindMode' => $user->colourBlindMode,
                'role' => $user->roles->first()->display_name,
                'responseCode' => 200,
                'responseMsg' => 'success'
            ];
            $response = $this->setData($data);
            return response()->json(['message' => 'success', 'data' => $response, 'status' => 200], 200);
        } else {
            $data = [
                'responseCode' => 201,
                'responseMsg' => 'User Detail Not Found.'
            ];
            return response()->json(['message' => 'success', 'data' => $data, 'status' => 200], 200);
        }
    }

    /**
     * Update User Profile.
     *
     * @response {
     *       "message": "success",
     *       "data": {
     *          "responseCode": 200,
     *          "responseMsg": "Profile updated successfully."
     *       },
     *       "status": 200
     * }
     *
     */
    public function updateProfile(Request $request) {

        $validator = Validator::make($request->all(), [
                    'first_name' => 'nullable',
                    'last_name' => 'nullable',
                    'mobile' => 'nullable',
                    'image' => 'nullable',
                    'image_type' => "required_with:image",
                    'gender' => 'nullable',
                    'colourBlindMode' => 'nullable'
        ]);

        if ($validator->fails()) {
            return $this->errorFormat($validator->errors());
        }

        $user = User::find($this->userId);


        if ($user) {
            if (!empty($request->first_name)) {
                $user->firstname = $request->first_name;
            }
            if (!empty($request->lastname)) {
                $user->lastname = $request->last_name;
            }
            if (!empty($request->mobile)) {
                $user->mobile = $request->mobile;
            }
            if (!empty($request->gender)) {
                $user->gender = $request->gender;
            }

            if (!empty($request->colourBlindMode)) {
                $user->colourBlindMode = $request->colourBlindMode;
            }
            if (!empty($request->gender)) {
                $user->gender = $request->gender;
            }
            $user->save();

            if (!empty($request->image)) {
                $this->base64ToImage($request->image, $this->userId, $request->image_type, 'user');
            }
            $data = [
                'responseCode' => 200,
                'responseMsg' => 'Profile updated successfully.',
            ];
            return response()->json(['message' => 'success', 'data' => $data, 'status' => 200], 200);
        } else {
            $data = [
                'responseCode' => 201,
                'responseMsg' => 'Profile not updated successfully.',
            ];
            return response()->json(['message' => 'success', 'data' => $data, 'status' => 201], 200);
        }
    }

    /**
     * Change Password.
     *
     * @response {
     *          "message": "success",
     *          "data": {
     *                  "responseCode": 200,
     *                  "responseMsg": "Password updated successfully."
     *              },
     *          "status": 200
     * }
     */
    public function changePassword(Request $request) {

        $validator = Validator::make($request->all(), [
                    'old_password' => 'required|string',
                    'new_password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorFormat($validator->errors());
        }
//dsa
        $userDetail = User::find($this->userId);

        $verify_password = \Hash::check($request->old_password, $userDetail->password);

        if ($verify_password) {
            $userDetail->password = \Hash::make($request->new_password);
            $userDetail->save();

            $data = [
                'responseCode' => 200,
                'responseMsg' => 'Password updated successfully.'
            ];

            return response()->json(['message' => 'success', 'data' => $data, 'status' => 200], 200);
        } else {
            $data = [
                'responseCode' => 201,
                'responseMsg' => 'Old password does not match.'
            ];

            return response()->json(['message' => 'success', 'data' => $data, 'status' => 200], 200);
        }
    }

    /**
     * Property Detail
     *
     * @response {
     * "message": "success",
     * "data": [
     *    {
     *        "id": 4346,
     *        "address1": "Sita Ram Park Badaganphti",
     *        "address2": "Indore",
     *        "unit_number": "W33",
     *        "activation_date": "2018-11-20 09:17:09",
     *        "property_id": 37,
     *        "building_id": 314,
     *        "latitude": "0.0000000",
     *        "longitude": "0.0000000",
     *        "building": "Testing",
     *        "floor": "1",
     *        "barcode_id": "DJLQVXRG5O",
     *        "unit_name": "LQVXRG",
     *        "last_scan_date": "",
     *        "created_at": "2018-11-13 12:19:42",
     *        "updated_at": "2018-11-20 09:17:09",
     *        "is_active": 1,
     *        "type": 0,
     *        "deleted_at": "",
     *        "units": "W33",
     *        "responseCode": 200,
     *        "responseMsg": "success"
     *     }
     * ],
     * "status": 200
     * }
     */
    public function getPropertyDetail(Request $request) {

        $propertyDetail = array();

        $validator = Validator::make($request->all(), ['property_id' => 'required|integer']);

        if ($validator->fails()) {
            return $this->errorFormat($validator->errors());
        }

// Get Property Units
        $propertyDetail = $this->getPropertyUnit($request->property_id);

        if (filter_var($propertyDetail[0]["unit_number"], FILTER_VALIDATE_INT)) {
            $propertyDetail[0]['units'] = $propertyDetail[0]["unit_number"] ? ++$propertyDetail[0]["unit_number"] : 100;
        } else {
            $propertyDetail[0]['units'] = $propertyDetail[0]["unit_number"];
        }

        if (isset($propertyDetail[0]['address1']) && !empty($propertyDetail[0]['address1'])) {
            $propertyDetail[0]['responseCode'] = 200;
            $propertyDetail[0]['responseMsg'] = "success";
            $response = $this->setData($propertyDetail);

            return response()->json(['message' => 'success', 'data' => $response, 'status' => 200], 200);
        } else {
            $data = [
                'responseCode' => 201,
                'responseMsg' => 'Property Detail Not Found.'
            ];

            return response()->json(['message' => 'success', 'data' => $data, 'status' => 200], 200);
        }
    }

    /**
     * Property Check-In.
     *
     * v5: Implemented send notification functionality.
     *
     * @response {
     *          "message": "success",
     *          "data": {
     *                  "responseCode": 200,
     *                  "responseMsg": "Checked-in successfully."
     *              },
     *          "status": 200
     * }
     */
    public function propertyCheckIn(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'id' => 'required|integer',
                'lat' => 'required',
                'long' => 'required'
            ]   
        );

        if ($validator->fails()) {
            return $this->errorFormat($validator->errors());
        }

        if (!$this->checkLocationForWalkThrough($request->id, $request->lat, $request->long)) {
            $data = [
                'responseCode' => (int) 201,
                'responseMsg' => 'You are not within the valid '
                . 'radius of this property address.'
                ];
            $response = $this->setData($data);
            
            return response()->json(
                [
                    'message' => 'success', 
                    'data' => $response, 
                    'status' => 200
                    ], 
                200
            );
        }

        $property = \App\PropertiesCheckIn::firstOrCreate(
            [
                'updated_at' => function ($query) use ($request) {
                    $query->select('updated_at')
                        ->from('properties_check_in')
                        ->where('property_id', $request->id)
                        ->where('user_id', $this->userId)
                        ->whereNull('deleted_at')
                        ->whereBetween(
                            DB::raw("convert_tz(updated_at,'UTC','" . $this->timezone . "')"),
                            [
                                $this->start, $this->end
                            ]
                        );
                },
                    'property_id' => $request->id,
                    'user_id' => $this->userId
                        ], [
                    'property_id' => $request->id,
                    'user_id' => $this->userId,
                    'check_in' => 1
            ]
        );

        //Create activity log: Start
        Activitylogs::create(
            [
                'text' => 'Property Checked-in',
                'user_id' => $this->userId,
                'updated_by' => $this->userId,
                'property_id' => $request->id,
                'type' => 7,
                'latitude' => $request->lat,
                'longitude' => $request->long,
                'ip_address' => $request->ip()
            ]
        );
        //Create activity log: End

        $this->NotiicationForCheckin($request->id);

        $data = [
            'responseCode' => '200',
            'responseMsg' => 'Checked-in successfully.'
        ];

        return response()
                ->json(
                    [
                        'message' => 'success',
                        'data' => $data,
                        'status' => 200
                    ],
                    200
                );
    }

    /**
     * Property List V2.
     *
     * @response {
     * "message": "success",
     * "data": {
     *    "propertyList": [
     *        {
     *            "id": 108,
     *            "name": "Garden Two",
     *            "address": "New Palasia",
     *            "city": "Indore",
     *            "state": "ALABAMA",
     *            "zip": "452001",
     *            "type": 2,
     *            "latitude": "22.724355",
     *            "longitude": "75.8838944",
     *            "checkInFlag": 0,
     *            "routeCompleteFlag": 0
     *         }
     *     ],
     *      "responseCode": 200,
     *      "responseMsg": "success"
     *   },
     *   "status": 200
     * }
     */
    public function propertiesListV2() {

        //dd($daytocheck);
        $arr = [];
        $startDate = \Carbon\Carbon::now()->timezone($this->timezone)->format('Y-m-d H:i:s');

        $properties = Property::whereIn('id', function ($query) {
                    $query->from('user_properties')
                    ->select('property_id')
                    ->where('user_id', $this->userId)
                    ->where('status', 1)
                    ->where("deleted_at", null)
                    ->whereIn('property_id', function ($query) {
                        $query->from("units")
                        ->select('property_id')
                        ->whereIn("property_id", function ($query) {
                            $query->from('property_frequencies')
                            ->select("property_id")
                            ->where("day", $this->getCurrentDay())
                            ->where("deleted_at", null)
                            ->groupBy('property_id');
                        })
                        ->where("deleted_at", null)
                        ->groupBy('property_id')
                        ->where('is_active', 1);
                    });
                })
                ->whereHas('service', function ($query) use ($startDate) {
                    $query->where('pickup_start', '<=', $startDate)
                    ->where('pickup_finish', '>=', $startDate);
                })
                ->get();


        foreach ($properties as $property) {
            $checkPropertyIn = $property->checkInProperty()
                            //->whereBetween('updated_at', [$this->start, $this->end])
                            ->whereBetween(DB::raw("convert_tz(updated_at,'UTC','" . $this->timezone . "')"), [$this->start, $this->end])
                            ->where('check_in', 1)->where('user_id', $this->userId);

            $arr[] = [
                'id' => $property->id,
                'name' => $property->name,
                'address' => $property->address,
                'city' => $property->city,
                'state' => $property->getState->name,
                'zip' => $property->zip,
                'type' => $property->type,
                'latitude' => $property->latitude,
                'longitude' => $property->longitude,
                'checkInFlag' => $checkPropertyIn->count(),
                'routeCompleteFlag' => 0,
            ];
        }

        if ($properties->isNotEmpty()) {
            $arrMain['propertyList'] = $arr;
            $arrMain['responseCode'] = 200;
            $arrMain['responseMsg'] = "success";
            $response = $this->setData($arrMain);

            return response()->json(['message' => 'success', 'data' => $response, 'status' => 200], 200);
        } else {
            $data = [
                'responseCode' => 201,
                'responseMsg' => 'Property Not Found.'
            ];

            return response()->json(['message' => 'success', 'data' => $data, 'status' => 200], 200);
        }
    }

    /**
     * Property List.
     *
     * @response {
     * "message": "success",
     * "data": [
     *    {
     *        "id": 1,
     *        "name": "Property 1",
     *        "address": "START TOWER",
     *        "city": "Start City",
     *        "state": "ALABAMA",
     *        "zip": "452002",
     *        "type": 1,
     *        "latitude": "22.7130291",
     *        "longitude": "75.8388698",
     *        "checkInFlag": 1,
     *        "routeCompleteFlag": 0
     *    },
     *    {
     *        "id": 108,
     *        "name": "Garden Two",
     *        "address": "New Palasia",
     *        "city": "Indore",
     *        "state": "ALABAMA",
     *        "zip": "452001",
     *        "type": 2,
     *        "latitude": "22.724355",
     *        "longitude": "75.8838944",
     *        "checkInFlag": 0,
     *        "routeCompleteFlag": 0
     *   }
     * ],
     * "status": 200
     * }
     */
    public function propertiesList() {

        $arr = [];
        $startDate = \Carbon\Carbon::now()->format('Y-m-d');

        $properties = Property::whereIn('id', function ($query) {
                    $query->from('user_properties')
                    ->select('property_id')
                    ->where('user_id', $this->userId)
                    ->where('status', 1)
                    ->where("deleted_at", null)
                    ->whereIn('property_id', function ($query) {
                        $query->from("units")
                        ->select('property_id')
                        ->whereIn("property_id", function ($query) {
                            $query->from('property_frequencies')
                            ->select("property_id")
                            ->where("day", $this->getCurrentDay())
                            ->where("deleted_at", null)
                            ->groupBy('property_id');
                        })
                        ->where("deleted_at", null)
                        ->groupBy('property_id')
                        ->where('is_active', 1);
                    });
                })
                ->whereHas('service', function ($query) use ($startDate) {
                    $query->whereDate('pickup_start', '<=', $startDate)
                    ->whereDate('pickup_finish', '>=', $startDate);
                })
                ->get();


        foreach ($properties as $property) {
            $checkPropertyIn = $property->checkInProperty()->whereBetween('updated_at', [$this->start, $this->end])
                            ->where('check_in', 1)->where('user_id', $this->userId);

            $arr[] = [
                'id' => $property->id,
                'name' => $property->name,
                'address' => $property->address,
                'city' => $property->city,
                'state' => $property->getState->name,
                'zip' => $property->zip,
                'type' => $property->type,
                'latitude' => $property->latitude,
                'longitude' => $property->longitude,
                'checkInFlag' => $checkPropertyIn->count(),
                'routeCompleteFlag' => 0,
            ];
        }

        if ($properties->isNotEmpty()) {
            $propertyDetail[0]['responseCode'] = 200;
            $propertyDetail[0]['responseMsg'] = "success";
            $response = $this->setData($arr);

            return response()->json(['message' => 'success', 'data' => $response, 'status' => 200], 200);
        } else {
            $data = [
                'responseCode' => 201,
                'responseMsg' => 'Property Not Found.'
            ];

            return response()->json(['message' => 'success', 'data' => $data, 'status' => 200], 200);
        }
    }

    /**
     * Scan Qrcode
     *
     * @response {
     *    "message": "success",
     *    "data": {
     *          "property_name": "Bansi Trade Centre",
     *          "address1": "New Palasia",
     *          "address2": "Indore",
     *          "hasMultipleUnit": 1,
     *          "unit": 116,
     *          "responseCode": 200,
     *          "responseMsg": "Not Active"
     *      },
     *    "status": 200
     * }
     */
    public function scanQrcode(Request $request) {

        $validator = Validator::make($request->all(), [
                    'barcode_id' => 'required|exists:units,barcode_id',
        ]);

        if ($validator->fails()) {
            return $this->errorFormat($validator->errors());
        }

        $barcode_id = $request->barcode_id;

        if ($this->checkValidEmployee($request->barcode_id)) {
            $activeOrNot = $this->propertyActiveOrNot($barcode_id);

            if ($activeOrNot === 'Already Active.') {
                $propertyDetail = $this->propertyDetailByQrbarId($barcode_id);
                $mainProperty = $this->getPropertyType($propertyDetail[0]->property_id);

                $data = [
                    'property_name' => $mainProperty['name'],
                    'address1' => $propertyDetail[0]["address1"],
                    'address2' => $propertyDetail[0]["address2"],
                    'unit' => $propertyDetail[0]["unit_number"],
                    'hasMultipleUnit' => $mainProperty["units"] > 1 ? 1 : 0,
                    'responseCode' => 201,
                    'responseMsg' => 'Already Active.'
                ];
                $response = $this->setData($data);
                return response()->json(['message' => 'success', 'data' => $response, 'status' => 200], 200);
            } elseif ($activeOrNot === 'Qrcode Not Found.') {
                $data = [
                    'responseCode' => 203,
                    'responseMsg' => 'QR code Not Found.'
                ];
                $response = $this->setData($data);
                return response()->json(['message' => 'success', 'data' => $response, 'status' => 200], 200);
            } else {
                $propertyDetail = $this->propertyDetailByQrbarId($barcode_id);
                $mainProperty = $this->getPropertyType($propertyDetail[0]->property_id);
                $propertyDetailUnit = $this->checkPropertyUnitEmpty($propertyDetail[0]->property_id, $barcode_id);

                $data = [
                    'property_name' => $mainProperty['name'],
                    'address1' => $mainProperty['address'],
                    'address2' => $mainProperty['city'],
                    'hasMultipleUnit' => $mainProperty["units"] > 1 ? 1 : 0,
                    'unit' => $propertyDetailUnit,
                    'responseCode' => 200,
                    'responseMsg' => 'Not Active'
                ];

                $response = $this->setData($data);
                return response()->json(['message' => 'success', 'data' => $response, 'status' => 200], 200);
            }
        } else {
            $data = ['responseCode' => 201, 'responseMsg' => 'Invalid QR code.'];
            $response = $this->setData($data);
            return response()->json(['message' => 'success', 'data' => $response, 'status' => 200], 200);
        }
    }

    protected function getBuldingByPropertyId($barcodeId) {

        $building = \App\Building::select('id', 'building_name', 'property_id', 'address')->where('property_id', function ($query) use ($barcodeId) {
                    $query->select('property_id')
                            ->from('units')
                            ->where('barcode_id', $barcodeId)
                            ->whereIn('property_id', function ($query) {
                                $query->select('id')
                                ->from('properties')
                                ->whereNull('deleted_at');
//  ->whereIn('type', [3, 4]);
                            })
                            ->whereNull('deleted_at');
                })->get();

        return $building;
    }

    /**
     * Scan Qrcode V2
     *
     * @response {
     * "message": "success",
     * "data": {
     *    "property_name": "Bansi Trade Centre",
     *    "address1": "New Palasia",
     *    "address2": "Indore, ALABAMA, 452001",
     *    "type": 1,
     *    "buildingName": [
     *         {
     *             "id": 456,
     *             "building_name": null,
     *             "property_id": 5,
     *             "address": ""
     *        }
     *     ],
     *    "unitAddress": "",
     *    "hasMultipleUnit": 1,
     *    "unit": "116",
     *    "responseCode": 200,
     *    "responseMsg": "Not Active"
     * },
     * "status": 200
     * }
     */
    public function scanQrcodeV2(Request $request) {

        $validator = Validator::make($request->all(), [
                    'barcode_id' => 'required|exists:units,barcode_id',
                        ], [
                    'barcode_id.exists' => "Barcode is not valid.",
        ]);

        if ($validator->fails()) {
            return $this->errorFormat($validator->errors());
        }

        $barcode_id = $request->barcode_id;

        if ($this->checkValidEmployee($request->barcode_id)) {
            $activeOrNot = $this->propertyActiveOrNot($barcode_id);
            $buildingName = $this->getBuldingByPropertyId($barcode_id);

            if ($activeOrNot === 'Already Active.') {
                $propertyDetail = $this->propertyDetailByQrbarId($barcode_id);
                $mainProperty = $this->getPropertyType($propertyDetail[0]->property_id);

                $data = [
                    'property_name' => $mainProperty['name'],
                    'address1' => $mainProperty['address'],
                    'address2' => $mainProperty['city'] . ', ' . $mainProperty['get_state']['name'] . ', ' . $mainProperty['zip'],
                    'type' => $mainProperty['type'],
                    'unit' => (string) $propertyDetail[0]["unit_number"],
                    'buildingName' => $buildingName,
                    'unitAddress' => $propertyDetail[0]["address1"],
                    'hasMultipleUnit' => $mainProperty["units"] > 1 ? 1 : 0,
                    'responseCode' => 201,
                    'responseMsg' => 'Already Active.'
                ];
                $response = $this->setData($data);
                return response()->json(['message' => 'success', 'data' => $response, 'status' => 200], 200);
            } elseif ($activeOrNot === 'Qrcode Not Found.') {
                $data = [
                    'responseCode' => 203,
                    'responseMsg' => 'QR code Not Found.'
                ];
                $response = $this->setData($data);
                return response()->json(['message' => 'success', 'data' => $response, 'status' => 200], 200);
            } else {
                $propertyDetail = $this->propertyDetailByQrbarId($barcode_id);
                $mainProperty = $this->getPropertyType($propertyDetail[0]->property_id);
                $propertyDetailUnit = $this->checkPropertyUnitEmpty($propertyDetail[0]->property_id, $barcode_id);

                $data = [
                    'property_name' => $mainProperty['name'],
                    'address1' => $mainProperty['address'],
                    'address2' => $mainProperty['city'] . ', ' . $mainProperty['get_state']['name'] . ', ' . $mainProperty['zip'],
                    'type' => $mainProperty['type'],
                    'buildingName' => $buildingName,
                    'unitAddress' => $propertyDetail[0]["address1"],
                    'hasMultipleUnit' => $mainProperty["units"] > 1 ? 1 : 0,
                    'unit' => (string) $propertyDetailUnit,
                    'responseCode' => 200,
                    'responseMsg' => 'Not Active'
                ];

                $response = $this->setData($data);
                return response()->json(['message' => 'success', 'data' => $response, 'status' => 200], 200);
            }
        } else {
            $data = ['responseCode' => 201, 'responseMsg' => 'Invalid QR code.'];
            $response = $this->setData($data);
            return response()->json(['message' => 'success', 'data' => $response, 'status' => 200], 200);
        }
    }

    /**
     * Report Violation
     *
     * @response {
     * "message": "success",
     * "data": {
     *    "requiredaction": [
     *       {
     *            "value": "Email Community",
     *            "id": 1
     *       },
     *       {
     *            "value": "Left Notice",
     *            "id": 2
     *       },
     *       {
     *            "value": "Charge",
     *            "id": 3
     *       },
     *       {
     *            "value": "Do Not Charge",
     *            "id": 4
     *       },
     *       {
     *            "value": "Contained/Resolved",
     *            "id": 5
     *       },
     *       {
     *             "value": "av",
     *            "id": 6
     *       },
     *       {
     *             "value": "action1",
     *             "id": 8
     *       }
     *     ],
     *     "reason": [
     *         {
     *            "value": "Trash not in/on LIXO trash bin",
     *            "id": 1
     *         },
     *         {
     *             "value": "Loose trash",
     *            "id": 2
     *         },
     *         {
     *              "value": "Trash bag(s) not tied",
     *              "id": 3
     *         },
     *         {
     *              "value": "Over trash bag limit",
     *              "id": 4
     *         },
     *         {
     *              "value": " Over trash bag size limit ",
     *              "id": 5
     *         },
     *         {
     *              "value": "Over trash bag weight limit ",
     *              "id": 6
     *         },
     *         {
     *              "value": "Recycling not in/on bin",
     *              "id": 7
     *         },
     *         {
     *               "value": "Loose recycling",
     *              "id": 8
     *         },
     *         {
     *              "value": "Recycling bag(s) not tied",
     *              "id": 9
     *         },
     *         {
     *             "value": "Over recycling bag limit",
     *             "id": 10
     *         },
     *         {
     *             "value": "Recycling out on non-recycling day",
     *             "id": 11
     *         },
     *         {
     *            "value": "Box(es) not broken down/flattened",
     *            "id": 12
     *         },
     *         {
     *            "value": "Over recycling bag weight limit ",
     *             "id": 13
     *         },
     *         {
     *             "value": "Over recycling bag weight limit",
     *             "id": 14
     *         },
     *         {
     *             "value": "Over cardboard limit",
     *             "id": 15
     *         },
     *         {
     *            "value": "Testing 100",
     *            "id": 22
     *         },
     *         {
     *            "value": "Trash and Debris Outside of Trash",
     *            "id": 23
     *         },
     *         {
     *            "value": "over filled",
     *            "id": 28
     *         },
     *         {
     *             "value": "Over cardboard limitds",
     *            "id": 30
     *         }
     *    ],
     *     "propertyDetail": [
     *        {
     *              "id": 2,
     *              "address1": "Demo",
     *              "address2": "demo",
     *              "unit_number": "101",
     *              "activation_date": "2018-11-13 06:59:44",
     *              "property_id": 1,
     *              "building_id": "",
     *              "latitude": "23.2599000",
     *              "longitude": "77.4126000",
     *              "building": "Property 1",
     *              "floor": "",
     *              "barcode_id": "4BDLDRG7Z6",
     *              "unit_name": "DLDRG7",
     *              "last_scan_date": "",
     *              "created_at": "2018-09-10 14:45:19",
     *              "updated_at": "2019-01-31 11:51:10",
     *              "is_active": 1,
     *              "type": 0,
     *              "deleted_at": ""
     *          }
     *       ],
     *      "responseCode": 200,
     *      "responseMsg": "Violation registered successfully."
     *   },
     *   "status": 200
     * }
     */
    public function reportViolation(Request $request) {

        $validator = Validator::make($request->all(), [
                    'long' => 'required',
                    'lat' => 'required',
                    'barcode_id' => ['required', 'string',
                        Rule::exists('units', 'barcode_id')->where(function ($query) {
                                    $query->where('is_active', 1)
                                            ->whereNull('deleted_at');
                                })
                    ]
                        ], [
                    'barcode_id.exists' => "QR code Not active.",
        ]);

        if ($validator->fails()) {
            return $this->errorFormat($validator->errors());
        }

        $barcode_id = $request->barcode_id;

//        if (!$this->isPropertyCheckIn($barcode_id)) {
//            $data = ['responseCode' => 201, 'responseMsg' => 'You must check-in before performing this activity on this property.'];
//            $response = $this->setData($data);
//            return response()->json(['message' => 'success', 'data' => $response, 'status' => 200], 200);
//        }

        $unit = Units::where(['barcode_id' => $request->barcode_id])->get();
        $propertyDetail = $this->setData($unit->toArray());
        $data = [
            'requiredaction' => $this->getAction(),
            'reason' => $this->getReason(),
            'propertyDetail' => $propertyDetail,
            'responseCode' => 200,
            'responseMsg' => 'Violation registered successfully.'
        ];
        $response = $this->setData($data);
        return response()->json(['message' => 'success', 'data' => $response, 'status' => 200], 200);
    }

    /**
     * Pick Up
     *
     * @response {
     * "message": "success",
     * "data": {
     *   "responseCode": 200,
     *   "responseMsg": "Recycle Done"
     * },
     * "status": 200
     * }
     */
    public function pickUp(Request $request) {

        $validator = Validator::make($request->all(), [
                    'barcode_id' => 'required|string|exists:units,barcode_id',
                    'lat' => 'required',
                    'long' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->errorFormat($validator->errors());
        }

        $barcodeId = $request->barcode_id;
        $activeOrNot = $this->propertyActiveOrNot($request->barcode_id);

        if ($activeOrNot) {
            if ($this->checkValidEmployee($request->barcode_id)) {
                $pickUp = Units::select('barcode_id', 'latitude', 'longitude')->where('barcode_id', $request->barcode_id)->get();
                if ($pickUp->count()) {
                    $pickupType = \App\Service::select('pickup_type')
                                    ->where('property_id', function ($query) use ($barcodeId) {
                                        $query->from('units')
                                        ->select('property_id')
                                        ->where("is_active", 1)
                                        ->where("barcode_id", $barcodeId);
                                    })->get();


                    $checkCount = Activitylogs::where("barcode_id", $barcodeId)
                            ->where("type", 2)
                            ->whereNull("deleted_at")
                            ->whereDate("created_at", date("Y-m-d"))
                            ->get();

                    if ($checkCount->count() == 0) {
                        if ($pickupType[0]->pickup_type == 1) {
                            $msg = "Waste Collected";
                            $this->sendnotification($barcodeId);
                            Activitylogs::create([
                                'text' => $msg,
                                'user_id' => $this->userId,
                                'updated_by' => $this->userId,
                                'barcode_id' => $request->barcode_id,
                                'type' => 2,
                                'latitude' => $request->lat,
                                'longitude' => $request->long,
                                'wast' => 1,
                                'ip_address' => $request->ip()
                            ]);
                        } elseif ($pickupType[0]->pickup_type == 2) {
                            $msg = "Recycle Done";
                            $this->sendnotification($barcodeId);
                            Activitylogs::create([
                                'text' => $msg,
                                'user_id' => $this->userId,
                                'updated_by' => $this->userId,
                                'barcode_id' => $request->barcode_id,
                                'type' => 2,
                                'latitude' => $request->lat,
                                'longitude' => $request->long,
                                'recycle' => 1,
                                'ip_address' => $request->ip()
                            ]);
                        } elseif ($pickupType[0]->pickup_type == 3) {
                            $msg = "Waste Collected";
                            $this->sendnotification($barcodeId);
                            Activitylogs::create([
                                'text' => $msg,
                                'user_id' => $this->userId,
                                'updated_by' => $this->userId,
                                'barcode_id' => $request->barcode_id,
                                'type' => 2,
                                'latitude' => $request->lat,
                                'longitude' => $request->long,
                                'wast' => 1,
                                'ip_address' => $request->ip()
                            ]);
                        }
                    } elseif ($checkCount->count() == 1 && $pickupType[0]->pickup_type == 3) {
//echo "<pre>";print_r($checkCount->toArray());die();
                        if ($checkCount[0]->recycle != 1) {
                            $msg = "Recycle Done";
                            Activitylogs::where("barcode_id", $request->barcode_id)
                                    ->where("type", 2)
                                    ->where("id", $checkCount[0]->id)
                                    ->update(["recycle" => 1, "text" => $msg]);
                        } else {
                            $msg = "Recycle Already Done";
                        }
                    } else {
//$this->sendnotification($barcodeId, date("Y-m-d"));
                        $msg = "Already Pickup.";
                    }

                    $data = ['responseCode' => 200, 'responseMsg' => $msg];
                    $response = $this->setData($data);
                    return response()->json(['message' => 'success', 'data' => $response, 'status' => 200], 200);
                } else {
                    $data = ['responseCode' => 201, 'responseMsg' => 'QR code Not Found.'];
                    $response = $this->setData($data);
                    return response()->json(['message' => 'success', 'data' => $response, 'status' => 200], 200);
                }
            } else {
                $data = ['responseCode' => 201, 'responseMsg' => 'Invalid QR code.'];
                $response = $this->setData($data);
                return response()->json(['message' => 'success', 'data' => $response, 'status' => 200], 200);
            }
        } else {
            $data = [
                'responseCode' => 201,
                'responseMsg' => 'QR code Not Active.'
            ];
            return response()->json(['message' => 'success.', 'data' => $data, 'status' => 200], 200);
        }
    }

    private function isPickupDay($barcode) {

        $todayDate = \Carbon\Carbon::now()->timezone($this->timezone)->format('Y-m-d H:i:s');

        $service = \App\Service::where('property_id', function ($query) use ($barcode) {
                    $query->select('property_id')
                    ->from('units')
                    ->where('barcode_id', $barcode)
                    ->whereNull('deleted_at');
                })
                ->where('pickup_start', '<=', $todayDate)
                ->where('pickup_finish', '>=', $todayDate)
                ->get();

        $propertyFrequency = \App\PropertyFrequencies::where('property_id', function ($query) use ($barcode) {
                    $query->select('property_id')
                    ->from('units')
                    ->where('barcode_id', $barcode)
                    ->whereNull('deleted_at');
                })
                ->where("day", $this->getCurrentDay())
                ->get();

        //if ($propertyFrequency->isNotEmpty() && $service->pickup_start <= $todayDate && $service->pickup_finish >= $todayDate) {
        if ($propertyFrequency->isNotEmpty() && $service->isNotEmpty()) {
            return true;
        } else {
            return false;
        }
    }

    private function isPropertyCheckIn($barcode) {

        $propertyCheckIn = \App\PropertiesCheckIn::where('property_id', function ($query) use ($barcode) {
                    $query->select('property_id')
                    ->from('units')
                    ->where('barcode_id', $barcode)
                    ->whereNull('deleted_at');
                })
                ->where("user_id", $this->userId)
                ->where("check_in", 1)
                ->whereBetween(DB::raw("convert_tz(updated_at,'UTC','" . $this->timezone . "')"), [$this->start, $this->end])
                ->get();

        if ($propertyCheckIn->isNotEmpty()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Pick Up V2
     *
     * Version v5 : Insert property id and building id in pickup api.
     *
     * @response {
     * "message": "success",
     * "data": {
     *   "responseCode": 200,
     *   "responseMsg": "Recycle Done"
     * },
     * "status": 200
     * }
     */
    public function pickUp_V2(Request $request) {

        $validator = Validator::make(
            $request->all(),
            [
                'barcode_id' => ['required', 'string',
                    Rule::exists('units', 'barcode_id')
                    ->where(
                        function ($query) {
                        $query->where('is_active', 1)
                            ->whereNull('deleted_at');
                        }
                    )
                ],
                'lat' => 'required',
                'long' => 'required',
                'requestid' => 'nullable',
            ],
            [
                'barcode_id.exists' => "QR code Not active.",
            ]
        );

        $requestId = !empty($request->requestid) ? $request->requestid : "";

        if ($validator->fails()) {
            return $this->errorFormat($validator->errors(), $requestId);
        }

        $barcode_id = $request->barcode_id;
        $building = $request->buliding;
        $lat = $request->lat;
        $long = $request->long;
        $propertyDetail = \App\Units::where('barcode_id', $barcode_id)
                ->first();

        if (!$this->isPropertyCheckIn($barcode_id)) {
            $data = [
                'responseCode' => 201, 
                'requestid' => $requestId, 
                'responseMsg' => 'You must check-in before'
                . ' performing this activity on this property.'
            ];
            $response = $this->setData($data);
            return response()->json(
                [
                    'message' => 'success', 
                    'data' => $response, 
                    'status' => 200
                ], 
                200
            );
        }

        if (!$this->isPickupDay($barcode_id)) {
            
            $data = [
                'responseCode' => 201, 
                'requestid' => $requestId,
                'responseMsg' => 'QR Code is not scheduled for today.'
            ];
            $response = $this->setData($data);
            return response()->json(['message' => 'success', 'data' => $response, 'status' => 200], 200);
        }

        if (!$this->checkLocation($barcode_id, $building, $lat, $long)) {
            $data = ['responseCode' => 201, 'requestid' => $requestId, 'responseMsg' => 'Task cannot be completed at your current location. User must be within valid radius of property address.'];
            $response = $this->setData($data);
            return response()->json(['message' => 'success', 'data' => $response, 'status' => 200], 200);
        }

        $pickupType = \App\Service::select('pickup_type')
                        ->where('property_id', function ($query) use ($barcode_id) {
                            $query->from('units')
                            ->select('property_id')
                            ->where("is_active", 1)
                            ->where("barcode_id", $barcode_id);
                        })->get();

        $checkCount = Activitylogs::where("barcode_id", $barcode_id)
                ->where("type", 2)
                ->whereBetween(DB::raw("convert_tz(updated_at,'UTC','" . $this->timezone . "')"), [$this->start, $this->end])
                ->get();

        if ($checkCount->isEmpty()) {
            if ($pickupType[0]->pickup_type == 1) {
                $this->sendnotification($barcode_id);
                $msg = "Waste Collected";
                Activitylogs::create([
                    'text' => $msg,
                    'user_id' => $this->userId,
                    'updated_by' => $this->userId,
                    'barcode_id' => $request->barcode_id,
                    'property_id' => $propertyDetail->property_id,
                    'building_id' => $propertyDetail->building_id,
                    'type' => 2,
                    'latitude' => $request->lat,
                    'longitude' => $request->long,
                    'wast' => 1,
                    'ip_address' => $request->ip()
                ]);
            } elseif ($pickupType[0]->pickup_type == 2) {
                $this->sendnotification($barcode_id);
                $msg = "Recycle Done";
                Activitylogs::create([
                    'text' => $msg,
                    'user_id' => $this->userId,
                    'updated_by' => $this->userId,
                    'barcode_id' => $request->barcode_id,
                    'type' => 2,
                    'property_id' => $propertyDetail->property_id,
                    'building_id' => $propertyDetail->building_id,
                    'latitude' => $request->lat,
                    'longitude' => $request->long,
                    'recycle' => 1,
                    'ip_address' => $request->ip()
                ]);
            } elseif ($pickupType[0]->pickup_type == 3) {
                $this->sendnotification($barcode_id);
                $msg = "Waste Collected";
                Activitylogs::create([
                    'text' => $msg,
                    'user_id' => $this->userId,
                    'updated_by' => $this->userId,
                    'barcode_id' => $request->barcode_id,
                    'type' => 2,
                    'property_id' => $propertyDetail->property_id,
                    'building_id' => $propertyDetail->building_id,
                    'latitude' => $request->lat,
                    'longitude' => $request->long,
                    'wast' => 1,
                    'ip_address' => $request->ip()
                ]);
            }
        } elseif ($checkCount->isNotEmpty() && $pickupType[0]->pickup_type == 3) {
            if ($checkCount[0]->recycle != 1) {
                $msg = "Recycle Done";
                Activitylogs::where("barcode_id", $request->barcode_id)
                        ->where("type", 2)
                        ->where("id", $checkCount[0]->id)
                        ->update(["recycle" => 1, "text" => $msg]);
            } else {
                $msg = "Recycle Already Done";
            }
        } else {
            $msg = "Already Pickup.";
        }

        $data = ['responseCode' => 200, 'requestid' => $requestId, 'responseMsg' => $msg];
        $response = $this->setData($data);
        return response()->json(['message' => 'success', 'data' => $response, 'status' => 200], 200);
    }

    /**
     * Manually Pickup
     *
     * @response {
     * "message": "success",
     * "data": {
     *   "responseCode": 200,
     *   "responseMsg": "Recycle Done"
     * },
     * "status": 200
     * }
     */
    public function manuallyPickup(Request $request) 
    {

        $property_id = $request->property_id;
        $unit_number = $request->unit_number;
        $buliding = $request->buliding;

        $validator = Validator::make(
            $request->all(),
            [
                'property_id' => 'required|integer',
                'unit_number' => ['required',
                    Rule::exists('units', 'unit_number')
                        ->where(
                            function ($query) use ($property_id, $buliding) {
                            $query->where('property_id', $property_id)
                                ->whereNull('deleted_at')
                                ->where('is_active', 1)
                                ->when(
                                    !empty($buliding), 
                                    function ($query) use ($buliding) {
                                    return $query->where('building', $buliding);
                                    }
                                );
                            }
                        )
                    ],
                    'buliding' => 'nullable',
                    'lat' => 'required',
                    'long' => 'required',
            ],
            [
                'unit_number.exists' => "Invalid Unit Number"
            ]
        );

        if ($validator->fails()) {
            return $this->errorFormat($validator->errors());
        }

        $barcode = \App\Units::select('barcode_id')
                ->where(
                    [
                        'property_id' => $property_id,
                        'unit_number' => $unit_number,
                        'is_active' => 1,
                    ]
                )
                ->when(
                    !empty($buliding), 
                    function ($query) use ($buliding) {
                        return $query->where('building', $buliding);
                    }
                )->get();

        $barcodeId = $barcode[0]->barcode_id;


        $barcode_id = $barcodeId;
        $building = $request->buliding;
        $lat = $request->lat;
        $long = $request->long;

        if (!$this->checkLocation($barcode_id, $building, $lat, $long)) {
            
            $data = [
                'responseCode' => 201, 
                'responseMsg' => 'Task cannot be completed '
                . 'at your current location. '
                . 'User must be within valid radius of property address.'
            ];
            
            $response = $this->setData($data);
            
            return response()->json(
                [
                    'message' => 'success',
                    'data' => $response,
                    'status' => 200
                ],
                200
            );
        }

        if ($this->isEmployeeValid($barcodeId)) {
            $pickupType = \App\Service::select('pickup_type')
                            ->where(
                                'property_id', 
                                function ($query) use ($barcodeId) {
                                    $query->from('units')
                                    ->select('property_id')
                                    ->where("is_active", 1)
                                    ->where("barcode_id", $barcodeId);
                                }
                            )->get();


            $checkCount = Activitylogs::where("barcode_id", $barcodeId)
                    ->where("type", 2)
                    ->whereDate("created_at", date("Y-m-d"))
                    ->get();

            if ($checkCount->count() == 0) {
                if ($pickupType[0]->pickup_type == 1) {
                    $msg = "Waste Collected";
                    $this->sendnotification($barcodeId, date("Y-m-d"));
                    
                    Activitylogs::create(
                        [
                            'text' => $msg,
                            'user_id' => $this->userId,
                            'updated_by' => $this->userId,
                            'barcode_id' => $barcodeId,
                            'type' => 2,
                            'latitude' => $request->lat,
                            'longitude' => $request->long,
                            'wast' => 1,
                            'ip_address' => $request->ip()
                        ]
                    );
                } elseif ($pickupType[0]->pickup_type == 2) {
                    $msg = "Recycle Done";
                    $this->sendnotification($barcodeId, date("Y-m-d"));

                    Activitylogs::create(
                        [
                            'text' => $msg,
                            'user_id' => $this->userId,
                            'updated_by' => $this->userId,
                            'barcode_id' => $barcodeId,
                            'type' => 2,
                            'latitude' => $request->lat,
                            'longitude' => $request->long,
                            'recycle' => 1,
                            'ip_address' => $request->ip()
                        ]
                    );
                } elseif ($pickupType[0]->pickup_type == 3) {
                    $msg = "Waste Collected";
                    $this->sendnotification($barcodeId, date("Y-m-d"));

                    Activitylogs::create(
                        [
                            'text' => $msg,
                            'user_id' => $this->userId,
                            'updated_by' => $this->userId,
                            'barcode_id' => $barcodeId,
                            'type' => 2,
                            'latitude' => $request->lat,
                            'longitude' => $request->long,
                            'wast' => 1,
                            'ip_address' => $request->ip()
                        ]
                    );
                }
                
            } elseif ($checkCount->count() == 1 
                    && $pickupType[0]->pickup_type == 3) {
                
                if ($checkCount[0]->recycle != 1) {
                    
                    $msg = "Recycle Done";
                    Activitylogs::where("barcode_id", $barcode_id)
                            ->where("type", 2)
                            ->where("id", $checkCount[0]->id)
                            ->update(["recycle" => 1, "text" => $msg]);
                } else {
                    $msg = "Recycle Already Done";
                }
            } else {
                $msg = "Already Pickup.";
            }

            $data = ['responseCode' => 200, 'responseMsg' => $msg];
            $response = $this->setData($data);
            return response()->json(
                [
                    'message' => 'success', 
                    'data' => $response, 
                    'status' => 200
                ], 
                200
            );
        } else {
            
            $data = [
                'responseCode' => 201, 
                'responseMsg' => 'Invalid QR code.'
            ];
            
            $response = $this->setData($data);
            return response()->json(
                [
                    'message' => 'success',
                    'data' => $response,
                    'status' => 200
                ],
                200
            );
        }
    }

    /**
     * Scan Revock Barcode
     *
     * Version v5:
     * 1] Implemented walk through rollback functionality.
     *
     * @response {
     *  "message": "success",
     *  "data": {
     *      "responseCode": 200,
     *      "responseMsg": "Pickup Rolled-back"
     *   },
     *  "status": 200
     * }
     */
    public function scanRevockBarcode(Request $request) {

        $validator = Validator::make($request->all(), [
                    'activity_id' => 'required|string|exists:activity_log,id,deleted_at,NULL',
                    'lat' => 'required',
                    'long' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->errorFormat($validator->errors());
        }

        $barcode_id = Activitylogs::where('id', $request->activity_id)->get();

        if ($barcode_id->isNotEmpty()) {
            $count = $delete = 0;

            if (isset($barcode_id[0]->type) && $barcode_id[0]->type == 1) {
                $id = $request->activity_id;
                $update = Units::where('barcode_id', function ($query) use ($id) {
                            $query->from('activity_log')
                                    ->select('barcode_id')->where('id', $id);
                        })->update([
                    'is_active' => 0,
                    //'unit_number' => NULL,
                    'activation_date' => date("Y-m-d H:i:s"),
                ]);

                Activitylogs::create([
                    'text' => 'QR code Deactivated',
                    'user_id' => $this->userId,
                    'updated_by' => $this->userId,
                    'barcode_id' => $barcode_id[0]['barcode_id'],
                    'type' => 5,
                    'property_id' => $barcode_id[0]['property_id'],
                    'building_id' => $barcode_id[0]['building_id'],
                    'latitude' => $request->lat,
                    'longitude' => $request->long,
                    'ip_address' => $request->ip()
                ]);

                $delete = Activitylogs::where('id', $request->activity_id)->delete();
                $data = ['responseCode' => 200, 'responseMsg' => 'QR code Deactivated.'];
            } elseif (isset($barcode_id[0]->type) && $barcode_id[0]->type == 2) {
                Activitylogs::create([
                    'text' => 'Pickup Rolled-back',
                    'user_id' => $this->userId,
                    'updated_by' => $this->userId,
                    'barcode_id' => $barcode_id[0]['barcode_id'],
                    'type' => 5,
                    'property_id' => $barcode_id[0]['property_id'],
                    'building_id' => $barcode_id[0]['building_id'],
                    'latitude' => $request->lat,
                    'longitude' => $request->long,
                    'ip_address' => $request->ip()
                ]);

                $delete = Activitylogs::where('id', $request->activity_id)->delete();
                $data = ['responseCode' => 200, 'responseMsg' => 'Pickup Rolled-back'];
            } elseif (isset($barcode_id[0]->type) && $barcode_id[0]->type == 3) {

                //User is able to rollabck violation when violation status is "NEW" (Task: #762).
                $checkStatus = \App\Violation::where([
                            'status' => 0, //0 means new status.
                            'activity_id' => $request->activity_id
                        ])->get();

                if ($checkStatus->isNotEmpty()) {

                    Activitylogs::create([
                        'text' => 'Violation Rolled-back',
                        'user_id' => $this->userId,
                        'updated_by' => $this->userId,
                        'barcode_id' => $barcode_id[0]['barcode_id'],
                        'type' => 5,
                        'property_id' => $barcode_id[0]['property_id'],
                        'building_id' => $barcode_id[0]['building_id'],
                        'latitude' => $request->lat,
                        'longitude' => $request->long,
                        'ip_address' => $request->ip()
                    ]);

                    $violationStatus = \App\Violation::where('activity_id', $request->activity_id)
                            ->update(['status' => 1]);

                    $delete = Activitylogs::where('id', $request->activity_id)->delete();

                    $data = ['responseCode' => 200, 'responseMsg' => 'Violation Rolled-back'];
                } else {
                    $delete = true;
                    $data = ['responseCode' => 201, 'responseMsg' => 'Status is already changed by admin.'];
                }
            } elseif (isset($barcode_id[0]->type) && $barcode_id[0]->type == 8) {
                Activitylogs::create([
                    'text' => 'Walk Through Rolled-back',
                    'user_id' => $this->userId,
                    'updated_by' => $this->userId,
                    'type' => 5,
                    'property_id' => $barcode_id[0]['property_id'],
                    'building_id' => $barcode_id[0]['building_id'],
                    'latitude' => $request->lat,
                    'longitude' => $request->long,
                    'ip_address' => $request->ip()
                ]);

                \App\walkThroughRecord::where('activity_id', $barcode_id[0]->id)->delete();

                $delete = Activitylogs::where('id', $request->activity_id)->delete();

                $data = ['responseCode' => 200, 'responseMsg' => 'Walk Through Rolled-back'];
            }

            if ($delete) {
                $response = $this->setData($data);
                return response()->json(['message' => "success", 'data' => $response, 'status' => 200], 200);
            } else {
                $data = ['responseCode' => 200, 'responseMsg' => 'Activity Id Not Found.'];
                $response = $this->setData($data);
                return response()->json(['message' => "success", 'data' => $response, 'status' => 201], 200);
            }
        } else {
            $data = [
                'responseCode' => 201,
                'responseMsg' => 'QR code Not Active.'
            ];
            return response()->json(['message' => 'success.', 'data' => $data, 'status' => 200], 200);
        }
    }

    /**
     * Create Violation
     *
     * @response {
     *  "message": "success",
     *  "data": {
     *      "responseCode": 200,
     *      "responseMsg": "Violation Reported."
     *   },
     *  "status": 200
     * }
     */
    public function createViolation(Request $request) {

        $validator = Validator::make($request->all(), [
                    'reasonid' => 'required|integer',
                    'requiredactionid' => 'required|integer',
                    'image' => 'nullable',
                    'barcode_id' => ['required', 'string',
                                Rule::exists('units', 'barcode_id')
                                ->whereNull('deleted_at')
                                ->where('is_active', 1)
                    ],
                    'image_type' => "required_with:image"
                        ], ['barcode_id.exists' => "Invalid Barcode"]);

        if ($validator->fails()) {
            return $this->errorFormat($validator->errors());
        }

        if ($this->isEmployeeValid($request->barcode_id)) {
            $activityId = Activitylogs::create([
                        'text' => 'Violation Reported',
                        'user_id' => $this->userId,
                        'updated_by' => $this->userId,
                        'barcode_id' => $request->barcode_id,
                        'type' => 3,
                        //'latitude' => $request->lat,
                        //'longitude' => $request->long,
                        'ip_address' => $request->ip()
            ]);

            $violation = \App\Violation::create([
                        'violation_reason' => $request->reasonid,
                        'violation_action' => $request->requiredactionid,
                        'user_id' => $this->userId,
                        'activity_id' => $activityId->id,
                        'barcode_id' => $request->barcode_id
            ]);

            if ($request->image != "") {
                $this->base64ToImage($request->image, $violation->id, $request->image_type);
            }

            if ($violation) {
                $data = ['responseCode' => '200', 'responseMsg' => 'Violation Reported.'];
                $response = $this->setData($data);
                return response()->json(['message' => "success", 'data' => $response, 'status' => 200], 200);
            } else {
                $data = ['responseCode' => '200', 'responseMsg' => 'QR code Not Found.'];
                return response()->json(['message' => "success", 'data' => $data, 'status' => 200], 200);
            }
        } else {
            $data = ['responseCode' => 201, 'responseMsg' => 'Invalid QR code.'];
            $response = $this->setData($data);
            return response()->json(['message' => 'success', 'data' => $response, 'status' => 200], 200);
        }
    }

    /**
     * Create ViolationV1
     *
     * Implemented multiple image upload.
     *
     * @response {
     *  "message": "success",
     *  "data": {
     *      "responseCode": 200,
     *      "responseMsg": "Violation Reported."
     *   },
     *  "status": 200
     * }
     */
    public function createViolationV1(Request $request) 
    {

        $validator = Validator::make(
            $request->all(),
            [
                    'reasonid' => 'required|integer',
                    'requiredactionid' => 'required|integer',
                    'special_note' => 'nullable',
                    'requestid' => 'nullable',
                    'barcode_id' => ['required', 'string',
                                Rule::exists('units', 'barcode_id')
                                ->whereNull('deleted_at')
                                ->where('is_active', 1)
                    ]
            ],
            [
                'barcode_id.exists' => "Invalid Barcode"
            ]
        );

        $requestId = !empty($request->requestid) ? $request->requestid : "";

        if ($validator->fails()) {
            return $this->errorFormat($validator->errors(), $requestId);
        }

        if ($this->isEmployeeValid($request->barcode_id)) 
        {
            //Get Property Detail:Start.
            $propertyId = $this->propertyDetailByQrbarId($request->barcode_id);
            //Get Property Detail:End.

            $activityId = Activitylogs::create(
                [
                    'text' => 'Violation Reported',
                    'user_id' => $this->userId,
                    'updated_by' => $this->userId,
                    'barcode_id' => $request->barcode_id,
                    'type' => 3,
                    'property_id' => empty($propertyId[0]->property_id) ?: $propertyId[0]->property_id,
                    'ip_address' => $request->ip()
                ]
            );

            $violation = \App\Violation::create(
                [
                    'violation_reason' => $request->reasonid,
                    'violation_action' => $request->requiredactionid,
                    'user_id' => $this->userId,
                    'activity_id' => $activityId->id,
                    'special_note' => isset($request->special_note) ? $request->special_note : null,
                    'barcode_id' => $request->barcode_id
                ]
            );
            
            $imageCount = isset($request->uploadFiles) 
                    && count($request->uploadFiles) 
                    ? count($request->uploadFiles) : 0;
            
            $data = array();
            
            if ($imageCount > 0) {
                foreach ($request->uploadFiles as $file) {
                    $name = $file->getClientOriginalName();
                    $file->move(public_path() . '/uploads/violation/', $name);
                    $data[] = array(
                        'filename' => $name, 
                        'violation_id' => $violation->id
                    );
                }
            }
            if (!empty($data)) {
                \App\ViolationImages::insert($data);
            }

            if ($violation) {
                //Send email notification to admins
                // assigned to that property: Start
                $property = \App\Units::where('barcode_id', $request->barcode_id)->first();
                $user = \App\Property::where('id', $property->property_id)
                        ->with(
                            [
                                'getEmployee' => function ($query) {
                                    $query->where('role_id', getAdminId());
                                }
                            ]
                        )->get();
                
                //Send email notification to admins 
                //assigned to that property: Start
                try {
                    \Notification::send(
                        $user[0]->getEmployee,
                        new \App\Notifications\NewViolation()
                    );
                }
                catch(\Exception $e) {
                  //echo 'Message: ' .$e->getMessage();
                }
                //Send email notification to admins 
                //assigned to that property: End

                $data = [
                    'responseCode' => '200', 
                    'responseMsg' => 'Violation Reported.', 
                    'requestid' => $requestId
                ];
                
                $response = $this->setData($data);
                return response()->json(
                    [
                        'message' => "success", 
                        'data' => $response, 
                        'status' => 200
                    ],
                    200
                );
                
            } else {
                
                $data = [
                    'responseCode' => '200',
                    'responseMsg' => 'QR code Not Found.', 
                    'requestid' => $requestId
                ];
                return response()->json(
                    [
                        'message' => "success",
                        'data' => $data,
                        'status' => 200
                    ],
                    200
                );
            }
        } else {
            
            $data = [
                'responseCode' => 201, 
                'responseMsg' => 'Invalid QR code.', 
                'requestid' => $requestId
            ];
            
            $response = $this->setData($data);
            
            return response()->json(
                [
                    'message' => 'success',
                    'data' => $response,
                    'status' => 200
                ],
                200
            );
        }
    }

    protected function checkLocation(...$val) {

        $barcode_id = $val[0];
        $building = $val[1];

        $getLatLong = \App\Property::select('latitude', 'longitude', 'radius')
                        ->where(['id' => function ($query) use ($barcode_id) {
                                $query->select('property_id')
                                ->from('units')
                                ->where('barcode_id', $barcode_id)
                                ->whereNull('deleted_at');
                            }])->get();

        $lat1 = $getLatLong[0]->latitude;
        $long1 = $getLatLong[0]->longitude;
        $radius = $getLatLong[0]->radius;
        $lat2 = $val[2];
        $long2 = $val[3];
        $unit = "M";

        $km = $this->distance($lat1, $long1, $lat2, $long2, $unit);
//        echo $km . ' <= ' . $radius;
//        die();
        if ($km <= $radius) {
            //if ($km <= 1000) {
            return true;
        } else {
            return false;
        }
    }

    protected function checkLocationForWalkThrough($propertyId, $lat2, $long2) {

        $getLatLong = \App\Property::select('latitude', 'longitude', 'radius')
                        ->where('id', $propertyId)->get();

        $lat1 = $getLatLong[0]->latitude;
        $long1 = $getLatLong[0]->longitude;
        $radius = $getLatLong[0]->radius;
        $unit = "M";

        $km = $this->distance($lat1, $long1, $lat2, $long2, $unit);

        if ($km <= $radius) {
            //if ($km <= 1000) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Walk Through
     *
     * Version v5: Created functionality to add log in activity log table.
     *
     * @response {
     * "message": "success.",
     *      "data": {
     *          "responseCode": "200",
     *          "responseMsg": "Walk through successfully submitted."
     *      },
     * "status": 200
     * }
     */
    public function walkThrough(Request $request) {

        $validator = Validator::make($request->all(), [
                    'long' => 'required|string',
                    'lat' => 'required|string',
                    'propertyId' => 'required|integer|exists:properties,id,deleted_at,NULL',
                    'buliding' => 'nullable',
                    'buliding_id' => 'nullable'
        ]);

        if ($validator->fails()) {
            return $this->errorFormat($validator->errors());
        }

        $checkLocation = $this->checkLocationForWalkThrough($request->propertyId, $request->lat, $request->long);

        if ($checkLocation) {
            $buldingId = "";
            $bulidingId = \App\Building::select('id', 'property_id')
                    ->where('building_name', $request->buliding)
                    ->where('property_id', $request->propertyId)
                    ->get();

            if (isset($bulidingId[0]->id)) {
                $buldingId = $bulidingId[0]->id;
            } elseif (!empty($request->buliding_id)) {
                $buldingId = $request->buliding_id;
            }

            $checkIfExists = \App\walkThroughRecord::where('property_id', $request->propertyId)
                            ->where('building_name', $request->buliding)
                            //->whereDate('created_at', ' = ', $date)
                            ->whereBetween(DB::raw("convert_tz(updated_at,'UTC','" . $this->timezone . "')"), [$this->start, $this->end])
                            ->when(isset($buldingId) && !empty($buldingId), function ($query) use ($buldingId) {
                                $query->where('building_id', $buldingId);
                            })
                            ->get()->count();

            if ($checkIfExists < 1) {
                // Generate Log For Walk Through: Start
                $activityId = Activitylogs::create([
                            'text' => 'Walk Through Done',
                            'user_id' => $this->userId,
                            'updated_by' => $this->userId,
                            'property_id' => $request->propertyId,
                            'building_id' => $buldingId,
                            'type' => 8,
                            'latitude' => $request->lat,
                            'longitude' => $request->long,
                            'ip_address' => $request->ip()
                ]);
                // Generate Log For Walk Through: End

                $walk = new \App\walkThroughRecord();
                $walk->property_id = $request->propertyId;
                $walk->activity_id = $activityId->id;

                if (isset($buldingId) && !empty($buldingId)) {
                    $walk->building_id = $buldingId;
                }
                $walk->save();
            }

            $data = ['responseCode' => '200', 'responseMsg' => 'Walk through successfully submitted.'];
            return response()->json(['message' => 'success.', 'data' => $data, 'status' => 200], 200);
        } else {
            $data = ['responseCode' => '201', 'responseMsg' => 'Task cannot be completed at your current location. User must be within valid radius of property address.'];
            return response()->json(['message' => 'success.', 'data' => $data, 'status' => 200], 200);
        }
    }

    /**
     * Activate Qrcode
     *
     * @response{
     *   "message": "success",
     *   "data": {
     *       "responseCode": "200",
     *       "responseMsg": "QR code Activated"
     *   },
     *   "status": 200
     * }
     */
    public function activateCode(Request $request) {

        $barcode_id = $request->barcode_id;
        $building = $request->building;
        $lat = $request->lat;
        $long = $request->long;

        $validator = Validator::make($request->all(), [
                    'address1' => 'required',
                    'address2' => 'required',
                    //'isLocationCheck' => 'required|integer',
                    'barcode_id' => 'required|exists:units,barcode_id',
                    'unit' => ['nullable', 'required_with:barcode_id',
                        Rule::unique('units', 'unit_number')->where(function ($query) use ($barcode_id, $building) {
                                    $query->where('property_id', function ($query) use ($barcode_id) {
                                                        $query->select('property_id')
                                                        ->from('units')
                                                        ->where('barcode_id', $barcode_id);
                                                    })
//->where('is_active', 1)
                                            ->where('building', $building)
                                            ->where('barcode_id', ' != ', $barcode_id);
                                })],
                    'long' => 'required|string',
                    'lat' => 'required|string',
                    'floor' => 'nullable',
                    'building' => 'nullable',
                    'building_id' => 'nullable',
//                    'building_id' => ['nullable', 'required_with:building',
//                        Rule::exists('buildings', 'id')->where(function ($query) use($building) {
//                                    $query->where('building_name', $building);
//                                })],
                    'type' => 'nullable|string', // 1or 2 for recycle or trash 3 = both
        ]);

//        if (empty($request->isLocationCheck)) {
//            $validator->after(function ($validator) use($barcode_id, $building, $lat, $long) {
//                if (!$this->checkLocation($barcode_id, $building, $lat, $long)) {
//
//                    $validator->errors()->add('field', 'Task cannot be completed at your current location. User must be within valid radius of property address.');
//                }
//            });
//        }

        if ($validator->fails()) {
            return $this->errorFormat($validator->errors());
        }

        if ($this->checkValidEmployee($request->barcode_id)) {
            $updates = \App\Units::where('barcode_id', $request->barcode_id)->first();

            $updates->address1 = $request->address1;
            $updates->address2 = $request->address2;


            $updates->unit_number = $request->unit;
            $updates->longitude = $request->long;
            $updates->latitude = $request->lat;

            if (!empty($request->building)) {
                $updates->building = $request->building;
            }

            if (!empty($request->building_id)) {
                $updates->building_id = $request->building_id;
            }

            $updates->floor = $request->floor;
            $updates->type = $request->type;
            $updates->is_active = 1;
            $updates->activation_date = date("Y-m-d H:i:s");
            $update = $updates->save();


//            $update = Units::where('barcode_id', $request->barcode_id)->update([
//                'address1' => $request->address1,
//                'address2' => $request->address2,
//                'unit_number' => $request->unit,
//                'longitude' => $request->long,
//                'latitude' => $request->lat,
//                'building' => $request->building,
//                'floor' => $request->floor,
//                'type' => $request->type,
//                'is_active' => 1,
//                'activation_date' => date("Y-m-d H:i:s"),
//            ]);

            if (isset($update) && !empty($update)) {
                $data = ['responseCode' => '200', 'responseMsg' => 'QR code Activated'];
                $response = $this->setData($data);

                $activityId = Activitylogs::create([
                            'text' => 'QR code Activated',
                            'user_id' => $this->userId,
                            'updated_by' => $this->userId,
                            'barcode_id' => $request->barcode_id,
                            'type' => 1,
                            'latitude' => $request->lat,
                            'longitude' => $request->long,
                            'ip_address' => $request->ip()
                ]);
                return response()->json(["message" => 'success', 'data' => $response, 'status' => 200], 200);
            } else {
                $data = [
                    'responseCode' => '201',
                    'responseMsg' => 'QR code Not Found.'
                ];
                return response()->json(['message' => 'success.', 'data' => $data, 'status' => 200], 200);
            }
        } else {
            $data = ['responseCode' => '201', 'responseMsg' => 'Invalid QR code.'];
            $response = $this->setData($data);
            return response()->json(['message' => 'success', 'data' => $response, 'status' => 200], 200);
        }
    }

    /**
     * Activate Qrcode V2
     *
     * @response {
     * "message": "success",
     * "data": {
     *    "responseCode": "200",
     *    "responseMsg": "QR code Activated"
     * },
     * "status": 200
     * }
     */
    public function activateCodeV2(Request $request) {

        $barcode_id = $request->barcode_id;
        $building = $request->building;
        $building_id = $request->building_id;
        $lat = $request->lat;
        $long = $request->long;

        $validator = Validator::make($request->all(), [
                    'address1' => 'required',
                    'address2' => 'required',
                    //'isLocationCheck' => 'required|integer',
                    'barcode_id' => 'required|exists:units,barcode_id',
                    'unit' => ['nullable', 'required_with:barcode_id',
                        Rule::unique('units', 'unit_number')->where(function ($query) use ($barcode_id, $building, $building_id) {
                                    $query->where('property_id', function ($query) use ($barcode_id) {
                                                        $query->select('property_id')
                                                        ->from('units')
                                                        ->where('barcode_id', $barcode_id);
                                                    })
//->where('is_active', 1)
                                            ->where(function ($query) use ($building, $building_id) {
                                                        $query->where('building', $building)
                                                        ->orWhere('building_id', $building_id);
                                                    })
                                            ->where('barcode_id', '!=', $barcode_id);
                                })],
                    'long' => 'required|string',
                    'lat' => 'required|string',
                    'floor' => 'nullable',
                    'building' => 'nullable',
                    'building_id' => 'nullable',
                    'unitAddress' => 'nullable',
                    'building_address' => 'nullable',
                    'type' => 'nullable|string', // 1or 2 for recycle or trash 3 = both
                        ], [
                    'barcode_id.exists' => "Barcode is not valid.",
        ]);

//        if (empty($request->isLocationCheck)) {
//            $validator->after(function ($validator) use($barcode_id, $building, $lat, $long) {
//                if (!$this->checkLocation($barcode_id, $building, $lat, $long)) {
//
//                    $validator->errors()->add('field', 'Task cannot be completed at your current location. User must be within valid radius of property address.');
//                }
//            });
//        }

        if ($validator->fails()) {
            return $this->errorFormat($validator->errors());
        }

        if ($this->checkValidEmployee($request->barcode_id)) {
            $updates = \App\Units::where('barcode_id', $request->barcode_id)
                    ->with('getPropertyDetail')
                    ->first();

            if ($request->type == 4) {
                $updates->address1 = $request->unitAddress;
                $updates->address2 = $request->unitAddress;
            } elseif ($request->type == 1) {
                $updates->address1 = $request->address1;
                $updates->address2 = $request->address2;
            }

            $updates->unit_number = $request->unit;
            //$updates->longitude = $request->long;
            $updates->longitude = $updates->getPropertyDetail->longitude;
            //$updates->latitude = $request->lat;
            $updates->latitude = $updates->getPropertyDetail->latitude;

            if (!empty($request->building)) {
                $updates->building = $request->building;
            }

            if (!empty($request->building_id)) {
                $updates->building_id = $request->building_id;
            } elseif (empty($request->building_id) && !empty($request->building)) {
                $building = \App\Building::where(['building_name' => $request->building, 'property_id' => $updates->property_id])->get();

                $updates->building_id = $building[0]->id;
            }

            $updates->floor = $request->floor;
            $updates->type = $request->type;
            $updates->is_active = 1;
            $updates->activation_date = date("Y-m-d H:i:s");
            $update = $updates->save();


            if (isset($request->building_address) && !empty($request->building_address)) {
                \App\Building::where('id', $updates->building_id)->update(['address' => $request->building_address]);
            }

            if (isset($request->address1) && !empty($request->address1)) {
                $property = \App\Property::find($updates->property_id);

                $address = $property->address . ', ' . $property->city . ', ' . $property->zip;
                $getLatLong = $this->getLatLong($address);

                if ($getLatLong['status'] == false) {
                    $data = [
                        'responseCode' => '201',
                        'responseMsg' => $getLatLong['msg']
                    ];
                    return response()->json(['message' => 'success.', 'data' => $data, 'status' => 200], 200);
                }

                $lat = $getLatLong['latitude'];
                $long = $getLatLong['longitude'];

                \App\Property::where(['id' => $updates->property_id])->update(["address" => $request->address1, "latitude" => $lat, "longitude" => $long]);
            }




//            $update = Units::where('barcode_id', $request->barcode_id)->update([
//                'address1' => $request->address1,
//                'address2' => $request->address2,
//                'unit_number' => $request->unit,
//                'longitude' => $request->long,
//                'latitude' => $request->lat,
//                'building' => $request->building,
//                'floor' => $request->floor,
//                'type' => $request->type,
//                'is_active' => 1,
//                'activation_date' => date("Y-m-d H:i:s"),
//            ]);

            if (isset($update) && !empty($update)) {
                $data = ['responseCode' => '200', 'responseMsg' => 'QR code Activated'];
                $response = $this->setData($data);

                Activitylogs::create([
                    'text' => 'QR code Activated',
                    'user_id' => $this->userId,
                    'updated_by' => $this->userId,
                    'barcode_id' => $request->barcode_id,
                    'type' => 1,
                    'latitude' => $request->lat,
                    'longitude' => $request->long,
                    'ip_address' => $request->ip()
                ]);
                return response()->json(["message" => 'success', 'data' => $response, 'status' => 200], 200);
            } else {
                $data = [
                    'responseCode' => '201',
                    'responseMsg' => 'QR code Not Found.'
                ];
                return response()->json(['message' => 'success.', 'data' => $data, 'status' => 200], 200);
            }
        } else {
            $data = ['responseCode' => '201', 'responseMsg' => 'Invalid QR code.'];
            $response = $this->setData($data);
            return response()->json(['message' => 'success', 'data' => $response, 'status' => 200], 200);
        }
    }

    /**
     * Get Activity Log
     *
     * @response {
     * "message": "success",
     * "data": {
     *     "records": [
     *         {
     *             "propertyAddress": "New Palasia",
     *             "name": "NewY",
     *             "city": "Indore",
     *             "state": "ALABAMA",
     *             "zip": "452010",
     *             "property_type": 4,
     *             "user_id": 215,
     *             "address1": "",
     *             "address2": "",
     *             "activity_id": 6157,
     *             "building": "",
     *             "floor": "",
     *             "unit_number": "",
     *             "action": "Property check in",
     *             "is_rollback": 1,
     *             "updated_at": "2019-02-13 13:46:54"
     *         },
     *         {
     *             "propertyAddress": "New Palasia",
     *             "name": "NewY",
     *             "city": "Indore",
     *             "state": "ALABAMA",
     *             "zip": "452010",
     *             "property_type": 4,
     *             "user_id": 215,
     *             "address1": "",
     *             "address2": "",
     *             "activity_id": 6120,
     *             "building": "",
     *             "floor": "",
     *             "unit_number": "",
     *             "action": "Property check in",
     *             "is_rollback": 1,
     *             "updated_at": "2019-02-11 14:51:17"
     *           }
     *       ],
     *       "responseCode": 200,
     *       "total_no_of_records": 46,
     *       "responseMsg": "success",
     *       "current_page": "1"
     *   },
     *   "status": 200
     * }
     */
    public function getActivityLog(Request $request) {

        $validator = Validator::make($request->all(), [
                    'record_per_page' => 'required|integer',
                    'page' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->errorFormat($validator->errors());
        }

        $limit = $request->record_per_page;
        $offset = $request->page ? $request->page : 1;
        $userId = $this->userId;

// Select activity log for a user
        $user_activity_logs = Activitylogs::where('user_id', $userId)
                ->whereNotNull('type')
                ->offset(($offset - 1) * $limit)
                ->latest()->limit($limit)
                ->get();

        if ($user_activity_logs->isNotEmpty()) {
            foreach ($user_activity_logs as $activity) {

                $address1 = $address2 = $property_address = $building_name = $unit_number = $floor = $property = $name = $city = $state = $zip = $propertyAddress = '';
                $is_rollback = $activity->type == 5 || $activity->type == 6 || $activity->type == 7 ? 0 : 1;
                $type = 0;


                //Check this activity log for violation (Task: #762):Start
                //TODO: Create new function fo this functionality;
                if (isset($activity->type) && $activity->type == 3) {

                    $checkIsNewViolation = \App\Violation::where('activity_id', $activity->id)
                            ->whereIn('status', [0])
                            ->get();

                    $is_rollback = $checkIsNewViolation->isNotEmpty() ? 1 : 0;
                }
                //Check this activity log for violation (Task: #762):End

                if (!empty($activity->barcode_id) && !empty($activity->unit)) {
                    $unit = $activity->unit;
                    $building = $unit->getBuildingDetail;
                    $property = $unit->getPropertyDetail;
                    $building_name = $unit->building;
                    $floor = $unit->floor;
                    $name = $property->name;
                    $city = $property->city;
                    $state = $property->getState->name;
                    $zip = $property->zip;
                    $type = $property->type;
                    $unit_number = $unit->unit_number;


                    $address1 = '';
                    $address2 = '';
                    $property_address = $property->address;
                    if ($property->type == 1) {
// Add main property adddress
                        $address1 = $address2 = $property->address;
                    } elseif ($property->type == 2 || $property->type == 3) {
// Add buildding address
                        if (isset($building->address)) {
                            $address1 = $address2 = $building->address;
                        }
                    } elseif ($property->type == 4) {
// Add unit address
                        $address1 = $unit->address1;
                        $address2 = $unit->address2;
                    }
                } else {
                    if (isset($activity->getPropertyDetailByPropertyIdWithTrashed)) {
                        $property = $activity->getPropertyDetailByPropertyIdWithTrashed;

                        $property_address = $property->address;
                        $name = $property->name;
                        $city = $property->city;
                        $state = $property->getState->name;
                        $zip = $property->zip;
                        $type = $property->type;

                        if ($property->type == 1) {
                            // Add main property adddress
                            $address1 = $address2 = $property->address;
                        } elseif ($property->type == 2 || $property->type == 3) {
                            // Add buildding address
                            if (isset($building->address)) {
                                $address1 = $address2 = $building->address;
                            }
                        } elseif ($property->type == 4) {
                            // Add unit address
                            $address1 = $property_address;
                            $address2 = $property_address;
                        }
                    }

                    if (isset($activity->getBuildingDetailWithTrashed)) {
                        $building = $activity->getBuildingDetailWithTrashed;
                        $building_name = $building->building_name;
                    }

                    if ($activity->type == 6 && !empty($activity->note)) {
                        $note = $activity->note;

                        $address1 = $note->address1;
                        $address2 = $note->address2;
                    }
                }

                $updated_at_carbon = $this->setUserTimeZoneForApi($activity->updated_at);
                //$updated_at_carbon = $activity->updated_at;

                $updated_at_formatted = '';
                if (!empty($updated_at_carbon) && $updated_at_carbon instanceof \Carbon\Carbon) {
                    $updated_at_formatted = $updated_at_carbon->format('Y-m-d H:i:s');
                }

                $records[] = [
                    'propertyAddress' => $property_address,
                    'name' => $name,
                    'city' => $city,
                    'state' => $state,
                    'zip' => $zip,
                    'property_type' => $type,
                    //'activity_type' => $activity->type,
                    'user_id' => $userId,
                    'address1' => $address1,
                    'address2' => $address2,
                    'activity_id' => $activity->id,
                    'building' => $building_name,
                    'floor' => $floor,
                    'unit_number' => $unit_number,
                    'action' => $activity->text,
                    'is_rollback' => $is_rollback,
                    'updated_at' => $updated_at_formatted
                ];
            }

            $data = [
                'records' => $records,
                'responseCode' => 200,
                'total_no_of_records' => Activitylogs::where('user_id', $this->userId)
                        ->orWhere('user_id', $this->userId)
                        ->whereNotNull('type')
                        ->count(),
                'responseMsg' => 'success',
                'current_page' => $offset
            ];
            $response = $this->setData($data);

            return response()->json(["message" => "success", 'data' => $response, 'status' => 200], 200);
        } else {
            $data = [
                'responseCode' => 201,
                'responseMsg' => 'No Active Log Found.',
            ];
            $response = $this->setData($data);
            return response()->json(["message" => "success", 'data' => $response, 'status' => 200], 200);
        }
    }

    /**
     * Note Reason
     *
     * @response {
     *  "message": "success",
     *  "data": {
     *      "reason": [
     *          {
     *              "id": 1,
     *              "reason": "Junk Removal Request"
     *          },
     *          {
     *              "id": 2,
     *              "reason": "Cleaning Service Request"
     *          },
     *          {
     *              "id": 3,
     *              "reason": "Valet Trash Service Confirmation (Alternative)"
     *          },
     *           {
     *               "id": 4,
     *               "reason": "New bin needed"
     *           },
     *           {
     *               "id": 5,
     *               "reason": "Bin tag damaged"
     *           },
     *           {
     *               "id": 6,
     *               "reason": "Other"
     *           }
     *       ],
     *       "responseCode": 200,
     *       "responseMsg": "Note reason list."
     *   },
     *   "status": 200
     * }
     */
    public function note_reason(Request $request) {

        $reason = \App\NoteSubject::select('id', 'subject as reason')
                ->where('user_id', $this->subscriberId)
                ->orWhere('type', 0)
                ->get();
        $data = [
            'reason' => $reason,
            'responseCode' => 200,
            'responseMsg' => 'Note reason list.'
        ];
        $response = $this->setData($data);
        return response()->json(['message' => 'success', 'data' => $response, 'status' => 200], 200);
    }

    /**
     * Note
     *
     * @response {
     *   "message": "success",
     *   "data": {
     *       "responseCode": 200,
     *       "responseMsg": "Notes added successfully."
     *   },
     *   "status": 200
     * }
     */
    public function note(Request $request) {

        $validator = Validator::make($request->all(), [
                    'barcode_id' => 'nullable',
                    'address1' => 'required',
                    'address2' => 'required',
                    'unit' => 'nullable',
                    'requestid' => 'nullable',
                    'long' => 'required|string',
                    'lat' => 'required|string',
                    'reason' => 'required|string',
                    'activityLogId' => 'string',
                    'description' => 'required|string',
                    'image' => 'nullable',
                    'image_type' => "required_with:image",
        ]);

        $requestId = !empty($request->requestid) ? $request->requestid : "";

        if ($validator->fails()) {
            return $this->errorFormat($validator->errors(), $requestId);
        }

        $noteActivityId = Activitylogs::create([
                    'text' => 'Note Added',
                    'user_id' => $this->userId,
                    'updated_by' => $this->userId,
                    'barcode_id' => $request->barcode_id,
                    'type' => 6,
                    'latitude' => $request->lat,
                    'longitude' => $request->long,
                    'wast' => 1,
                    'ip_address' => $request->ip()
        ]);

        $insert = \App\BarcodeNotes::create([
                    "barcode_id" => $request->barcode_id,
                    "address1" => $request->address1,
                    "address2" => $request->address2,
                    "long" => $request->long,
                    "lat" => $request->lat,
                    "reason" => $request->reason,
                    "unit" => $request->unit,
                    "activityLogId" => $noteActivityId->id,
                    "description" => $request->description,
                    "user_id" => $this->userId
        ]);


        if (!empty($request->image)) {
            $this->base64ToImage($request->image, $insert->id, $request->image_type, 'note');
        }

        if ($insert) {
            $data = ['responseCode' => 200, 'responseMsg' => 'Notes added successfully.', 'requestid' => $requestId];
            return response()->json(["message" => 'success', 'data' => $data, 'status' => 200], 200);
        } else {
            $data = ['responseCode' => 201, 'responseMsg' => 'Notes not added.', 'requestid' => $requestId];
            return response()->json(['message' => 'success.', 'data' => $data, 'status' => 200], 200);
        }
    }

    /**
     * Get Employee Schedule
     *
     * @response {
     *    "message": "success",
     *    "data": {
     *        "units": [
     *            {
     *                "id": 1,
     *                "address": "START TOWER",
     *                "city": "Start City",
     *                "unit": [
     *                    {
     *                        "latitude": "1.1111111",
     *                        "longitude": "1.1111111",
     *                        "address1": "Demo11",
     *                        "address2": "demo1",
     *                        "floor": "4",
     *                        "building": "Property 1",
     *                        "unit_number": "102",
     *                        "barcode_id": "XE7W4YGDN5",
     *                        "pickup": 0
     *                    },
     *                    {
     *                        "latitude": "23.2599000",
     *                        "longitude": "77.4126000",
     *                        "address1": "Demo",
     *                        "address2": "demo",
     *                        "floor": "",
     *                        "building": "Property 1",
     *                        "unit_number": "101",
     *                       "barcode_id": "4BDLDRG7Z6",
     *                       "pickup": 0
     *                   },
     *                   {
     *                       "latitude": "21.9200001",
     *                       "longitude": "82.7799988",
     *                      "address1": "START TOWER App",
     *                      "address2": "start city app",
     *                      "floor": "",
     *                      "building": "Property 1",
     *                      "unit_number": "103",
     *                      "barcode_id": "KVRWVYQW47",
     *                     "pickup": 0
     *                  }
     *              ],
     *              "pickup": 0
     *          },
     *          {
     *              "id": 3,
     *              "address": "Galaxy Weblinks",
     *              "city": "Indore1",
     *              "unit": [
     *                  {
     *                       "latitude": "22.7236236",
     *                       "longitude": "75.8820350",
     *                       "address1": "Palasia",
     *                       "address2": "Indore",
     *                       "floor": "",
     *                       "building": "",
     *                       "unit_number": "100",
     *                       "barcode_id": "P9EWJQGY8N",
     *                       "pickup": 0
     *                   },
     *                   {
     *                        "latitude": "22.7236033",
     *                        "longitude": "75.8820694",
     *                        "address1": "Galaxy Weblinks",
     *                        "address2": "Indore1",
     *                     "floor": "",
     *                     "building": "",
     *                      "unit_number": "101",
     *                      "barcode_id": "P9EWJQQGY8",
     *                       "pickup": 0
     *                   }
     *               ],
     *               "pickup": 0
     *           },
     *       ],
     *       "total_unit": "205",
     *       "total_property": 25,
     *       "total_active_unit": 78,
     *       "pickup_remaining": 76,
     *       "pick_made": 2,
     *       "responseCode": 200,
     *       "responseMsg": "success."
     *   },
     *   "status": 200
     * }
     */
    public function getEmployeschedule(Request $request) {
        $validator = Validator::make($request->all(), [
                    'which_date' => 'nullable',
        ]);

        if (!empty($request->which_date)) {
            $whichDate = $request->which_date;
        } else {
            $whichDate = date('Y-m-d');
        }

        $userId = $this->userId;

        $total_property = $this->getPropertyByUserId($userId);
        $total_unit = $this->getPropertyTotalUnit($userId);
        $notPickupList = $this->getRemainingPickupProperty($userId, $whichDate);
        $totalActiveUnit = $this->getTotalActiveUnit($userId, $whichDate)->count();
        $pick_made = Activitylogs::where('user_id', $userId)
                ->whereDate('created_at', $whichDate)
                ->where('type', 2)
                ->get();


        $data = [
            'units' => $notPickupList,
            'total_unit' => $total_unit,
            'total_property' => $total_property->count(),
            'total_active_unit' => $totalActiveUnit,
            'pickup_remaining' => abs($totalActiveUnit - $pick_made->count()),
            'pick_made' => $pick_made->count(),
            'responseCode' => 200,
            'responseMsg' => 'success.'
        ];


        return response()->json(["message" => 'success', 'data' => $data, 'status' => 200], 200);
    }

    /**
     * Get Employee Schedule V2
     *
     * @response {
     * "message": "success",
     * "data": {
     *    "properties": [
     *        {
     *            "name": "Silver",
     *            "address": "Indore",
     *            "city": "Indore",
     *            "state": "INDIANA",
     *            "zip": "545445",
     *            "id": 25,
     *            "buildings": [
     *                {
     *                    "building": "2",
     *                    "unit": [
     *                        {
     *                            "latitude": "22.7236332",
     *                           "longitude": "75.8820407",
     *                           "address1": "Indore",
     *                           "address2": "Indore",
     *                           "floor": "",
     *                           "building": "2",
     *                           "unit_number": "100uab1dfg",
     * *                         "barcode_id": "JXG6PYKW54",
     *                           "is_active": 1,
     *                            "pickup": 0
     *                        }
     *                    ]
     *                }
     *            ]
     *        },
     *        {
     *            "name": "GSA New",
     *            "address": "Garden",
     *            "city": "Indore",
     *            "state": "INDIANA",
     *            "zip": "452010",
     *            "id": 101,
     *            "buildings": [
     *                {
     *                    "building": "First",
     *                    "unit": [
     *                         {
     *                            "latitude": "22.7237389",
     *                            "longitude": "75.8820639",
     *                            "address1": "",
     *                            "address2": "",
     *                            "floor": "2",
     *                             "building": "First",
     *                             "unit_number": "100",
     *                             "barcode_id": "QBWZNE3WYJ",
     *                             "is_active": 1,
     *                             "pickup": 0
     *                         }
     *                     ]
     *                  }
     *              ]
     *          }
     *      ],
     *      "total_unit": "205",
     *      "total_property": 25,
     *      "total_active_unit": 78,
     *       "pickup_remaining": 76,
     *       "pick_made": 2,
     *       "responseCode": 200,
     *       "responseMsg": "success."
     *   },
     *   "status": 200
     * }
     */
    public function getEmployescheduleV2(Request $request) {
        $validator = Validator::make($request->all(), [
                    'which_date' => 'nullable',
        ]);

        if (!empty($request->which_date)) {
            $whichDate = $request->which_date;
        } else {
            $whichDate = date('Y-m-d');
        }

        $userId = $this->userId;

        $total_property = $this->getPropertyByUserId($userId);
        $total_unit = $this->getPropertyTotalUnit($userId);
        $notPickupList = $this->getRemainingPickupPropertyV2($userId, $whichDate);
        $totalActiveUnit = $this->getTotalActiveUnit($userId, $whichDate)->count();
        $pick_made = Activitylogs::where('user_id', $userId)
                ->whereDate('created_at', $whichDate)
                ->where('type', 2)
                ->get();


        $data = [
            'properties' => $notPickupList,
            'total_unit' => $total_unit,
            'total_property' => $total_property->count(),
            'total_active_unit' => $totalActiveUnit,
            'pickup_remaining' => abs($totalActiveUnit - $pick_made->count()),
            'pick_made' => $pick_made->count(),
            'responseCode' => 200,
            'responseMsg' => 'success.'
        ];


        return response()->json(["message" => 'success', 'data' => $data, 'status' => 200], 200);
    }

    /**
     * Get Employee Schedule V3
     *
     * Version v5: Remove i-phone and android version check and created functionality to remove exception properties.
     *
     * @response {
     * "message": "success",
     * "data": {
     *    "properties": [
     *        {
     *            "name": "SFH",
     *            "address": "New Palasia",
     *            "city": "Indore",
     *            "state": "NEW HAMPSHIRE",
     *            "zip": "452010",
     *            "id": 182,
     *            "type": 1,
     *             "buildings": [
     *                {
     *                    "building": "",
     *                    "building_id": 94,
     *                    "building_address": "",
     *                    "walkThrough": 0,
     *                    "unit": [
     *                         {
     *                             "latitude": "22.7236384",
     *                             "longitude": "75.8820452",
     *                             "address1": "New Palasia",
     *                             "address2": "Indore, NEW HAMPSHIRE, 452010",
     *                             "floor": "",
     *                             "building": "SFH",
     *                             "unit_number": "100A",
     *                             "barcode_id": "K2LADEQVL3",
     *                             "is_active": 1,
     *                             "property_id": 182,
     *                             "building_id": 94,
     *                             "pickup": 0
     *                           }
     *                       ],
     *                       "get_building_detail": {
     *                           "id": 94,
     *                           "building_name": "SFH",
     *                           "unit_number": 1,
     *                           "property_id": 182,
     *                           "address": "",
     *                           "created_at": "2019-01-11 15:52:06",
     *                           "updated_at": "2019-02-20 12:28:08",
     *                           "deleted_at": ""
     *                      }
     *                 }
     *              ]
     *          }
     *      ],
     *       "total_unit": "35",
     *       "total_property": 6,
     *       "total_active_unit": 15,
     *       "pickup_remaining": 15,
     *       "pick_made": 0,
     *       "responseCode": 200,
     *       "responseMsg": "success."
     *    },
     *    "status": 200
     * }
     */
    public function getEmployescheduleV3(Request $request) {

        $validator = Validator::make($request->all(), [
                    'which_date' => 'nullable',
        ]);

        $userId = $this->userId;
        $whichDate = $request->which_date;
        $total_property = $this->getPropertyByUserId($userId);
        $total_unit = $this->getPropertyTotalUnit($userId);
        $notPickupList = $this->getRemainingPickupPropertyV3($userId, $whichDate);
        $totalActiveUnit = $this->getTotalActiveUnit($userId, $whichDate)->count();
        $pick_made = Activitylogs::where('user_id', $userId)
                ->whereDate('created_at', $whichDate)
                ->where('type', 2)
                ->get();


        $data = [
            'properties' => $notPickupList,
            'total_unit' => $total_unit,
            'total_property' => $total_property->count(),
            'total_active_unit' => $totalActiveUnit,
            'pickup_remaining' => abs($totalActiveUnit - $pick_made->count()),
            'pick_made' => $pick_made->count(),
            'responseCode' => 200,
            'responseMsg' => 'success.'
        ];


        return response()->json(["message" => 'success', 'data' => $data, 'status' => 200], 200);
    }

    public function getEmployescheduleV4(Request $request) {

        $validator = Validator::make($request->all(), [
                    'which_date' => 'nullable',
        ]);

        if (!empty($request->which_date)) {
            $whichDate = $request->which_date;
        } else {
            $whichDate = date('Y-m-d');
        }

        $userId = $this->userId;

        $total_property = $this->getPropertyByUserId($userId);
        $total_unit = $this->getPropertyTotalUnit($userId);
        $notPickupList = $this->getRemainingPickupPropertyV4($userId, $whichDate);
        $totalActiveUnit = $this->getTotalActiveUnit($userId, $whichDate)->count();
        $pick_made = Activitylogs::where('user_id', $userId)
                ->whereDate('created_at', $whichDate)
                ->where('type', 2)
                ->get();


        $data = [
            'properties' => $notPickupList,
            'total_unit' => $total_unit,
            'total_property' => $total_property->count(),
            'total_active_unit' => $totalActiveUnit,
            'pickup_remaining' => abs($totalActiveUnit - $pick_made->count()),
            'pick_made' => $pick_made->count(),
            'responseCode' => 200,
            'responseMsg' => 'success.'
        ];


        return response()->json(["message" => 'success', 'data' => $data, 'status' => 200], 200);
    }

    /**
     * Add Note Schedule
     *
     * @response {
     * "message": "success",
     * "data": {
     *    "units": [
     *        {
     *            "id": 134,
     *            "name": "Paradise",
     *            "address": "New Palasia",
     *            "city": "Indore",
     *            "state": 1,
     *            "zip": "452010",
     *            "hasMultipleUnit": 1,
     *            "unit": [
     *                {
     *                    "latitude": "19.0176147",
     *                    "longitude": "72.8561644",
     *                    "address1": "New Palasiya",
     *                    "address2": "Indore",
     *                    "floor": "2",
     *                    "building": "Para 1",
     *                    "unit_number": "100",
     *                    "barcode_id": "4PLNQ82XLJ"
     *                }
     *            ]
     *        },
     *        {
     *            "id": 182,
     *            "name": "Single",
     *            "address": "New Palasia",
     *            "city": "Indore",
     *            "state": 28,
     *            "zip": "452010",
     *            "hasMultipleUnit": 0,
     *            "unit": [
     *                {
     *                    "latitude": "22.7236384",
     *                    "longitude": "75.8820452",
     *                    "address1": "New Palasia",
     *                     "address2": "Indore, NEW HAMPSHIRE, 452010",
     *                     "floor": "",
     *                     "building": "Single",
     *                     "unit_number": "100A",
     *                     "barcode_id": "K2LADEQVL3"
     *                  }
     *              ]
     *          }
     *       ],
     *       "total_unit": "25",
     *       "total_property": 4,
     *       "total_active_unit": 6,
     *       "pickup_remaining": 6,
     *       "pick_made": 0,
     *       "responseCode": 200,
     *       "responseMsg": "success."
     *   },
     *   "status": 200
     * }
     */
    public function addNoteSchedule(Request $request) {

        $validator = Validator::make($request->all(), [
                    'which_date' => 'nullable',
        ]);

        if (!empty($request->which_date)) {
            $whichDate = $request->which_date;
        } else {
            $whichDate = date('Y-m-d');
        }

        $userId = $this->userId;

        $total_property = $this->getPropertyByUserId($userId);
        $total_unit = $this->getPropertyTotalUnit($userId);
        $notPickupList = $this->getAddNoteUnit($userId, $whichDate);
        $totalActiveUnit = $this->getTotalActiveUnit($userId, $whichDate)->count();
        $pick_made = Activitylogs::where('user_id', $userId)
                ->whereDate('created_at', $whichDate)
                ->where('type', 2)
                ->get();


        $data = [
            'units' => $notPickupList ? $notPickupList : [],
            'total_unit' => $total_unit,
            'total_property' => $total_property->count(),
            'total_active_unit' => $totalActiveUnit,
            'pickup_remaining' => abs($totalActiveUnit - $pick_made->count()),
            'pick_made' => $pick_made->count(),
            'responseCode' => 200,
            'responseMsg' => 'success.'
        ];


        return response()->json(["message" => 'success', 'data' => $data, 'status' => 200], 200);
    }

    /**
     * Add Note Schedule V2
     *
     * @response {
     * "message": "success",
     * "data": {
     *   "properties": [
     *       {
     *           "id": 134,
     *           "name": "Paradise",
     *           "address": "New Palasia",
     *           "city": "Indore",
     *           "state": 1,
     *           "zip": "452010",
     *           "buildings": [
     *               {
     *                   "building": "Para 1",
     *                   "unit": [
     *                       {
     *                           "latitude": "19.0176147",
     *                           "longitude": "72.8561644",
     *                           "address1": "New Palasiya",
     *                           "address2": "Indore",
     *                           "floor": "2",
     *                           "building": "Para 1",
     *                           "unit_number": "100",
     *                           "barcode_id": "4PLNQ82XLJ"
     *                       }
     *                   ]
     *               }
     *           ]
     *       },
     *       {
     *           "id": 136,
     *           "name": "NewY",
     *           "address": "New Palasia",
     *           "city": "Indore",
     *           "state": 1,
     *           "zip": "452010",
     *           "buildings": [
     *                 {
     *                "building": "NewY 1",
     *                "unit": [
     *                    {
     *                        "latitude": "22.7236984",
     *                        "longitude": "75.8817195",
     *                        "address1": "Indore",
     *                        "address2": "indore",
     *                        "floor": "2",
     *                        "building": "NewY 1",
     *                        "unit_number": "100",
     *                        "barcode_id": "A3GBJRVAWP"
     *                    },
     *                        {
     *                          "latitude": "22.7236308",
     *                          "longitude": "75.8820416",
     *                           "address1": "New Palasia",
     *                            "address2": "indore",
     *                          "floor": "100",
     *                             "building": "NewY 1",
     *                             "unit_number": "Atlanta",
     *                             "barcode_id": "E7W438EYLD"
     *                         },
     *                         {
     *                             "latitude": "22.7235983",
     *                             "longitude": "75.8820408",
     *                             "address1": "New Palasia",
     *                             "address2": "indore",
     *                             "floor": "101",
     *                             "building": "NewY 1",
     *                             "unit_number": "Atlantb",
     *                             "barcode_id": "A3GBJRVKWP"
     *                         },
     *                         {
     *                             "latitude": "19.0176147",
     *                             "longitude": "72.8561644",
     *                             "address1": "New Palasia",
     *                             "address2": "indore",
     *                             "floor": "25",
     *                             "building": "NewY 1",
     *                             "unit_number": "101",
     *                             "barcode_id": "9EWJM27DWY"
     *                         }
     *                     ]
     *                 }
     *             ]
     *         },
     *         {
     *             "id": 182,
     *             "name": "Single",
     *             "address": "New Palasia",
     *             "city": "Indore",
     *             "state": 28,
     *             "zip": "452010",
     *             "buildings": [
     *                 {
     *                     "building": "Single",
     *                     "unit": [
     *                         {
     *                             "latitude": "22.7236384",
     *                             "longitude": "75.8820452",
     *                             "address1": "New Palasia",
     *                             "address2": "Indore, NEW HAMPSHIRE, 452010",
     *                             "floor": "",
     *                             "building": "Single",
     *                             "unit_number": "100A",
     *                             "barcode_id": "K2LADEQVL3"
     *                         }
     *                     ]
     *                 }
     *              ]
     *          }
     *     ],
     *     "total_unit": "25",
     *     "total_property": 4,
     *     "total_active_unit": 6,
     *      "pickup_remaining": 6,
     *     "pick_made": 0,
     *    "responseCode": 200,
     *      "responseMsg": "success."
     *  },
     *  "status": 200
     * }
     */
    public function addNoteScheduleV2(Request $request) {

        $validator = Validator::make($request->all(), [
                    'which_date' => 'nullable',
        ]);

        if (!empty($request->which_date)) {
            $whichDate = $request->which_date;
        } else {
            $whichDate = date('Y-m-d');
        }

        $userId = $this->userId;

        $total_property = $this->getPropertyByUserId($userId);
        $total_unit = $this->getPropertyTotalUnit($userId);
        $notPickupList = $this->getAddNoteUnitV2($userId, $whichDate);
        $totalActiveUnit = $this->getTotalActiveUnit($userId, $whichDate)->count();
        $pick_made = Activitylogs::where('user_id', $userId)
                ->whereDate('created_at', $whichDate)
                ->where('type', 2)
                ->get();


        $data = [
            'properties' => $notPickupList ? $notPickupList : [],
            'total_unit' => $total_unit,
            'total_property' => $total_property->count(),
            'total_active_unit' => $totalActiveUnit,
            'pickup_remaining' => abs($totalActiveUnit - $pick_made->count()),
            'pick_made' => $pick_made->count(),
            'responseCode' => 200,
            'responseMsg' => 'success.'
        ];


        return response()->json(["message" => 'success', 'data' => $data, 'status' => 200], 200);
    }

    private function getAddNoteUnitV2($user_id, $date) {

        $totalBarcodePerProperty = 0;
        $totalBarcodeActivePerProperty = 0;
//DB::enableQueryLog();
        $notPickupList_data = array();
        $notPickupList_main = Property::select('id', 'address', 'city', 'state', 'zip', 'name')->whereIn('id', function ($query) use ($user_id, $date) {
                    $query->from('user_properties')
                            ->select('property_id')
                            ->where('user_id', $user_id)
                            ->where('status', 1)
                            ->where("deleted_at", null)
                            ->whereIn('property_id', function ($query) use ($user_id, $date) {
                                $query->from("units")
                                ->select('property_id')
                                ->whereNotIn('barcode_id', function ($query) use ($user_id, $date) {
                                    $query->from('activity_log')
                                    ->select('barcode_id')
//->where('user_id', $user_id)
                                    ->where("deleted_at", null)
                                    ->whereDate('created_at', "'" . $date . "'");
                                })
                                ->whereIn("property_id", function ($query) {
                                    $query->from('property_frequencies')
                                    ->select("property_id")
                                    ->where("day", $this->getCurrentDay())
                                    ->where("deleted_at", null)
                                    ->groupBy('property_id');
                                })
                                ->where("deleted_at", null)
                                ->groupBy('property_id')
                                ->where('is_active', 1);
                            });
                })->get();

        if ($notPickupList_main->count() > 0) {
            $i = 0;
            foreach ($notPickupList_main as $notPickupLists) {
                $mainProperty = $this->getPropertyType($notPickupLists->id);
                $punits = $mainProperty["units"] > 1 ? 1 : 0;

                $notPickupLists["buildings"] = Units::select(\DB::raw("IFNULL(building,'') as building"))
                        ->where("property_id", $notPickupLists->id)
                        ->where("is_active", 1)
                        ->whereNotIn('barcode_id', function ($query) use ($user_id, $date) {
                            $query->from('activity_log')
                            ->select('barcode_id')
                            //->where('user_id', $user_id)
                            ->where('type', 2)
                            ->whereNull('deleted_at')
                            ->whereDate('created_at', $date);
                        })
                        ->groupBy('building')
                        ->get();

                foreach ($notPickupLists["buildings"] as $notBuilding) {
                    if ($notBuilding->building != null) {
                        $notBuilding["unit"] = Units::select("latitude", "longitude", "address1", "address2", "floor", "building", "unit_number", 'barcode_id')
                                ->where("property_id", $notPickupLists->id)
                                ->where("building", $notBuilding->building)
                                ->where("is_active", 1)
                                ->whereNotIn('barcode_id', function ($query) use ($user_id, $date) {
                                    $query->from('activity_log')
                                    ->select('barcode_id')
//->where('user_id', $user_id)
                                    ->where('type', 2)
                                    ->whereNull('deleted_at')
                                    ->whereDate('created_at', $date);
                                })
                                ->get();

                        $notBuilding["unit"] = $this->setData($notBuilding["unit"]->toArray());
                    } else {
                        $notBuilding["unit"] = Units::select("latitude", "longitude", "address1", "address2", "floor", "building", "unit_number", 'barcode_id')
                                ->where("property_id", $notPickupLists->id)
//->where("building", $notBuilding->building)
                                ->whereNull("building")
                                ->where("is_active", 1)
                                ->whereNotIn('barcode_id', function ($query) use ($user_id, $date) {
                                    $query->from('activity_log')
                                    ->select('barcode_id')
//->where('user_id', $user_id)
                                    ->where('type', 2)
                                    ->whereNull('deleted_at')
                                    ->whereDate('created_at', $date);
                                })
                                ->get();

                        $notBuilding["unit"] = $this->setData($notBuilding["unit"]->toArray());
                    }
                }
//dd($notBuilding["unit"]);
                if (count($notPickupLists["buildings"]) > 0) {
                    $notPickupList_data[$i]['id'] = $notPickupLists->id;
                    $notPickupList_data[$i]['name'] = $notPickupLists->name;
                    $notPickupList_data[$i]['address'] = $notPickupLists->address;
                    $notPickupList_data[$i]['city'] = $notPickupLists->city;
                    $notPickupList_data[$i]['state'] = $notPickupLists->state;
                    $notPickupList_data[$i]['zip'] = $notPickupLists->zip;
                    $notPickupList_data[$i]["buildings"] = $this->setData($notPickupLists["buildings"]->toArray());

                    $totalBarcodeActivePerProperty = 0;
                    $i++;
                } else {
                    
                }
            }
        }

        if (isset($notPickupList_data) && !empty($notPickupList_data)) {
            return $notPickupList_data;
        } else {
            return false;
        }
    }

    /**
     * Add Note Schedule V3
     *
     * @response {
     *  "message": "success",
     *  "data": {
     *      "properties": [
     *          {
     *              "id": 136,
     *              "name": "NewY",
     *              "address": "New Palasia",
     *              "city": "Indore",
     *              "state": "ALABAMA",
     *              "zip": "452010",
     *              "type": 4,
     *              "buildings": [
     *                  {
     *                      "building": "NewY 1",
     *                      "building_id": 20,
     *                      "unit": [
     *                          {
     *                              "latitude": "22.7236984",
     *                              "longitude": "75.8817195",
     *                              "property_id": 136,
     *                              "floor": "2",
     *                              "building": "NewY 1",
     *                              "unit_number": "100",
     *                              "barcode_id": "A3GBJRVAWP",
     *                              "address1": "New Palasia",
     *                              "address2": "Indore, ALABAMA, 452010"
     *                          },
     * *                          {
     *                              "latitude": "22.7236308",
     *                              "longitude": "75.8820416",
     *                              "property_id": 136,
     *                              "floor": "100",
     *                              "building": "NewY 1",
     *                              "unit_number": "Atlanta",
     *                              "barcode_id": "E7W438EYLD",
     *                              "address1": "New Palasia",
     *                              "address2": "Indore, ALABAMA, 452010"
     *                          },
     *                          {
     *                              "latitude": "22.7235983",
     *                              "longitude": "75.8820408",
     *                              "property_id": 136,
     *                              "floor": "101",
     *                               "building": "NewY 1",
     *                               "unit_number": "Atlantb",
     *                               "barcode_id": "A3GBJRVKWP",
     *                               "address1": "New Palasia",
     *                               "address2": "Indore, ALABAMA, 452010"
     *                           },
     *                           {
     *                               "latitude": "19.0176147",
     *                               "longitude": "72.8561644",
     *                               "property_id": 136,
     *                               "floor": "25",
     *                               "building": "NewY 1",
     *                               "unit_number": "101",
     *                               "barcode_id": "9EWJM27DWY",
     *                               "address1": "New Palasia",
     *                               "address2": "Indore, ALABAMA, 452010"
     *                           }
     *                       ]
     *                   }
     *               ]
     *           }
     *       ],
     *       "total_unit": "25",
     *       "total_property": 4,
     *       "total_active_unit": 6,
     *       "pickup_remaining": 6,
     *       "pick_made": 0,
     *       "responseCode": 200,
     *       "responseMsg": "success."
     *   },
     *   "status": 200
     * }
     */
    public function addNoteScheduleV3(Request $request) {

        $validator = Validator::make($request->all(), [
                    'which_date' => 'nullable',
        ]);

        if (!empty($request->which_date)) {
            $whichDate = $request->which_date;
        } else {
            $whichDate = date('Y-m-d');
        }

        $userId = $this->userId;

        $total_property = $this->getPropertyByUserId($userId);
        $total_unit = $this->getPropertyTotalUnit($userId);
        $notPickupList = $this->getAddNoteUnitV3($userId, $whichDate);
        $totalActiveUnit = $this->getTotalActiveUnit($userId, $whichDate)->count();
        $pick_made = Activitylogs::where('user_id', $userId)
                ->whereDate('created_at', $whichDate)
                ->where('type', 2)
                ->get();


        $data = [
            'properties' => $notPickupList ? $notPickupList : [],
            'total_unit' => $total_unit,
            'total_property' => $total_property->count(),
            'total_active_unit' => $totalActiveUnit,
            'pickup_remaining' => abs($totalActiveUnit - $pick_made->count()),
            'pick_made' => $pick_made->count(),
            'responseCode' => 200,
            'responseMsg' => 'success.'
        ];


        return response()->json(["message" => 'success', 'data' => $data, 'status' => 200], 200);
    }

    private function getAddNoteUnitV3($user_id, $date) {

        $totalBarcodePerProperty = 0;
        $totalBarcodeActivePerProperty = 0;
        $startDate = \Carbon\Carbon::parse($date)->addHour(6);
        $endDate = \Carbon\Carbon::parse($date)->addDays(1)->addHour(5)->addMinute(59)->addSecond(59);
//DB::enableQueryLog();
        $notPickupList_data = array();
        $notPickupList_main = Property::select('id', 'address', 'city', 'state', 'zip', 'name', 'type')
                ->whereIn('id', function ($query) use ($user_id, $startDate, $endDate) {
                    $query->from('user_properties')
                    ->select('property_id')
                    ->where('user_id', $user_id)
                    ->where('status', 1)
                    ->where("deleted_at", null)
                    ->whereIn('property_id', function ($query) use ($startDate, $endDate) {
                        $query->from("units")
                        ->select('property_id')
//                        ->whereNotIn('barcode_id', function($query) use ($startDate, $endDate) {
//                            $query->from('activity_log')
//                            ->select('barcode_id')
//                            ->where("deleted_at", NULL)
//                            ->whereNotNull("barcode_id", NULL)
//                            ->whereBetween('created_at', [$startDate, $endDate]);
//                        })
                        ->whereIn("property_id", function ($query) {
                            $query->from('property_frequencies')
                            ->select("property_id")
                            ->where("day", $this->getCurrentDay())
                            ->where("deleted_at", null)
                            ->groupBy('property_id');
                        })
                        ->where("deleted_at", null)
                        ->groupBy('property_id')
                        ->where('is_active', 1);
                    });
                })
                ->when($this->platform == 'IOS' && $this->appVersion <= 13, function ($query) use ($startDate, $user_id) {
                    $query->where(function ($query) use ($startDate, $user_id) {
                        $query->whereHas('service', function ($query) use ($startDate) {
                            $query->whereDate('pickup_start', ' <= ', \Carbon\Carbon::parse($startDate))
                            ->whereDate('pickup_finish', ' >= ', \Carbon\Carbon::parse($startDate));
                        })
                        ->orWhere(function ($query) use ($user_id) {
                            $query->whereHas('checkInProperty', function ($query) use ($user_id) {
                                $query->whereBetween(DB::raw("convert_tz(updated_at,'UTC','" . $this->timezone . "')"), [$this->start, $this->end])
                                ->where('user_id', $user_id);
                            });
                        });
                    });
                })
                ->when($this->platform == 'ANDROID' || ($this->platform == 'IOS' && $this->appVersion >= 14), function ($query) use ($startDate, $user_id) {
                    $query->whereHas('service', function ($query) use ($startDate) {
                        $query->whereDate('pickup_start', ' <= ', \Carbon\Carbon::parse($startDate))
                        ->whereDate('pickup_finish', ' >= ', \Carbon\Carbon::parse($startDate));
                    })
                    ->whereHas('checkInProperty', function ($query) use ($user_id) {
                        $query->whereBetween(DB::raw("convert_tz(updated_at,'UTC','" . $this->timezone . "')"), [$this->start, $this->end])
                        ->where('user_id', $user_id);
                    });
                })


//                ->whereHas('service', function($query) use($startDate) {
//                    $query->whereDate('pickup_start', ' <= ', \Carbon\Carbon::parse($startDate))
//                    ->whereDate('pickup_finish', ' >= ', \Carbon\Carbon::parse($startDate));
//                })
//                ->whereHas('checkInProperty', function($query) {
//                    $query->whereBetween('updated_at', [$this->start, $this->end])
//                    ->where('user_id', $this->userId);
//                })
                ->get();

        if ($notPickupList_main->count() > 0) {
            $i = 0;
            foreach ($notPickupList_main as $notPickupLists) {
                $notPickupLists->state = $notPickupLists->getState->name;

                $mainProperty = $this->getPropertyType($notPickupLists->id);
                $punits = $mainProperty["units"] > 1 ? 1 : 0;

                $notPickupLists["buildings"] = Units::select(\DB::raw("IFNULL(building,'') as building"), 'building_id')
                        ->where("property_id", $notPickupLists->id)
                        ->where("is_active", 1)
                        ->whereNotIn('barcode_id', function ($query) use ($user_id, $startDate, $endDate) {
                            $query->from('activity_log')
                            ->select('barcode_id')
//->where('user_id', $user_id)
                            ->where('type', 2)
                            ->whereNull('deleted_at')
//->whereDate('created_at', $date);
                            //->whereBetween('created_at', [$startDate, $endDate]);
                            ->whereBetween(DB::raw("convert_tz(updated_at,'UTC','" . $this->timezone . "')"), [$this->start, $this->end]);
                        })
                        ->groupBy('building')
                        ->get();

                foreach ($notPickupLists["buildings"] as $notBuilding) {
///////////////////////BUILDING ADDRESS/////////////////////////////////

                    if (isset($notBuilding->building_id) && !empty($notBuilding->building_id) && $notBuilding->building_id != null) {
                        $buildingDetail = \App\Building::find($notBuilding->building_id);
                        if (isset($buildingDetail->address)) {
                            $notBuilding->building_address = $buildingDetail->address;
                        }
                    } else {
                        $notBuilding->building_address = "";
                    }

///////////////////////BUILDING ADDRESS///////////////////////////////

                    if ($notBuilding->building != null) {
                        $unit = Units::select("latitude", "longitude", "property_id", "floor", "building", "unit_number", 'barcode_id')
                                ->where("property_id", $notPickupLists->id)
                                ->where("building", $notBuilding->building)
                                ->where("is_active", 1)
                                ->whereNotIn('barcode_id', function ($query) use ($user_id, $startDate, $endDate) {
                                    $query->from('activity_log')
                                    ->select('barcode_id')
//->where('user_id', $user_id)
                                    ->where('type', 2)
                                    ->whereNull('deleted_at')
// ->whereDate('created_at', $date);
                                    //->whereBetween('created_at', [$startDate, $endDate]);
                                    ->whereBetween(DB::raw("convert_tz(updated_at,'UTC','" . $this->timezone . "')"), [$this->start, $this->end]);
                                })
                                ->get();

                        $unit = $unit->map(function ($value) {

                            $property = $value->getPropertyDetail;
                            $value->address1 = $property->address;
                            $value->address2 = $property->city . ', ' . $property->getState->name . ', ' . $property->zip;
                            unset($value->getPropertyDetail);
                            unset($value->getState);

                            return $value;
                        });

                        $notBuilding["unit"] = $this->setData($unit->toArray());
                    } else {
                        $unit = Units::select("latitude", "longitude", "property_id", "floor", "building", "unit_number", 'barcode_id')
                                ->where("property_id", $notPickupLists->id)
//->where("building", $notBuilding->building)
                                ->whereNull("building")
                                ->where("is_active", 1)
                                ->whereNotIn('barcode_id', function ($query) use ($user_id, $startDate, $endDate) {
                                    $query->from('activity_log')
                                    ->select('barcode_id')
//->where('user_id', $user_id)
                                    ->where('type', 2)
                                    ->whereNull('deleted_at')
//->whereDate('created_at', $date);
                                    //  ->whereBetween('created_at', [$startDate, $endDate]);
                                    ->whereBetween(DB::raw("convert_tz(updated_at,'UTC','" . $this->timezone . "')"), [$this->start, $this->end]);
                                })
                                ->get();

                        $unit = $unit->map(function ($value) {

                            $property = $value->getPropertyDetail;
                            $value->address1 = $property->address;
                            $value->address2 = $property->city . ', ' . $property->getState->name . ', ' . $property->zip;
                            unset($value->getPropertyDetail);
                            unset($value->getState);
                            return $value;
                        });

                        $notBuilding["unit"] = $this->setData($unit->toArray());
                    }

                    if (isset($notPickupLists->type) && $notPickupLists->type == 1) {
                        $notBuilding->building = "";
                    }
                }
//dd($notBuilding["unit"]);
                if (count($notPickupLists["buildings"]) > 0) {
                    $notPickupList_data[$i]['id'] = $notPickupLists->id;
                    $notPickupList_data[$i]['name'] = $notPickupLists->name;
                    $notPickupList_data[$i]['address'] = $notPickupLists->address;
                    $notPickupList_data[$i]['city'] = $notPickupLists->city;
                    $notPickupList_data[$i]['state'] = $notPickupLists->state;
                    $notPickupList_data[$i]['zip'] = $notPickupLists->zip;
                    $notPickupList_data[$i]['type'] = $notPickupLists->type;
                    $notPickupList_data[$i]["buildings"] = $this->setData($notPickupLists["buildings"]->toArray());

                    $totalBarcodeActivePerProperty = 0;
                    $i++;
                } else {
                    
                }
            }
        }

        if (isset($notPickupList_data) && !empty($notPickupList_data)) {
            return $notPickupList_data;
        } else {
            return false;
        }
    }

    /**
     * Work Plan Fillter Api
     *
     * @response {
     * "message": "success",
     * "data": {
     *    "units": [
     *        {
     *           "id": 134,
     *           "address": "New Palasia",
     *           "city": "Indore",
     *           "unit": [
     *               {
     *                   "latitude": "19.0176147",
     *                   "longitude": "72.8561644",
     *                   "address1": "New Palasiya",
     *                   "address2": "Indore",
     *                   "floor": "2",
     *                   "building": "Para 1",
     *                   "unit_number": "100",
     *                   "barcode_id": "4PLNQ82XLJ",
     *                   "pickup": 0
     *               }
     *           ],
     *           "pickup": 0
     *       },
     *       {
     *           "id": 136,
     *           "address": "New Palasia",
     *          "city": "Indore",
     *          "unit": [
     *              {
     *                  "latitude": "22.7236308",
     *                  "longitude": "75.8820416",
     *                  "address1": "New Palasia",
     *                  "address2": "indore",
     *                  "floor": "100",
     *                  "building": "NewY 1",
     *                  "unit_number": "Atlanta",
     *                  "barcode_id": "E7W438EYLD",
     *                  "pickup": 0
     *              },
     *              {
     *                  "latitude": "22.7235983",
     *                  "longitude": "75.8820408",
     *                  "address1": "New Palasia",
     *                  "address2": "indore",
     *                  "floor": "101",
     *                  "building": "NewY 1",
     *                  "unit_number": "Atlantb",
     *                  "barcode_id": "A3GBJRVKWP",
     *                  "pickup": 0
     *              },
     *              {
     *                  "latitude": "19.0176147",
     *                  "longitude": "72.8561644",
     *                   "address1": "New Palasia",
     *                   "address2": "indore",
     *                   "floor": "25",
     *                    "building": "NewY 1",
     *                  "unit_number": "101",
     *                  "barcode_id": "9EWJM27DWY",
     *                   "pickup": 0
     *               }
     *            ],
     *            "pickup": 0
     *        },
     *        {
     *            "id": 182,
     *            "address": "New Palasia",
     *            "city": "Indore",
     *            "unit": [
     *              {
     *                   "latitude": "22.7236384",
     *                    "longitude": "75.8820452",
     *                     "address1": "New Palasia",
     *                     "address2": "Indore, NEW HAMPSHIRE, 452010",
     *                     "floor": "",
     *                    "building": "Single",
     *                    "unit_number": "100A",
     *                      "barcode_id": "K2LADEQVL3",
     *                      "pickup": 0
     *                  }
     *              ],
     *              "pickup": 0
     *          }
     *       ],
     *      "total_unit": "25",
     *      "total_property": 4,
     *       "total_active_unit": 6,
     *       "pickup_remaining": 6,
     *       "pick_made": 0,
     *       "responseCode": 200,
     *       "responseMsg": "success."
     *   },
     *   "status": 200
     * }
     */
    public function workPlanFillterApi(Request $request) {
        $validator = Validator::make($request->all(), [
                    'which_date' => 'nullable',
                    'search_text' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->errorFormat($validator->errors());
        }

        if (!empty($request->which_date)) {
            $whichDate = $request->which_date;
        } else {
            $whichDate = date('Y-m-d');
        }

        $userId = $this->userId;
        $search_text = $request->search_text;

        $total_property = $this->getPropertyByUserId($userId);
        $total_unit = $this->getPropertyTotalUnit($userId);
        $notPickupList = $this->remainingPickupWorkPlanFillter($userId, $whichDate, $search_text);
        $totalActiveUnit = $this->getTotalActiveUnit($userId, $whichDate)->count();
        $pick_made = Activitylogs::where('user_id', $userId)
                ->whereDate('created_at', $whichDate)
                ->where('type', 2)
                ->get();


        $data = [
            'units' => $notPickupList,
            'total_unit' => $total_unit,
            'total_property' => $total_property->count(),
            'total_active_unit' => $totalActiveUnit,
            'pickup_remaining' => abs($totalActiveUnit - $pick_made->count()),
            'pick_made' => $pick_made->count(),
            'responseCode' => 200,
            'responseMsg' => 'success.'
        ];


        return response()->json(["message" => 'success', 'data' => $data, 'status' => 200], 200);
    }

    protected function distance($lat1, $lon1, $lat2, $lon2, $unit) {

        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
            return ($miles * 1.609344);
        } elseif ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }

    private function remainingPickupWorkPlanFillter($user_id, $date, $search_text) {

        $totalBarcodePerProperty = 0;
        $totalBarcodeActivePerProperty = 0;
//DB::enableQueryLog();

        $notPickupList = Property::select('id', 'address', 'city')->whereIn('id', function ($query) use ($user_id, $search_text) {
                    $query->from('user_properties')
                            ->select('property_id')
                            ->where('user_id', $user_id)
                            ->where('status', 1)
                            ->whereIn('property_id', function ($query) use ($user_id, $search_text) {
                                $query->from("units")
                                ->select('property_id')
                                ->whereIn("property_id", function ($query) use ($user_id, $search_text) {
                                    $query->from('property_frequencies')
                                    ->select("property_id")
                                    ->where("day", $this->getCurrentDay())
                                    ->where("deleted_at", null)
                                    ->groupBy('property_id');
                                })
                                ->where("deleted_at", null)
                                ->where(function ($query) use ($search_text) {
//$query->where('unit_numberq', $search_text);
                                    $query->where('unit_number', 'LIKE', "%" . $search_text . "%");
                                    $query->orWhere('address1', 'LIKE', "%" . $search_text . "%");
                                    $query->orWhere('address2', 'LIKE', "%" . $search_text . "%");
                                })
                                ->groupBy('property_id')
                                ->where('is_active', 1);
                            });
                })->get();

//echo "<pre>";print_r($notPickupList->toArray());die("");

        foreach ($notPickupList as $notPickupLists) {
            $notPickupLists["unit"] = Units::select("latitude", "longitude", "address1", "address2", "floor", "building", "unit_number", 'barcode_id')
                    ->where("property_id", $notPickupLists->id)
                    ->where("is_active", 1)
                    ->where(function ($query) use ($search_text) {
//  $query->where('unit_number', $search_text);
                        $query->where('unit_number', 'LIKE', "%" . $search_text . "%");
                        $query->orWhere('address1', 'LIKE', "%" . $search_text . "%");
                        $query->orWhere('address2', 'LIKE', "%" . $search_text . "%");
                    })
                    ->get();


            foreach ($notPickupLists["unit"] as $check_pickup) {
                $check_pickup["pickup"] = Activitylogs::where(['type' => 2, 'barcode_id' => $check_pickup['barcode_id']])
                                ->whereDate('created_at', ' = ', $date)
                                ->get()->count();
                if ($check_pickup["pickup"] > 0) {
                    $totalBarcodeActivePerProperty++;
                }
            }
            $notPickupLists["pickup"] = $notPickupLists['unit']->count() == $totalBarcodeActivePerProperty ? 1 : 0;
            $notPickupLists["unit"] = $this->setData($notPickupLists["unit"]->toArray());
            $totalBarcodeActivePerProperty = 0;
        }


//dd($notPickupList,DB::getQueryLog());

        if (isset($notPickupList) && !empty($notPickupList)) {
            return $notPickupList;
        } else {
            return false;
        }
    }

    /**
     * Work Plan Fillter Api V2
     *
     * @response {
     * "message": "success",
     * "data": {
     *   "properties": [
     *       {
     *           "name": "Property 1",
     *           "address": "START TOWER",
     *           "city": "Start City",
     *            "state": "ALABAMA",
     *            "zip": "452002",
     *           "id": 1,
     *            "type": 1,
     *           "buildings": [
     *               {
     *                   "building": "",
     *                   "building_id": "",
     *                   "building_address": "",
     *                   "walkThrough": 0,
     *                   "unit": [
     *                       {
     *                           "latitude": "1.1111111",
     *                           "longitude": "1.1111111",
     *                           "address1": "Demo11",
     *                           "address2": "Start City, ALABAMA, 452002",
     *                           "floor": "4",
     *                           "building": "Property 1",
     *                           "unit_number": "102",
     *                           "barcode_id": "XE7W4YGDN5",
     *                           "is_active": 1,
     *                            "property_id": 1,
     *                           "building_id": "",
     *                           "pickup": 0
     *                       },
     *                       {
     *                           "latitude": "23.2599000",
     *                           "longitude": "77.4126000",
     *                           "address1": "Demo",
     *                           "address2": "Start City, ALABAMA, 452002",
     *                           "floor": "",
     *                           "building": "Property 1",
     *                           "unit_number": "101",
     *                           "barcode_id": "4BDLDRG7Z6",
     *                          "is_active": 1,
     *                           "property_id": 1,
     *                           "building_id": "",
     *                          "pickup": 0
     *                      },
     *                       {
     *                          "latitude": "21.9200001",
     *                          "longitude": "82.7799988",
     *                          "address1": "START TOWER App",
     *                          "address2": "Start City, ALABAMA, 452002",
     *                          "floor": "",
     *                          "building": "Property 1",
     *                          "unit_number": "103",
     *                          "barcode_id": "KVRWVYQW47",
     *                          "is_active": 1,
     *                          "property_id": 1,
     *                          "building_id": 13,
     *                          "pickup": 0
     *                      }
     *                  ]
     *              }
     *          ]
     *      }
     *  ],
     *  "total_unit": "205",
     *  "total_property": 25,
     * "total_active_unit": 78,
     * "pickup_remaining": 76,
     *  "pick_made": 2,
     *  "responseCode": 200,
     *  "responseMsg": "success."
     * },
     * "status": 200
     * }
     */
    public function workPlanFillterApiV2(Request $request) {

        $validator = Validator::make($request->all(), [
                    'which_date' => 'nullable',
                    'search_text' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->errorFormat($validator->errors());
        }

        if (!empty($request->which_date)) {
            $whichDate = $request->which_date;
        } else {
            $whichDate = date('Y-m-d');
        }

        $userId = $this->userId;
        $search_text = $request->search_text;

        $total_property = $this->getPropertyByUserId($userId);
        $total_unit = $this->getPropertyTotalUnit($userId);
        $notPickupList = $this->remainingPickupWorkPlanFillterV2($userId, $whichDate, $search_text);
        $totalActiveUnit = $this->getTotalActiveUnit($userId, $whichDate)->count();
        $pick_made = Activitylogs::where('user_id', $userId)
                ->whereDate('created_at', $whichDate)
                ->where('type', 2)
                ->get();


        $data = [
            'properties' => $notPickupList,
            'total_unit' => $total_unit,
            'total_property' => $total_property->count(),
            'total_active_unit' => $totalActiveUnit,
            'pickup_remaining' => abs($totalActiveUnit - $pick_made->count()),
            'pick_made' => $pick_made->count(),
            'responseCode' => 200,
            'responseMsg' => 'success.'
        ];


        return response()->json(["message" => 'success', 'data' => $data, 'status' => 200], 200);
    }

    private function remainingPickupWorkPlanFillterV2($user_id, $date, $search_text) {

        $totalBarcodePerProperty = 0;
        $totalBarcodeActivePerProperty = 0;
        $notPickupList_data = array();
        $startDate = \Carbon\Carbon::parse($date)->addHour(6);
        $endDate = \Carbon\Carbon::parse($date)->addDays(1)->addHour(5)->addMinute(59)->addSecond(59);


        $notPickupList = Property::select('name', 'address', 'city', 'state', 'zip', 'id', 'type')->whereIn('id', function ($query) use ($user_id) {
                    $query->from('user_properties')
                    ->select('property_id')
                    ->where('user_id', $user_id)
                    ->where('status', 1)
                    ->where("deleted_at", null)
                    ->whereIn('property_id', function ($query) {
                        $query->from("units")
                        ->select('property_id')
                        ->whereIn("property_id", function ($query) {
                            $query->from('property_frequencies')
                            ->select("property_id")
                            ->where("day", $this->getCurrentDay())
                            ->where("deleted_at", null)
                            ->groupBy('property_id');
                        })
                        ->where("deleted_at", null)
                        ->groupBy('property_id')
                        ->where('is_active', 1);
                    });
                })
//                ->whereHas('service', function($query) use($startDate) {
//                    $query->whereDate('pickup_start', '<=', $startDate)
//                    ->whereDate('pickup_finish', '>=', $startDate);
//                })
//                ->whereHas('checkInProperty', function($query) {
//                    $query->whereBetween('updated_at', [$this->start, $this->end])
//                    ->where('user_id', $this->userId);
//                })
                ->when($this->platform == 'IOS' && $this->appVersion <= 13, function ($query) use ($startDate, $user_id) {
                    $query->where(function ($query) use ($startDate, $user_id) {
                        $query->whereHas('service', function ($query) use ($startDate) {
                            $query->whereDate('pickup_start', '<=', $startDate)
                            ->whereDate('pickup_finish', '>=', $startDate);
                        })
                        ->orWhere(function ($query) use ($user_id) {
                            $query->whereHas('checkInProperty', function ($query) {
                                $query->whereBetween(DB::raw("convert_tz(updated_at,'UTC','" . $this->timezone . "')"), [$this->start, $this->end])
                                ->where('user_id', $this->userId);
                            });
                        });
                    });
                })
                ->when($this->platform == 'ANDROID' || ($this->platform == 'IOS' && $this->appVersion >= 14), function ($query) use ($startDate, $user_id) {
                    $query->whereHas('service', function ($query) use ($startDate) {
                        $query->whereDate('pickup_start', '<=', $startDate)
                        ->whereDate('pickup_finish', '>=', $startDate);
                    })
                    ->whereHas('checkInProperty', function ($query) {
                        $query->whereBetween(DB::raw("convert_tz(updated_at,'UTC','" . $this->timezone . "')"), [$this->start, $this->end])
                        ->where('user_id', $this->userId);
                    });
                })
                ->get();

        if ($notPickupList->count() > 0) {
            foreach ($notPickupList as $notPickupLists) {
                $state_name = \App\State::select('name')->where('id', $notPickupLists->state)->first();
                $notPickupLists->state = $state_name->name;

                $notPickupLists["buildings"] = Units::select("building")
                                ->where(["property_id" => $notPickupLists->id, "is_active" => 1])
                                ->where(function ($query) use ($search_text) {
//$query->orWhere('address1', 'LIKE', "%" . $search_text . "%")
                                    $query->Where('unit_number', 'LIKE', "%" . $search_text . "%");
//->orWhere('address2', 'LIKE', "%" . $search_text . "%");
                                })->groupBy('building')->get();


                foreach ($notPickupLists["buildings"] as $notBuilding) {
                    $i = 0;
                    if ($notBuilding->building != null) {
                        $notBuilding["unit"] = Units::select("latitude", "longitude", "address1", "address2", "floor", "building", "unit_number", 'barcode_id', 'is_active')
                                        ->where([
                                            "property_id" => $notPickupLists->id,
                                            "building" => $notBuilding->building,
                                            "is_active" => 1])
                                        ->where(function ($query) use ($search_text) {
//$query->orWhere('address1', 'LIKE', "%" . $search_text . "%")
                                            $query->orWhere('unit_number', 'LIKE', "%" . $search_text . "%");
//->orWhere('address2', 'LIKE', "%" . $search_text . "%");
                                        })->get();

                        foreach ($notBuilding["unit"] as $check_pickup) {
                            $check_pickup["pickup"] = Activitylogs::where(['type' => 2, 'barcode_id' => $check_pickup['barcode_id']])
//->whereDate('created_at', ' = ', $date)
                                            // ->whereBetween('created_at', [$startDate, $endDate])
                                            ->whereBetween('created_at', [$startDate, $endDate])
                                            ->get()->count();
                            if ($check_pickup["pickup"] > 0) {
                                $totalBarcodeActivePerProperty++;
                            }
                        }

                        $notBuilding["unit"] = $this->setData($notBuilding["unit"]->toArray());
                    } else {
                        $notBuilding["unit"] = Units::select("latitude", "longitude", "address1", "address2", "floor", "building", "unit_number", 'barcode_id', 'is_active')
                                        ->where([
                                            "property_id" => $notPickupLists->id,
                                            "building" => $notBuilding->building,
                                            "is_active" => 1])
                                        ->where(function ($query) use ($search_text) {
//$query->orWhere('address1', 'LIKE', "%" . $search_text . "%")
                                            $query->orWhere('unit_number', 'LIKE', "%" . $search_text . "%");
//->orWhere('address2', 'LIKE', "%" . $search_text . "%");
                                        })->get();

                        foreach ($notBuilding["unit"] as $check_pickup) {
                            $check_pickup["pickup"] = Activitylogs::where(['type' => 2, 'barcode_id' => $check_pickup['barcode_id']])
//->whereDate('created_at', ' = ', $date)
                                            //->whereBetween('created_at', [$startDate, $endDate])
                                            ->whereBetween('created_at', [$startDate, $endDate])
                                            ->get()->count();
                            if ($check_pickup["pickup"] > 0) {
                                $totalBarcodeActivePerProperty++;
                            }
                        }

                        $notBuilding["unit"] = $this->setData($notBuilding["unit"]->toArray());
                    }
                }

// dump($notPickupLists["buildings"]);
// die('.....');
                $notPickupLists["buildings"] = $this->setData($notPickupLists["buildings"]->toArray());
                $totalBarcodeActivePerProperty = 0;
            }
        }


//dd($notPickupList,DB::getQueryLog());



        foreach ($notPickupList as $notPickupLists) {
            if (count($notPickupLists->buildings) > 0) {
                $notPickupList_data[] = $notPickupLists;
            }
//dump($notPickupLists);
        }

// if (isset($notPickupList) && !empty($notPickupList)) {
//     return $notPickupList;
// } else {
//     return false;
// }

        return $notPickupList_data;
    }

    private function getAddNoteUnit($user_id, $date) {

        $totalBarcodePerProperty = 0;
        $totalBarcodeActivePerProperty = 0;
//DB::enableQueryLog();
        $notPickupList_data = array();
        $notPickupList_main = Property::select('id', 'address', 'city', 'state', 'zip', 'name')->whereIn('id', function ($query) use ($user_id, $date) {
                    $query->from('user_properties')
                            ->select('property_id')
                            ->where('user_id', $user_id)
                            ->where('status', 1)
                            ->where("deleted_at", null)
                            ->whereIn('property_id', function ($query) use ($user_id, $date) {
                                $query->from("units")
                                ->select('property_id')
                                ->whereNotIn('barcode_id', function ($query) use ($user_id, $date) {
                                    $query->from('activity_log')
                                    ->select('barcode_id')
                                    ->where('user_id', $user_id)
                                    ->where("deleted_at", null)
                                    ->whereDate('created_at', "'" . $date . "'");
                                })
                                ->whereIn("property_id", function ($query) {
                                    $query->from('property_frequencies')
                                    ->select("property_id")
                                    ->where("day", $this->getCurrentDay())
                                    ->where("deleted_at", null)
                                    ->groupBy('property_id');
                                })
                                ->where("deleted_at", null)
                                ->groupBy('property_id')
                                ->where('is_active', 1);
                            });
                })->get();

        if ($notPickupList_main->count() > 0) {
            $i = 0;
            foreach ($notPickupList_main as $notPickupLists) {
                $mainProperty = $this->getPropertyType($notPickupLists->id);
                $punits = $mainProperty["units"] > 1 ? 1 : 0;

                $propUnits = Units::select("latitude", "longitude", "address1", "address2", "floor", "building", "unit_number", 'barcode_id')
                                ->where("property_id", $notPickupLists->id)
                                ->where("is_active", 1)
                                ->whereNotIn('barcode_id', function ($query) use ($user_id, $date) {
                                    $query->from('activity_log')
                                    ->select('barcode_id')
                                    ->where('user_id', $user_id)
                                    ->where('type', 2)
                                    ->whereDate('created_at', $date);
                                })->get();
//dump(count($propUnits));
                if (count($propUnits) > 0) {
                    $notPickupList_data[$i]['id'] = $notPickupLists->id;
                    $notPickupList_data[$i]['name'] = $notPickupLists->name;
                    $notPickupList_data[$i]['address'] = $notPickupLists->address;
                    $notPickupList_data[$i]['city'] = $notPickupLists->city;
                    $notPickupList_data[$i]['state'] = $notPickupLists->state;
                    $notPickupList_data[$i]['zip'] = $notPickupLists->zip;
                    $notPickupList_data[$i]["hasMultipleUnit"] = $punits;
                    $notPickupList_data[$i]["unit"] = $this->setData($propUnits->toArray());
//$notPickupList_data[$i]["unit"] = $this->setData($notPickupLists["unit"]->toArray());

                    $totalBarcodeActivePerProperty = 0;
                    $i++;
                } else {
                    
                }
            }
        }


// $notPickupList->each(function ($item, $key) {
//      if (count($item->unit) < 1) {
//             return false;
//         }
//    });
//        $notPickupList->reject(function ($value, $key) {
//            if (empty($value->unit)) {
//               // dump($value);
//                unset($value->id);
//                unset($value->address);
//                unset($value->city);
//                unset($value->hasMultipleUnit);
//                unset($value->unit);
//
//            }
//        });

        if (isset($notPickupList_data) && !empty($notPickupList_data)) {
            return $notPickupList_data;
        } else {
            return false;
        }
    }

    private function propertyActiveOrNot($barcode_id) {

        $ifQrcodeExist = Units::select('id', 'is_active')->where('barcode_id', $barcode_id)->get();

        $where = array('barcode_id' => $barcode_id, 'is_active' => 0);
        $activeOrNot = Units::select('id')->where($where)->get()->isEmpty();

        if ($ifQrcodeExist->isEmpty()) {
            return false;
        } elseif ($ifQrcodeExist->toArray()[0]['is_active'] == 1) {
            return "Already Active.";
        } elseif ($activeOrNot == "") {
            return false;
        } else {
            return true;
        }
    }

    private function getTotalActiveUnit($user_id, $date) {

//DB::enableQueryLog();
        $totalActiveUnit = Units::select('id', 'address1', 'address2', 'unit_number', 'barcode_id')->whereIn('property_id', function ($query) use ($user_id) {
                    $query->from('user_properties')
                            ->select('property_id')
                            ->where('user_id', $user_id)
                            ->where('status', 1);
                })->where('is_active', 1)->get();

//dd($notPickupList,DB::getQueryLog());

        if (isset($totalActiveUnit) && !empty($totalActiveUnit)) {
            return $totalActiveUnit;
        } else {
            return false;
        }
    }

    private function getRemainingPickupPropertyV4($user_id, $date) {

        $totalBarcodePerProperty = 0;
        $totalBarcodeActivePerProperty = 0;
        $startDate = \Carbon\Carbon::parse($date)->addHour(6);
        $endDate = \Carbon\Carbon::parse($date)->addDays(1)->addHour(5)->addMinute(59)->addSecond(59);

        $notPickupList = Property::select('name', 'address', 'city', 'state', 'zip', 'id', 'type', 'state')->whereIn('id', function ($query) use ($user_id) {
                    $query->from('user_properties')
                            ->select('property_id')
                            ->where('user_id', $user_id)
                            ->where('status', 1)
                            ->where("deleted_at", null)
                            ->whereIn('property_id', function ($query) {
                                $query->from("units")
                                ->select('property_id')
                                ->whereIn("property_id", function ($query) {
                                    $query->from('property_frequencies')
                                    ->select("property_id")
                                    ->where("day", $this->getCurrentDay())
                                    ->where("deleted_at", null)
                                    ->groupBy('property_id');
                                })
                                ->where("deleted_at", null)
                                ->groupBy('property_id')
                                ->where('is_active', 1);
                            });
                })->get();

        if ($notPickupList->count() > 0) {
            foreach ($notPickupList as $notPickupLists) {
                $state_name = \App\State::select('name')->where('id', $notPickupLists->state)->first();

                $notPickupLists->state = $state_name->name;

                $notPickupLists["buildings"] = Units::select("building", "building_id")
                        ->where("property_id", $notPickupLists->id)
                        ->where("is_active", 1)->groupBy('building')
//->with(['getBuildingDetail'])
                        ->get();


                foreach ($notPickupLists["buildings"] as $notBuilding) {
///////////////////////BUILDING ADDRESS/////////////////////////////////

                    if (isset($notBuilding->building_id) && !empty($notBuilding->building_id) && $notBuilding->building_id != null) {
                        $buildingDetail = \App\Building::find($notBuilding->building_id);
                        $notBuilding->building_address = $buildingDetail->address;
                    } else {
                        $notBuilding->building_address = "";
                    }

///////////////////////BUILDING ADDRESS///////////////////////////////

                    if ($notBuilding->building != null) {
                        $unit = Units::select("latitude", "longitude", "address1", "address2", "floor", "building", "unit_number", 'barcode_id', 'is_active', 'property_id', 'building_id')
                                        ->where("property_id", $notPickupLists->id)
                                        ->where("building", $notBuilding->building)
                                        ->where("is_active", 1)->get();

                        foreach ($unit as $check_pickup) {
                            $check_pickup["pickup"] = Activitylogs::where(['type' => 2, 'barcode_id' => $check_pickup['barcode_id']])
//->whereDate('created_at', ' = ', $date)
                                            //->whereBetween('created_at', [$startDate, $endDate])
                                            ->whereBetween('created_at', [$startDate, $endDate])
                                            ->get()->count();
                            if ($check_pickup["pickup"] > 0) {
                                $totalBarcodeActivePerProperty++;
                            }

//PROPERTY TYPE
                            $check_pickup->address2 = $notPickupLists->city . ', ' . $notPickupLists->state . ', ' . $notPickupLists->zip;
                            if (isset($notPickupLists->type) && ($notPickupLists->type == 1 || $notPickupLists->type == 4)) {
                                $check_pickup->address1 = $check_pickup->address1;
                            } elseif (isset($notPickupLists->type) && ($notPickupLists->type == 2 || $notPickupLists->type == 3)) {
                                $check_pickup->address1 = $check_pickup->getBuildingDetail->address;
                                unset($check_pickup->get_building_detail);
                            }


//PROPERTY TYPE
                        }

                        $notBuilding["unit"] = $this->setData($unit->toArray());
                    } else {
                        $unit = Units::select("latitude", "longitude", "address1", "address2", "floor", "building", "unit_number", 'barcode_id', 'is_active')
                                        ->where("property_id", $notPickupLists->id)
                                        ->where("building", $notBuilding->building)
                                        ->where("is_active", 1)->get();

                        foreach ($unit as $check_pickup) {
                            $check_pickup["pickup"] = Activitylogs::where(['type' => 2, 'barcode_id' => $check_pickup['barcode_id']])
//->whereDate('created_at', ' = ', $date)
                                            //->whereBetween('created_at', [$startDate, $endDate])
                                            ->whereBetween('created_at', [$startDate, $endDate])
                                            ->whereNull('deleted_at')
                                            ->get()->count();
                            if ($check_pickup["pickup"] > 0) {
                                $totalBarcodeActivePerProperty++;
                            }

//PROPERTY TYPE

                            $check_pickup->address2 = $notPickupLists->city . ', ' . $notPickupLists->state . ', ' . $notPickupLists->zip;
                            if (isset($notPickupLists->type) && $notPickupLists->type == 1) {
                                $check_pickup->address1 = $check_pickup->address1;
                            } elseif (isset($notPickupLists->type) && ($notPickupLists->type == 2 || $notPickupLists->type == 3)) {
                                if (isset($check_pickup->getBuildingDetail->address)) {
                                    $check_pickup->address1 = $check_pickup->getBuildingDetail->address;
                                } else {
                                    $check_pickup->address1 = '';
                                }


                                unset($check_pickup->get_building_detail);
                            }

//PROPERTY TYPE
                        }


                        $notBuilding["unit"] = $this->setData($unit->toArray());
                    }


                    if (isset($notPickupLists->type) && $notPickupLists->type == 1) {
                        $notBuilding->building = "";
                    }
                }

                $notPickupLists["buildings"] = $this->setData($notPickupLists["buildings"]->toArray());
                $totalBarcodeActivePerProperty = 0;
            }
        }


//dd($notPickupList,DB::getQueryLog());

        if (isset($notPickupList) && !empty($notPickupList)) {
            return $notPickupList;
        } else {
            return false;
        }
    }

    /**
     * Report Issue Reason
     *
     * @response {
     * "message": "success",
     * "data": {
     *   "reason": [
     *       {
     *           "id": 1,
     *           "reason": "Missed Pickup"
     *       },
     *       {
     *            "id": 2,
     *           "reason": "Customer Complaint"
     *        },
     *        {
     *            "id": 3,
     *             "reason": "Property Inaccessible"
     *         },
     *         {
     *             "id": 4,
     *              "reason": "Delayed service due to weather"
     *          },
     *        {
     *            "id": 5,
     *            "reason": "Compactor Full"
     *         }
     *       ],
     *       "responseCode": 200,
     *       "responseMsg": "success"
     *   },
     *    "status": 200
     * }
     */
    public function getReportIssueReason(Request $request) {

        $issueReason = \App\IssueReason::select('id', 'reason')
                        ->where('user_id', $this->subscriberId)
                        ->orWhereNull('user_id')->get();

        if (isset($issueReason) && $issueReason->isNotEmpty()) {
            $response['reason'] = $this->setData($issueReason);
            $response['responseCode'] = 200;
            $response['responseMsg'] = "success";
            return response()->json(['message' => 'success', 'data' => $response, 'status' => 200], 200);
        } else {
            $data = [
                'responseCode' => 201,
                'responseMsg' => 'Reason Not Found.'
            ];
            return response()->json(['message' => 'success', 'data' => $data, 'status' => 200], 200);
        }
    }

    /**
     * Report Issue
     *
     * Version v5: Remove updateOrCreate function and issue_date param and change issue_date format (y-m-d to y-m-d h:i:s).
     *
     * @response {
     *   "message": "success",
     *   "data": {
     *       "responseCode": 200,
     *       "responseMsg": "Exception generated successfully, waiting for admin review."
     *   },
     *    "status": 200
     * }
     */
    public function reportIssue(Request $request) {

        $validator = Validator::make($request->all(), [
                    'title' => 'required|string',
                    'description' => 'required|string',
                    'issue_date' => 'required|date_format:"Y-m-d H:i:s"',
                    'reason' => 'required|integer',
                    'property_id' => 'required|integer|exists:properties,id,deleted_at,NULL',
                    'building_id' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return $this->errorFormat($validator->errors());
        }

        $reportIssue = \App\ReportIssue::create([
                    'title' => $request->title,
                    'description' => $request->description,
                    'property_id' => $request->property_id,
                    'building_id' => $request->building_id,
                    'issue_reason_id' => $request->reason,
                    'issue_date' => $request->issue_date,
                    'user_id' => $this->userId,
                    'subscribers_id' => $this->subscriberId
        ]);

        if ($reportIssue) {
            $data = [
                'responseCode' => 200,
                'responseMsg' => 'Exception generated successfully, waiting for admin review.'
            ];

            return response()->json(['message' => 'success', 'data' => $data, 'status' => 200], 200);
        } else {
            $data = [
                'responseCode' => 201,
                'responseMsg' => 'Report issue failed.'
            ];

            return response()->json(['message' => 'success', 'data' => $data, 'status' => 200], 200);
        }
    }

    private function getRemainingPickupPropertyV3($user_id, $date) {

        $totalBarcodePerProperty = 0;
        $totalBarcodeActivePerProperty = 0;
        $buildingId = $propertyId = [];

        if (!empty($date)) {
            $start = \Carbon\Carbon::parse($date)->format('Y-m-d') . " 06:00:00";
            $end = \Carbon\Carbon::parse($start)->addDays(1)->format('Y-m-d') . " 05:59:59";
        } else {
            $start = $this->start;
            $end = $this->end;
        }
        //Exclude Property: Start
        $excludedProperty = \App\ExcludedProperty::whereBetween(DB::raw("convert_tz(exclude_date,'UTC','" . $this->timezone . "')"), [$this->start, $this->end])->get();

        $excludedProperty->each(function ($item) use (&$propertyId, &$buildingId) {

            if (empty($item->building_id)) {
                $propertyId[] = $item->property_id;
            } else {
                $buildingId[] = $item->building_id;
            }
        });

        //print_r($propertyId);
        //dd($buildingId);
        //Exclude Property: End


        $notPickupList = Property::select('name', 'address', 'city', 'state', 'zip', 'id', 'type', 'state')->whereIn('id', function ($query) use ($user_id, $propertyId) {
                    $query->from('user_properties')
                    ->select('property_id')
                    ->where('user_id', $user_id)
                    ->where('status', 1)
                    ->where("deleted_at", null)
                    ->whereIn('property_id', function ($query) use ($propertyId) {
                        $query->from("units")
                        ->select('property_id')
                        ->whereIn("property_id", function ($query) use ($propertyId) {
                            $query->from('property_frequencies')
                            ->select("property_id")
                            ->whereNotIn('property_id', $propertyId)
                            ->where("day", $this->getCurrentDay())
                            ->where("deleted_at", null)
                            ->groupBy('property_id');
                        })
                        ->where("deleted_at", null)
                        ->groupBy('property_id')
                        ->where('is_active', 1);
                    });
                })
                ->whereHas('service', function ($query) use ($start, $end) {
                    $query->where('pickup_start', '<=', $start)
                    ->where('pickup_finish', '>=', $end);
                })
                ->whereHas('checkInProperty', function ($query) use ($user_id) {
                    $query->whereBetween(DB::raw("convert_tz(updated_at,'UTC','" . $this->timezone . "')"), [$this->start, $this->end])
                    ->where('user_id', $user_id);
                })
                ->get();


        if ($notPickupList->count() > 0) {
            foreach ($notPickupList as $key => $notPickupLists) {
                $state_name = \App\State::select('name')->where('id', $notPickupLists->state)->first();

                $notPickupLists->state = $state_name->name;

                $buildings = Units::select("building", "building_id")
                        ->where("property_id", $notPickupLists->id)
                        ->where(function ($query) use ($buildingId) {
                            $query->whereNotIn('building_id', $buildingId)->orWhereNull('building_id');
                        })
                        ->where("is_active", 1)
                        ->groupBy('building')
                        ->get();

                foreach ($buildings as $notBuilding) {
                    ///////////////////////Walk Through/////////////////////////////////

                    $walkThrough = \App\walkThroughRecord::where("property_id", $notPickupLists->id)
                            ->when($notPickupLists->type == 2 || $notPickupLists->type == 3, function ($query) use ($notBuilding) {
                                $query->where('building_id', $notBuilding->building_id);
                            })
                            ->whereBetween(DB::raw("convert_tz(updated_at,'UTC','" . $this->timezone . "')"), [$this->start, $this->end])
                            ->get();

                    if (isset($notBuilding->building_id) && !empty($notBuilding->building_id) && $notBuilding->building_id != null) {
                        if (isset($notBuilding->getBuildingDetail->address)) {
                            $notBuilding->building_address = $notBuilding->getBuildingDetail->address;
                        }
                    } else {
                        $notBuilding->building_address = "";
                    }

                    $notBuilding->walkThrough = $walkThrough->count();

                    ///////////////////////Walk Through///////////////////////////////

                    $unit = null;
                    if ($notBuilding->building != null) {
                        $unit = Units::select("latitude", "longitude", "address1", "address2", "floor", "building", "unit_number", 'barcode_id', 'is_active', 'property_id', 'building_id')
                                        ->where("property_id", $notPickupLists->id)
                                        ->where("building", $notBuilding->building)
                                        ->where("is_active", 1)->get();
                    } else {
                        $unit = Units::select("latitude", "longitude", "address1", "address2", "floor", "building", "unit_number", 'barcode_id', 'is_active')
                                        ->where("property_id", $notPickupLists->id)
                                        ->where("is_active", 1)->get();
                    }

                    foreach ($unit as $check_pickup) {
                        $check_pickup["pickup"] = Activitylogs::where(['type' => 2, 'barcode_id' => $check_pickup['barcode_id']])
                                        ->whereBetween(DB::raw("convert_tz(updated_at,'UTC','" . $this->timezone . "')"), [$this->start, $this->end])
                                        ->get()->count();
                        if ($check_pickup["pickup"] > 0) {
                            $totalBarcodeActivePerProperty++;
                        }

//PROPERTY TYPE
                        $check_pickup->address2 = $notPickupLists->city . ', ' . $notPickupLists->state . ', ' . $notPickupLists->zip;
                        if (isset($notPickupLists->type) && ($notPickupLists->type == 1 || $notPickupLists->type == 4)) {
                            $check_pickup->address1 = $check_pickup->address1;
                        } elseif (isset($notPickupLists->type) && ($notPickupLists->type == 2 || $notPickupLists->type == 3)) {
                            $check_pickup->address1 = $check_pickup->getBuildingDetail->address;
                            unset($check_pickup->get_building_detail);
                        }
                    }

                    $notBuilding["unit"] = $this->setData($unit->toArray());



                    if (isset($notPickupLists->type) && $notPickupLists->type == 1) {
                        $notBuilding->building = "";
                    }
                }

                $notPickupLists["buildings"] = $this->setData($buildings->toArray());
                $totalBarcodeActivePerProperty = 0;

                if ($buildings->count() == 0) {
                    $notPickupList->forget($key);
                }
            }
        }

        if (isset($notPickupList) && !empty($notPickupList)) {
            return $notPickupList->values();
        } else {
            return false;
        }
    }

    private function getRemainingPickupPropertyV2($user_id, $date) {

        $totalBarcodePerProperty = 0;
        $totalBarcodeActivePerProperty = 0;

        $notPickupList = Property::select('name', 'address', 'city', 'state', 'zip', 'id')->whereIn('id', function ($query) use ($user_id) {
                    $query->from('user_properties')
                            ->select('property_id')
                            ->where('user_id', $user_id)
                            ->where('status', 1)
                            ->where("deleted_at", null)
                            ->whereIn('property_id', function ($query) {
                                $query->from("units")
                                ->select('property_id')
                                ->whereIn("property_id", function ($query) {
                                    $query->from('property_frequencies')
                                    ->select("property_id")
                                    ->where("day", $this->getCurrentDay())
                                    ->where("deleted_at", null)
                                    ->groupBy('property_id');
                                })
                                ->where("deleted_at", null)
                                ->groupBy('property_id')
                                ->where('is_active', 1);
                            });
                })->get();

        if ($notPickupList->count() > 0) {
            foreach ($notPickupList as $notPickupLists) {
                $state_name = \App\State::select('name')->where('id', $notPickupLists->state)->first();

                $notPickupLists->state = $state_name->name;

                $notPickupLists["buildings"] = Units::select("building")
                                ->where("property_id", $notPickupLists->id)
                                ->where("is_active", 1)->groupBy('building')->get();


                foreach ($notPickupLists["buildings"] as $notBuilding) {
                    if ($notBuilding->building != null) {
                        $notBuilding["unit"] = Units::select("latitude", "longitude", "address1", "address2", "floor", "building", "unit_number", 'barcode_id', 'is_active')
                                        ->where("property_id", $notPickupLists->id)
                                        ->where("building", $notBuilding->building)
                                        ->where("is_active", 1)->get();

                        foreach ($notBuilding["unit"] as $check_pickup) {
                            $check_pickup["pickup"] = Activitylogs::where(['type' => 2, 'barcode_id' => $check_pickup['barcode_id']])
                                            ->whereDate('created_at', ' = ', $date)
                                            ->get()->count();
                            if ($check_pickup["pickup"] > 0) {
                                $totalBarcodeActivePerProperty++;
                            }
                        }


                        $notBuilding["unit"] = $this->setData($notBuilding["unit"]->toArray());
                    } else {
                        $notBuilding["unit"] = Units::select("latitude", "longitude", "address1", "address2", "floor", "building", "unit_number", 'barcode_id', 'is_active')
                                        ->where("property_id", $notPickupLists->id)
                                        ->where("building", $notBuilding->building)
                                        ->where("is_active", 1)->get();


                        foreach ($notBuilding["unit"] as $check_pickup) {
                            $check_pickup["pickup"] = Activitylogs::where(['type' => 2, 'barcode_id' => $check_pickup['barcode_id']])
                                            ->whereDate('created_at', ' = ', $date)
                                            ->whereNull('deleted_at')
                                            ->get()->count();
                            if ($check_pickup["pickup"] > 0) {
                                $totalBarcodeActivePerProperty++;
                            }
                        }




                        $notBuilding["unit"] = $this->setData($notBuilding["unit"]->toArray());
                    }
                }

                $notPickupLists["buildings"] = $this->setData($notPickupLists["buildings"]->toArray());
                $totalBarcodeActivePerProperty = 0;
            }
        }


//dd($notPickupList,DB::getQueryLog());

        if (isset($notPickupList) && !empty($notPickupList)) {
            return $notPickupList;
        } else {
            return false;
        }
    }

    private function getRemainingPickupProperty($user_id, $date) {

        $totalBarcodePerProperty = 0;
        $totalBarcodeActivePerProperty = 0;
//DB::enableQueryLog();

        $notPickupList = Property::select('id', 'address', 'city')->whereIn('id', function ($query) use ($user_id) {
                    $query->from('user_properties')
                            ->select('property_id')
                            ->where('user_id', $user_id)
                            ->where('status', 1)
                            ->where("deleted_at", null)
                            ->whereIn('property_id', function ($query) {
                                $query->from("units")
                                ->select('property_id')
                                ->whereIn("property_id", function ($query) {
                                    $query->from('property_frequencies')
                                    ->select("property_id")
                                    ->where("day", $this->getCurrentDay())
                                    ->where("deleted_at", null)
                                    ->groupBy('property_id');
                                })
                                ->where("deleted_at", null)
                                ->groupBy('property_id')
                                ->where('is_active', 1);
                            });
                })->get();

//echo "<pre>";print_r($notPickupList->toArray());die("");
        if ($notPickupList->count() > 0) {
            foreach ($notPickupList as $notPickupLists) {
                $notPickupLists["unit"] = Units::select("latitude", "longitude", "address1", "address2", "floor", "building", "unit_number", 'barcode_id')
                                ->where("property_id", $notPickupLists->id)
                                ->where("is_active", 1)->get();


                foreach ($notPickupLists["unit"] as $check_pickup) {
                    $check_pickup["pickup"] = Activitylogs::where(['type' => 2, 'user_id' => $user_id, 'barcode_id' => $check_pickup['barcode_id']])
                                    ->whereDate('created_at', ' = ', $date)
                                    ->get()->count();
                    if ($check_pickup["pickup"] > 0) {
                        $totalBarcodeActivePerProperty++;
                    }
                }
                $notPickupLists["pickup"] = $notPickupLists['unit']->count() == $totalBarcodeActivePerProperty ? 1 : 0;
                $notPickupLists["unit"] = $this->setData($notPickupLists["unit"]->toArray());
                $totalBarcodeActivePerProperty = 0;
            }
        }


//dd($notPickupList,DB::getQueryLog());

        if (isset($notPickupList) && !empty($notPickupList)) {
            return $notPickupList;
        } else {
            return false;
        }
    }

    private function checkAllPropertyPickupDone($property_id) {
        $totalPropertyUnit = Unit::where(['property_id' => $property_id, 'is_active' => 1])->get()->count();
        $totalPickup = Activitylogs::where(['property_id' => $property_id, 'is_active' => 1])->get()->count();
    }

    private function getPropertyTotalUnit($user_id) {

//$totalUnit = Property::where('user_id',$user_id)->sum('units');


        $totalUnit = Property::whereIn('id', function ($query) use ($user_id) {
                    $query->from('user_properties')
                            ->select('property_id')
                            ->where('user_id', $user_id)
                            ->where('status', 1);
                })->sum('units');

        if (isset($totalUnit) && !empty($totalUnit)) {
            return $totalUnit;
        } else {
            return false;
        }
    }

    private function getPropertyByUserId($id) {
        $propertyType = \App\UserProperties::select('property_id')->where(['user_id' => $id, 'status' => 1]);

        if (isset($propertyType) && !empty($propertyType)) {
            return $propertyType->get();
        } else {
            return false;
        }
    }

    private function getPropertyType($id) {
        $propertyType = Property::select('type', 'address', 'city', 'name', 'units', 'state', 'zip')->where('id', $id)->with('getState')->first();

        if (isset($propertyType) && !empty($propertyType)) {
            return $propertyType->toArray();
        } else {
            return false;
        }
    }

    private function propertyDetailByQrbarId($barcode_id) {
        $propertyId = Units::where('barcode_id', $barcode_id)->get();

        if ($propertyId->count()) {
            return $propertyId;
        } else {
            return false;
        }
    }

    private function getPropertyUnit($id) {

        $propertyUnit = Units::where(['property_id' => $id, 'is_active' => 1])->orderBy('updated_at', 'desc')->limit('1')->get()->toArray();

//dd($propertyUnit);

        if (isset($propertyUnit) && !empty($propertyUnit)) {
            return $propertyUnit;
        } else {
            return false;
        }
    }

    private function checkPropertyUnitEmpty($id, $barcodeId) {

        $checkHaveUnit = Units::select('unit_number')
                ->where(['property_id' => $id, 'barcode_id' => $barcodeId])
                ->get();

        if (isset($checkHaveUnit[0]->unit_number) && !empty($checkHaveUnit[0]->unit_number)) {
            return $checkHaveUnit[0]->unit_number;
        } else {
            $propertyUnit = Units::where(['property_id' => $id, 'is_active' => 1])->orderBy('unit_number', 'desc')->limit('1')->get();
//dd($propertyUnit);
            if (isset($propertyUnit[0]->unit_number) && !empty($propertyUnit[0]->unit_number)) {
                $defaultUnit = ++$propertyUnit[0]->unit_number;
            } else {
                $defaultUnit = 100;
            }
            return $defaultUnit;
        }
    }

    private function checkAppVersion($appVersion, $plateFrom) {

        $appVersion = AppSetting::select('app_version')->where(['app_version' => $appVersion, 'plateform' => $plateFrom])->get();

        if ($appVersion->count()) {
            return true;
        } else {
            return false;
        }
    }

    private function checkUnitAddressUnique($address1, $address2, $unitNumber) {

        $appVersion = Units::where(['address1' => $address1, 'address2' => $address2, 'unit_number' => $unitNumber])->get();

        if (!$appVersion->count()) {
            return true;
        } else {
            return false;
        }
    }

    private function getUserIdByToken($authToken) {

        if (!empty($authToken)) {
            $authToken = explode(' ', $authToken)[1];

            $user_id = User::select('id')
                            ->where('api_token', $authToken)->get();
            if ($user_id->count()) {
                return $user_id[0]->id;
            }
        } else {
            return false;
        }
    }

    private function getSubscriberIdByToken($authToken) {

        if (!empty($authToken)) {
            $authToken = explode(' ', $authToken)[1];

            $user_id = User::select('subscriber_id')
                            ->where('api_token', $authToken)->get();
            if ($user_id->count()) {
                return $user_id[0]->subscriber_id;
            }
        } else {
            return false;
        }
    }

    private function getUserDetailIdByToken($authToken) {

        if (!empty($authToken)) {
            $authToken = explode(' ', $authToken)[1];

            $user_id = User::where('api_token', $authToken)->first();
            if (!empty($user_id)) {
                return $user_id;
            }
        } else {
            return false;
        }
    }

    private function getTimezoneByToken($authToken) {

        if (!empty($authToken)) {
            $authToken = explode(' ', $authToken)[1];

            $user_id = User::select('timezone')
                            ->where('api_token', $authToken)->first();
            if (!empty($user_id)) {
                return $user_id->timezone;
            }
        } else {
            return false;
        }
    }

    private function getVersionByToken($authToken) {

        if (!empty($authToken)) {
            $authToken = explode(' ', $authToken)[1];

            $user_id = User::select('app_version')
                            ->where('api_token', $authToken)->first();
            if (!empty($user_id)) {
                return $user_id->app_version;
            }
        } else {
            return false;
        }
    }

    private function getPlatformByToken($authToken) {

        if (!empty($authToken)) {
            $authToken = explode(' ', $authToken)[1];

            $user_id = User::select('platform')
                            ->where('api_token', $authToken)->first();
            if (!empty($user_id)) {
                return $user_id->platform;
            }
        } else {
            return false;
        }
    }

    protected function setData($value) {
        array_walk_recursive($value, function (&$item) {
            $item = null === $item ? '' : $item;
        });
        $this->data = $value;
        return $this->data;
    }

    private function base64ToImage($imageData, $id, $image_type, $type = "") {
        $time = time();
        if ($type === 'note') {
            $img_perfix = 'note_';
            $folder_name = 'note/';
            $table_name = '\App\BarcodeNotes';
        } elseif ($type === 'user') {
            $img_perfix = 'user_';
            $folder_name = 'user/';
            $table_name = '\App\User';
        } else {
            $img_perfix = 'violation_';
            $folder_name = 'violation/';
            $table_name = '\App\violation';
        }

        $imgdata = base64_decode($imageData);
        $f = finfo_open();
        $mime_type = "." . explode("/", finfo_buffer($f, $imgdata, FILEINFO_MIME_TYPE))[1];

//$img = $imageData;
//$image_parts = explode(";base64,", $img);
//$image_type_aux = explode("image/", $image_parts[0]);
//$image_type = $image_type_aux[1];
        $image_base64 = base64_decode($imageData);
        $file = public_path() . '/uploads/' . $folder_name . $img_perfix . $id . "." . $image_type;

        file_put_contents($file, $image_base64);

        $violation = $table_name::find($id);
        $violation->image_name = $img_perfix . $id . "." . $image_type;
        $violation->save();
    }

    private function setTimeZone($triggerOn) {

        $userTimezone = new \DateTimeZone($this->timezone);
        $gmtTimezone = new \DateTimeZone('GMT');
        $myDateTime = new \DateTime($triggerOn, $gmtTimezone);
        $offset = $userTimezone->getOffset($myDateTime);
        $myInterval = \DateInterval::createFromDateString((string) $offset . 'seconds');
        $myDateTime->add($myInterval);
        $result = $myDateTime->format('Y-m-d H:i:s');
        return $result;
    }

    private function isEmployeeValid($barcode_id) {

        $propertyUserId = Property::where('id', function ($query) use ($barcode_id) {
                    $query->select('property_id')
                            ->from('units')
                            ->where('barcode_id', $barcode_id);
                })->get();

        if ($propertyUserId[0]->subscriber_id == $this->subscriberId) {
            return true;
        } else {
            return false;
        }
    }

    private function checkValidEmployee($barcode_id) {

        $propertyUserId = Property::where('id', function ($query) use ($barcode_id) {
                    $query->select('property_id')
                            ->from('units')
                            ->where('barcode_id', $barcode_id);
                })->get();

        $property_id = Units::select('property_id')->where('barcode_id', $barcode_id)->get();

        if ($property_id->isNotEmpty()) {
            $propertyUserId = Property::where('id', $property_id[0]->property_id)->get();

            if ($propertyUserId[0]->subscriber_id == $this->subscriberId) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    private function getCurrentDay() {
        // Get Current Time
        // Get User Time Zone
        // Convert into Local time


        $time = \Carbon\Carbon::now();
        $converted_to_timezone = \Carbon\Carbon::now()->setTimezone($this->timezone);
        $currentDay = $converted_to_timezone->subHours(6)->format('l');
        /* $aTime='2000-01-01 06:00:00';
          $cTime = '2000-01-01 '.date("h:i:s",strtotime($current_local_time));
          $diff = strtotime($aTime) - strtotime($cTime);
          $currentDay='';

          if($diff >= 0){
          $currentDay = \Carbon\Carbon::now()->subDays(1)->format('l');
          }else{
          $currentDay = \Carbon\Carbon::now()->format('l');
          } */

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

    // Get date according to timezone

    private function getStartDate() {
        // Get Current Time
        // Get User Time Zone
        // Convert into Local time

        $time = \Carbon\Carbon::now();
        $converted_to_timezone = \Carbon\Carbon::now()->setTimezone($this->timezone);
        $currentDay = $converted_to_timezone->subHours(6);

        return $currentDay;
    }

    protected function getReason() {

        $reasonFirst = \App\Reason::select('reason as value', 'id')
                ->where('user_id', $this->subscriberId)
                ->whereNotNull('user_id')
                ->get();

        $reasonSec = \App\Reason::select('reason as value', 'id')
                ->whereIn('id', function ($query) {
                    $query->select('violation_reason')
                    ->from('violations')
                    ->whereIn('user_id', function ($query) {
                        $query->select('id')
                        ->from('users')
                        ->whereNull('deleted_at')
                        ->where('subscriber_id', $this->subscriberId);
                    })
                    ->whereNull('deleted_at');
                })
                ->whereNull('user_id')
                ->get();

        $reason = $reasonFirst->merge($reasonSec);

        if (!$reason->isEmpty()) {
            return $reason;
        } else {
            return [];
        }
    }

    protected function getAction() {

        $action = \App\Action::select('action as value', 'id')
                ->where(function ($query) {
                    $query->where('company_id', $this->subscriberId)
                    ->orWhere('type', 0);
                })
                ->get();

        if (!$action->isEmpty()) {
            return $action;
        } else {
            return false;
        }
    }

    protected function errorFormat($error, $requestId = "") 
    {

        $errorMsg = "";
        foreach ($error->all() as $message) {
            $errorMsg .= $message;
        }

        if ($errorMsg == 'Task cannot be completed at your current location. User must be within valid radius of property address.') {
            $responseCode = 204;
        } else {
            $responseCode = 201;
        }

        return response()->json(
            [
                'message' => $errorMsg,
                'data' => array(
                    'responseCode' => $responseCode,
                    'responseMsg' => $errorMsg,
                    'requestid' => $requestId
                ),
                'status' => 200
            ]
        );
    }

    function sendnotification($barcodeId) 
    { 
        try {
            $date = \Carbon\Carbon::now()->format('d-m-Y');

            $propertyId = Units::select('property_id')
                    ->where('barcode_id', $barcodeId)
                    ->first();

            $propertyName = \App\Property::select('name')
                    ->where('id', $propertyId->property_id)
                    ->first();

            $barcodes = Units::select('barcode_id')
                    ->where('property_id', $propertyId->property_id)
                    ->get();

            $bar = \App\Activitylogs::whereIn('barcode_id', $barcodes)
                    ->whereBetween(
                        DB::raw("convert_tz(updated_at,'UTC','" . $this->timezone . "')"),
                        [$this->start, $this->end]
                    )
                    ->where('type', 2)
                    ->get();

            if ($bar->isEmpty()) {
                $userDetail = \App\User::whereIn(
                    'id',
                    function ($query) use ($propertyId) {
                        $query->select('user_id')
                            ->from('user_properties')
                            ->where('property_id', $propertyId->property_id);
                    }
                )->where('role_id', 10)->get();

                foreach ($userDetail as $user) {
                    $mobile = "+1" . $user->mobile . "";

                    $content = "Dear " . $user->firstname . " " . $user->lastname . ",<br/>";
                    $content .= "Pickup has been initiated "
                            . "for " . $propertyName->name . "<br/><br/>";
                    $content .= "Best regards,<br/><br/>";
                    $content .= "Trash Scan Customer Support.<br/><br/>";

                    $user->notify(new EmailTemplate($content, 'First pick up done for ' . $propertyName->name . ' ' . $date . ''));
                    sms($mobile, 'First pick up done for ' . $propertyName->name . " " . $date . '');

                }
            }
        }
        catch(\Exception $e) {
            //echo 'Message: ' .$e->getMessage();die('222');
        }
    }

    protected function getLatLong($address) {

        $params = array(
            'address' => $address,
            'sensor' => 'false',
            'key' => 'AIzaSyCubWJgDiR9oE6vy6yimjXSzUcs2tt20D0',
        );

        $formattedAddr = http_build_query($params);
//        $geocodeFromAddr = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address=' . $formattedAddr . '&sensor=false&key=AIzaSyCubWJgDiR9oE6vy6yimjXSzUcs2tt20D0');
//        $output = json_decode($geocodeFromAddr);

        $url = "https://maps.googleapis.com/maps/api/geocode/json?" . $formattedAddr;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // Disable SSL verification
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        curl_close($ch);

        $output = json_decode($result);
        //print json_encode($result, JSON_PRETTY_PRINT);
        //dd($url,$result);
        if ($output->status == "OK") {
            $data['latitude'] = $output->results[0]->geometry->location->lat;
            $data['longitude'] = $output->results[0]->geometry->location->lng;
            $data['status'] = true;
            return $data;
        } else {
            $data['status'] = false;
            if (isset($output->error_message)) {
                $data['msg'] = $output->error_message;
            } else {
                $data['msg'] = 'Invalid Address.';
            }

            return $data;
        }
    }

    public function setUserTimeZoneForApi($data = "") 
    {

        $tz = $this->timezone;

        if (isset($tz) && !empty($tz)) {
            $timezone = $tz;
        } else {
            $timezone = "America/New_York";
        }

        if (isset($data) && !empty($data)) {
            $dataTimeZone = $data->setTimezone($timezone);
        } else {
            $dataTimeZone = "";
        }
        return $dataTimeZone;
    }

    private function NotiicationForCheckin($propertyId) 
    {

        try {
            
            $data = [];
            $address = "";
            $userDetail = $this->userDetail;

            $mail = \App\Property::where('id', $propertyId)
                    ->with(
                        [
                            'getEmployee' => function ($query) {
                                $query->where('role_id', getAdminId());
                            }
                        ]
                    )->get();

            if ($mail->isNotEmpty() && $mail[0]->getEmployee->isNotEmpty()) {

                if (isset($mail[0]->address) && !empty($mail[0]->address)) {
                    $address = $mail[0]->address . ', ' 
                            . $mail[0]->city . ', '
                            . $mail[0]->getState->name 
                            . ', ' . $mail[0]->zip;
                }

                $data = [
                    'propertyName' => $mail[0]->name,
                    'address' => $address,
                    'checkInTime' => \Carbon\Carbon::now()->timezone($this->timezone)->format('m-d-Y H:i:s'),
                    'emoployeeName' => $userDetail->firstname . ' ' . $userDetail->lastname
                ];

                $notify = \Notification::send($mail[0]->getEmployee, new \App\Notifications\PropertyCheckIn($data));
                return true;
                //dd(\Config::get('mail'));
            }
            
        } catch (\Exception $e) {
            //echo 'Message: ' . $e->getMessage();
            return false;
        }
    }

    public function getUserTime()
    {
        
        $timeZone = \Carbon\Carbon::now()
                ->timezone($this->timezone)
                ->format('m-d-Y H:i:s');

        return response()->json(
            [
                'message' => "success",
                'data' => array(
                    'responseCode' => 200,
                    'responseMsg' => "User Current Time",
                    'userTime' => $timeZone . " (" . $this->timezone . ")"
                ),
                'status' => 200
            ]
        );
    }

}
