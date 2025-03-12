<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Analytic extends Model
{
    use HasFactory;
    protected $guarded = false;

    public function time () {
        $this->time = ceil(strlen($this->description) / 1500);
        return $this;
    }

    public function users () {
        return $this->belongsToMany(User::class, "analytic_users");
    }
}
