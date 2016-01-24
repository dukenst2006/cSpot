<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    //

    protected $fillable = ['name'];



    public function plans()
    {
        return $this->hasMany('App\Models\Plan', 'type_id');
    }


}
