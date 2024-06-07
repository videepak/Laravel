<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Models\Activity;
use Zizaco\Entrust\Traits\EntrustUserTrait;

class User extends Authenticatable
{
    use Notifiable;
    use EntrustUserTrait {
        restore as private restoreA;
    }
    use SoftDeletes {
        restore as private restoreB;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'firstname', 'lastname', 'email', 'password', 'mobile', 'user_id', 'timezone', 'device_token', 'api_token', 'subscriber_id', 'role_id', 'is_admin', 'last_login', 'daliy_status'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    const  PROPERTYMANAGER = 10;

    public function subscriber()
    {
        return $this->belongsTo('App\Subscriber', 'user_id', 'id');
    }

    public function getSubscriber()
    {
        return $this->belongsTo('App\Subscriber', 'subscriber_id', 'id');
    }

    /**
     * Get Reporting manager users with clock-in and clock-out detail.
     * Exculde property manager users.
     */
    public function getManagerUsers()
    {
        $user = $this->userReporting;
        $start = $this->start;
        $end = $this->end;

        if (!empty($start) && !empty($end)) {
            $todayStart = \Carbon\Carbon::parse($start, $user->timezone)->copy()->format('Y-m-d') . ' 06:00:00';

            $todayEnd = \Carbon\Carbon::parse($end, $user->timezone)->copy()->format('Y-m-d') . ' 05:59:59';
        } else {
            $today = \Carbon\Carbon::now()->setTimezone($user->timezone)->subHours(6);
            $todayStart = $today->copy()->format('Y-m-d') . ' 06:00:00';
            $todayEnd = $today->copy()->addDay(1)->format('Y-m-d') . ' 05:59:59';
        }
        
        return $this->hasMany('App\User', 'reporting_manager_id', 'id')
            ->where('role_id', '!=', 10) //Remove property manager from the list.
            ->with(
                [
                    'clockDetail' => function ($query) use ($user, $todayStart, $todayEnd) {
                        $query->select('id', 'user_id', 'clock_in', 'clock_out', 'reason')
                            ->whereBetween(
                                \DB::raw("convert_tz(clock_in, 'UTC', '" . $user->timezone . "')"),
                                [
                                    $todayStart,
                                    $todayEnd,
                                ]
                            )
                            //->limit(1)
                            ->orderBy('id', 'DESC');
                    },
                ]
            );
    }

    public function restore()
    {
        $this->restoreA();
        $this->restoreB();
    }

    public function rolesdata()
    {
        return $this->hasMany('App\Role', 'user_id');
    }

    public function getActivities()
    {
        return $this->hasMany('App\Activitylogs', 'user_id');
    }

    public function customers()
    {
        return $this->hasMany('App\Customer', 'user_id', 'id');
    }

    public function checkinUser()
    {
        return $this->hasMany('App\PropertiesCheckIn', 'user_id', 'id');
    }

    public function properties()
    {
        return $this->hasMany('App\Property', 'user_id', 'id');
    }

    public function clockDetail()
    {
        return $this->hasMany('App\ClockInOut', 'user_id', 'id');
    }

    public static function activeLog($text, $useId, $barcode_id, $requestIp, $type, $lat = '', $long = '')
    {
        $Activity = new Activity();
        $Activity->text = $text;
        $Activity->user_id = $useId;
        $Activity->updated_by = $useId;
        $Activity->barcode_id = $barcode_id;
        $Activity->type = $type;
        $Activity->latitude = $lat;
        $Activity->longitude = $long;
        $Activity->ip_address = $requestIp;
        $Act = $Activity->save();

        if ($Act) {
            return true;
        } else {
            return false;
        }
    }

    public static function noteActiveLog($text, $useId, $barcode_id, $requestIp, $type, $lat = '', $long = '')
    {
        $Activity = new Activity();
        $Activity->text = $text;
        $Activity->user_id = $useId;
        $Activity->updated_by = $useId;
        $Activity->barcode_id = $barcode_id;
        $Activity->type = $type;
        $Activity->latitude = $lat;
        $Activity->longitude = $long;
        $Activity->ip_address = $requestIp;
        $Act = $Activity->save();

        if ($Act) {
            return $Activity->id;
        } else {
            return false;
        }
    }

    public function logs()
    {
        return $this->hasMany('App\Activitylogs', 'user_id');
    }

    public function assignedproperties()
    {
        return $this->hasMany('App\UserProperties', 'user_id');
    }

    public function getUserNotification()
    {
        return $this->hasMany('App\UserNotification', 'id', 'user_id');
    }

    public function hasClockInOut()
    {
        return $this->hasMany('App\ClockInOut', 'user_id');
    }

    public function canAccess()
    {
        return $this->hasMany('App\UserPermissions', 'user_id');
    }
    public function getProperties()
    {
        return $this->belongsToMany('App\Property', 'user_properties');
    }
}
