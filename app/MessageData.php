<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MessageData extends Model
{    
    protected $table = 'message_data';
    public function user() {
        return $this->belongsTo('App\User' ,'user_id');
    }
}
