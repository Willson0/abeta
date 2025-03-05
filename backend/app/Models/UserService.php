<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserService extends Model
{
    protected $guarded = false;
    public function user () {
        return $this->belongsTo(User::class);
    }

    public function service () {
        return $this->belongsTo(Service::class);
    }
}
