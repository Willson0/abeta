<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VentureDeal extends Model
{
    protected $guarded = false;

    public function user () {
        return $this->belongsTo(User::class);
    }
}
