<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentResponse extends Model
{
     use SoftDeletes;
    protected $table = 'payment_response';
}
