<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SubscriptionData extends Model
{
    protected $table = 'subscription_data';
    public function user() {
        return $this->belongsTo('App\User' ,'user_id');
    }
}
