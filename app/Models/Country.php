<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $appends = ['flag'];


    public function getFlagAttribute()
    {
        $country = Country::find($this->id);
        return asset('/flags/' . strtolower($country->code).'.png');
    }
}
