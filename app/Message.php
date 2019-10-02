<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'sms';
    public function user() {
        return $this->belongsTo('App\User' ,'user_id');
    }
}
