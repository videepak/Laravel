<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'pickup_start', 'pickup_finish', 
        'pickup_frequency', 'qrcode_tracking', 
        'valet_trash', 'recycling'
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public function setPickupStartAttribute($pickupStart)
    {
        //$this->attributes['pickup_start'] = date('Y-m-d', strtotime($pickupStart));
        $this->attributes['pickup_start'] = \Carbon\Carbon::parse($pickupStart)
                ->addHour(6);
    }

    public function setPickupFinishAttribute($pickupFinish)
    {
        //$this->attributes['pickup_finish'] = date('Y-m-d', strtotime($pickupFinish));
        $this->attributes['pickup_finish'] = \Carbon\Carbon::parse($pickupFinish)
                ->addDays(1)->addHour(5)->addMinute(59)->addSecond(59);
    }

}
